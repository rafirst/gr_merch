<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\StockInEditRequest;
use App\Models\StockOut;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function show(string $type, int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($type === 'in') {
            $record = StockIn::with(['item', 'cabang', 'user'])->findOrFail($id);
            $this->authorizeCabang($user, $record->cabang_id);

            return view('history.show', compact('type', 'record'));
        }

        if ($type === 'out') {
            $record = StockOut::with(['item', 'cabang', 'user', 'approver'])->findOrFail($id);
            $this->authorizeCabang($user, $record->cabang_id);

            return view('history.show', compact('type', 'record'));
        }

        if ($type === 'approval-out') {
            $record = StockOut::with(['item', 'cabang', 'user', 'approver'])->findOrFail($id);
            $this->authorizeCabang($user, $record->cabang_id);

            return view('history.show', compact('type', 'record'));
        }

        if ($type === 'approval-in') {
            $record = StockInEditRequest::with(['oldItem.cabang', 'newItem', 'requester', 'approver'])->findOrFail($id);
            $cabangId = $record->oldItem?->cabang_id ?? $record->newItem?->cabang_id;

            if ($cabangId !== null) {
                $this->authorizeCabang($user, (int) $cabangId);
            } elseif (! $user->isAdminHo()) {
                abort(403, 'Anda tidak memiliki akses ke histori cabang lain.');
            }

            return view('history.show', compact('type', 'record'));
        }

        abort(404);
    }

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $inQuery = StockIn::with(['item', 'cabang', 'user'])
            ->selectRaw("id, item_id, cabang_id, jumlah, tanggal, user_id, 'in' as tipe, null as jenis, null as status")
            ->when(! $user->isAdminHo(), fn ($q) => $q->where('cabang_id', $user->cabang_id));

        $outQuery = StockOut::with(['item', 'cabang', 'user'])
            ->selectRaw('id, item_id, cabang_id, jumlah, tanggal, user_id, "out" as tipe, jenis, status')
            ->when(! $user->isAdminHo(), fn ($q) => $q->where('cabang_id', $user->cabang_id));

        if ($request->filled('tipe') && $request->tipe === 'in') {
            $riwayat = StockIn::with(['item', 'cabang', 'user'])
                ->when(! $user->isAdminHo(), fn ($q) => $q->where('cabang_id', $user->cabang_id))
                ->when($request->filled('item'), fn ($q) => $q->whereHas('item', fn ($qq) => $qq->where('nama_items', 'like', '%'.$request->item.'%')))
                ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->dari))
                ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->sampai))
                ->latest('tanggal')->paginate(20)->withQueryString();

            return view('history.index', ['riwayat' => $riwayat, 'tipe' => 'in']);
        }

        if ($request->filled('tipe') && $request->tipe === 'out') {
            $riwayat = StockOut::with('item')
                ->when(! $user->isAdminHo(), fn ($q) => $q->where('cabang_id', $user->cabang_id))
                ->when($request->filled('item'), fn ($q) => $q->whereHas('item', fn ($qq) => $qq->where('nama_items', 'like', '%'.$request->item.'%')))
                ->when($request->filled('jenis'), fn ($q) => $q->where('jenis', $request->jenis))
                ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->dari))
                ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->sampai))
                ->latest('tanggal')->paginate(20)->withQueryString();

            return view('history.index', ['riwayat' => $riwayat, 'tipe' => 'out']);
        }

        if ($request->filled('tipe') && $request->tipe === 'approval') {
            $stockOutApprovals = StockOut::with(['item', 'cabang', 'user', 'approver'])
                ->whereIn('status', ['approved', 'rejected'])
                ->whereNotNull('approved_by')
                ->when(! $user->isAdminHo(), fn ($q) => $q->where('cabang_id', $user->cabang_id))
                ->when($request->filled('item'), fn ($q) => $q->whereHas('item', fn ($qq) => $qq->where('nama_items', 'like', '%'.$request->item.'%')))
                ->when($request->filled('dari'), fn ($q) => $q->whereDate('approved_at', '>=', $request->dari))
                ->when($request->filled('sampai'), fn ($q) => $q->whereDate('approved_at', '<=', $request->sampai))
                ->get()
                ->map(fn (StockOut $stockOut): array => [
                    'id' => $stockOut->id,
                    'tanggal' => $stockOut->approved_at ?? $stockOut->updated_at,
                    'item' => $stockOut->item->nama_items ?? '-',
                    'cabang' => $stockOut->cabang->nama_cabang ?? '-',
                    'jumlah' => $stockOut->jumlah,
                    'requester' => $stockOut->user->name ?? '-',
                    'type' => 'Approval Transaksi',
                    'status' => $stockOut->status,
                    'approver' => $stockOut->approver->name ?? '-',
                    'note' => $stockOut->catatan_approval ?: $stockOut->keterangan,
                    'history_route' => route('history.show', ['type' => 'approval-out', 'id' => $stockOut->id]),
                ]);
            $stockInApprovals = StockInEditRequest::with(['oldItem.cabang', 'newItem', 'requester', 'approver'])
                ->whereIn('status', ['approved', 'rejected'])
                ->when(! $user->isAdminHo(), fn ($q) => $q->whereHas('oldItem', fn ($qq) => $qq->where('cabang_id', $user->cabang_id)))
                ->when($request->filled('item'), fn ($q) => $q->whereHas('oldItem', fn ($qq) => $qq->where('nama_items', 'like', '%'.$request->item.'%')))
                ->when($request->filled('dari'), fn ($q) => $q->whereDate('approved_at', '>=', $request->dari))
                ->when($request->filled('sampai'), fn ($q) => $q->whereDate('approved_at', '<=', $request->sampai))
                ->get()
                ->map(fn (StockInEditRequest $editRequest): array => [
                    'id' => $editRequest->id,
                    'tanggal' => $editRequest->approved_at ?? $editRequest->updated_at,
                    'item' => $editRequest->oldItem->nama_items ?? '-',
                    'cabang' => $editRequest->oldItem->cabang->nama_cabang ?? '-',
                    'jumlah' => $editRequest->old_jumlah.' -> '.$editRequest->new_jumlah,
                    'requester' => $editRequest->requester->name ?? '-',
                    'type' => 'Edit Stok',
                    'status' => $editRequest->status,
                    'approver' => $editRequest->approver->name ?? '-',
                    'note' => $editRequest->catatan_approval ?: $editRequest->new_keterangan,
                    'history_route' => route('history.show', ['type' => 'approval-in', 'id' => $editRequest->id]),
                ]);
            $approvalHistory = $stockOutApprovals
                ->merge($stockInApprovals)
                ->sortByDesc('tanggal')
                ->values();

            return view('history.index', compact('approvalHistory') + ['tipe' => 'approval']);
        }

        // Default: tampilkan gabungan ringkas (barang masuk & keluar terbaru)
        $stockIns = (clone $inQuery)->latest('tanggal')->take(10)->get();
        $stockOuts = StockOut::with(['item', 'cabang', 'user'])
            ->when(! $user->isAdminHo(), fn ($q) => $q->where('cabang_id', $user->cabang_id))
            ->latest('tanggal')->take(10)->get();

        return view('history.index', ['stockIns' => $stockIns, 'stockOuts' => $stockOuts, 'tipe' => null]);
    }

    private function authorizeCabang(User $user, int $cabangId): void
    {
        if (! $user->isAdminHo() && $user->cabang_id !== $cabangId) {
            abort(403, 'Anda tidak memiliki akses ke histori cabang lain.');
        }
    }
}
