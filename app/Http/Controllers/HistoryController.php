<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
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
            $riwayat = StockOut::with(['item', 'cabang', 'user', 'approver'])
                ->when(! $user->isAdminHo(), fn ($q) => $q->where('cabang_id', $user->cabang_id))
                ->when($request->filled('item'), fn ($q) => $q->whereHas('item', fn ($qq) => $qq->where('nama_items', 'like', '%'.$request->item.'%')))
                ->when($request->filled('jenis'), fn ($q) => $q->where('jenis', $request->jenis))
                ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->dari))
                ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->sampai))
                ->latest('tanggal')->paginate(20)->withQueryString();

            return view('history.index', ['riwayat' => $riwayat, 'tipe' => 'out']);
        }

        // Default: tampilkan gabungan ringkas (barang masuk & keluar terbaru)
        $stockIns = (clone $inQuery)->latest('tanggal')->take(10)->get();
        $stockOuts = StockOut::with(['item', 'cabang', 'user'])
            ->when(! $user->isAdminHo(), fn ($q) => $q->where('cabang_id', $user->cabang_id))
            ->latest('tanggal')->take(10)->get();

        return view('history.index', ['stockIns' => $stockIns, 'stockOuts' => $stockOuts, 'tipe' => null]);
    }
}
