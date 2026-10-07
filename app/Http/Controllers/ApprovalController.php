<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockInEditRequest;
use App\Models\StockOut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Hanya Admin HO yang bisa akses (dibatasi via route middleware 'role:admin_ho')
class ApprovalController extends Controller
{
    public function index()
    {
        $transactionRequests = StockOut::with(['item', 'cabang', 'user'])
            ->where('status', 'pending')
            ->get()
            ->map(fn (StockOut $stockOut): array => [
                'model' => $stockOut,
                'item' => $stockOut->item->nama_items ?? '-',
                'requester' => $stockOut->user->name ?? '-',
                'type' => $stockOut->jenis === 'DO' ? 'DO' : ucfirst($stockOut->jenis),
                'detail_route' => route('approval.show.stockout', $stockOut),
                'approve_route' => route('approval.approve', $stockOut),
                'reject_route' => route('approval.reject', $stockOut),
                'item_label' => ($stockOut->jenis === 'penjualan' ? 'penjualan ' : 'request ').($stockOut->item->nama_items ?? 'transaksi ini'),
            ])
            ->toBase();
        $stockInRequests = StockInEditRequest::with(['oldItem', 'newItem', 'stockIn', 'requester'])
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(fn (StockInEditRequest $editRequest): array => [
                'model' => $editRequest,
                'item' => $editRequest->oldItem->nama_items ?? '-',
                'requester' => $editRequest->requester->name ?? '-',
                'type' => 'Edit Stok',
                'detail_route' => route('approval.show.stockin.edit', $editRequest),
                'approve_route' => route('approval.stockin.edit.approve', $editRequest),
                'reject_route' => route('approval.stockin.edit.reject', $editRequest),
                'item_label' => 'edit stok '.($editRequest->oldItem->nama_items ?? 'barang masuk'),
            ])
            ->toBase();
        $approvalRequests = $transactionRequests
            ->merge($stockInRequests)
            ->sortByDesc(fn (array $request): int => $request['model']->created_at?->getTimestamp() ?? 0)
            ->values();

        return view('approval.index', compact('approvalRequests'));
    }

    public function showStockOut(StockOut $stockOut)
    {
        abort_unless($stockOut->status === 'pending', 404);
        $stockOut->load(['item', 'cabang', 'user']);

        return view('approval.show', [
            'approvalType' => $stockOut->jenis === 'penjualan' ? 'penjualan_member' : 'request',
            'stockOut' => $stockOut,
            'editRequest' => null,
        ]);
    }

    public function showStockInEdit(StockInEditRequest $editRequest)
    {
        abort_unless($editRequest->status === 'pending', 404);
        $editRequest->load(['oldItem', 'newItem', 'stockIn', 'requester']);

        return view('approval.show', [
            'approvalType' => 'edit_stok',
            'stockOut' => null,
            'editRequest' => $editRequest,
        ]);
    }

    public function approve(Request $request, StockOut $stockOut)
    {
        if ($stockOut->status !== 'pending') {
            return redirect()->route('approval.index')->with('error', 'Transaksi ini sudah diproses sebelumnya.');
        }

        $item = $stockOut->item;

        if ($stockOut->jumlah > $item->stok_items) {
            return redirect()->route('approval.index')->with('error', 'Stok tidak mencukupi untuk approve transaksi ini. Stok saat ini: '.$item->stok_items);
        }

        DB::transaction(function () use ($stockOut, $item, $request) {
            $stockOut->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'catatan_approval' => $request->input('catatan_approval'),
            ]);

            $item->decrement('stok_items', $stockOut->jumlah);
        });

        return redirect()->route('approval.index')->with('success', 'Transaksi berhasil di-approve, stok telah dipotong.');
    }

    public function reject(Request $request, StockOut $stockOut)
    {
        if ($stockOut->status !== 'pending') {
            return redirect()->route('approval.index')->with('error', 'Transaksi ini sudah diproses sebelumnya.');
        }

        $stockOut->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'catatan_approval' => $request->input('catatan_approval'),
        ]);

        return redirect()->route('approval.index')->with('success', 'Transaksi berhasil ditolak.');
    }

    public function approveStockInEdit(Request $request, StockInEditRequest $editRequest)
    {
        if ($editRequest->status !== 'pending') {
            return redirect()->route('approval.index')->with('error', 'Request edit stok ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($editRequest, $request) {
            $editRequest->load('stockIn');
            $stockIn = $editRequest->stockIn;
            $oldItem = Item::whereKey($editRequest->old_item_id)->lockForUpdate()->firstOrFail();
            $newItem = Item::whereKey($editRequest->new_item_id)->lockForUpdate()->firstOrFail();

            if ($stockIn->item_id !== $editRequest->old_item_id || $stockIn->jumlah !== $editRequest->old_jumlah) {
                abort(409, 'Data barang masuk sudah berubah sebelum request ini diproses.');
            }

            if ($oldItem->is($newItem)) {
                $quantityDifference = $editRequest->new_jumlah - $editRequest->old_jumlah;
                if ($quantityDifference < 0 && $oldItem->stok_items < abs($quantityDifference)) {
                    abort(409, 'Stok tidak mencukupi untuk menerapkan perubahan ini.');
                }

                if ($quantityDifference > 0) {
                    $oldItem->increment('stok_items', $quantityDifference);
                } elseif ($quantityDifference < 0) {
                    $oldItem->decrement('stok_items', abs($quantityDifference));
                }
            } else {
                if ($oldItem->stok_items < $editRequest->old_jumlah) {
                    abort(409, 'Stok item sebelumnya sudah terpakai sehingga item tidak dapat dipindahkan.');
                }

                $oldItem->decrement('stok_items', $editRequest->old_jumlah);
                $newItem->increment('stok_items', $editRequest->new_jumlah);
            }

            $stockIn->update([
                'item_id' => $editRequest->new_item_id,
                'cabang_id' => $newItem->cabang_id,
                'jumlah' => $editRequest->new_jumlah,
                'tanggal' => $editRequest->new_tanggal,
                'sumber' => $editRequest->new_sumber,
                'keterangan' => $editRequest->new_keterangan,
            ]);

            $editRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'catatan_approval' => $request->input('catatan_approval'),
            ]);
        });

        return redirect()->route('approval.index')->with('success', 'Edit barang masuk berhasil di-approve dan stok telah disesuaikan.');
    }

    public function rejectStockInEdit(Request $request, StockInEditRequest $editRequest)
    {
        if ($editRequest->status !== 'pending') {
            return redirect()->route('approval.index')->with('error', 'Request edit stok ini sudah diproses sebelumnya.');
        }

        $editRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'catatan_approval' => $request->input('catatan_approval'),
        ]);

        return redirect()->route('approval.index')->with('success', 'Request edit barang masuk berhasil ditolak.');
    }
}
