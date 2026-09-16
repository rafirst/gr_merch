<?php

namespace App\Http\Controllers;

use App\Models\StockOut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Hanya Admin HO yang bisa akses (dibatasi via route middleware 'role:admin_ho')
class ApprovalController extends Controller
{
    public function index()
    {
        $pendingList = StockOut::with(['item', 'cabang', 'user'])
            ->where('status', 'pending')
            ->latest('tanggal')
            ->paginate(15);

        return view('approval.index', compact('pendingList'));
    }

    public function approve(Request $request, StockOut $stockOut)
    {
        if ($stockOut->status !== 'pending') {
            return back()->with('error', 'Transaksi ini sudah diproses sebelumnya.');
        }

        $item = $stockOut->item;

        if ($stockOut->jumlah > $item->stok_items) {
            return back()->with('error', 'Stok tidak mencukupi untuk approve transaksi ini. Stok saat ini: '.$item->stok_items);
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

        return back()->with('success', 'Transaksi berhasil di-approve, stok telah dipotong.');
    }

    public function reject(Request $request, StockOut $stockOut)
    {
        if ($stockOut->status !== 'pending') {
            return back()->with('error', 'Transaksi ini sudah diproses sebelumnya.');
        }

        $stockOut->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'catatan_approval' => $request->input('catatan_approval'),
        ]);

        return back()->with('success', 'Transaksi berhasil ditolak.');
    }
}
