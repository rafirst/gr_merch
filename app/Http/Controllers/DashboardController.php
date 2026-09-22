<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user->isAdminHo();

        $itemQuery = Item::query();
        $stockInQuery = StockIn::query();
        $stockOutQuery = StockOut::query();

        if (! $isAdmin) {
            $itemQuery->where('cabang_id', $user->cabang_id);
            $stockInQuery->where('cabang_id', $user->cabang_id);
            $stockOutQuery->where('cabang_id', $user->cabang_id);
        }

        $totalItems = (clone $itemQuery)->count();
        $totalStok = (clone $itemQuery)->sum('stok_items');
        $pendingApproval = (clone $stockOutQuery)->where('status', 'pending')->count();

        // Grafik in vs out 6 bulan terakhir
        $bulanLabel = [];
        $dataIn = [];
        $dataOut = [];
        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $bulanLabel[] = $bulan->translatedFormat('M Y');

            $dataIn[] = (clone $stockInQuery)
                ->whereYear('tanggal', $bulan->year)
                ->whereMonth('tanggal', $bulan->month)
                ->sum('jumlah');

            $dataOut[] = (clone $stockOutQuery)
                ->where('status', 'approved')
                ->whereYear('tanggal', $bulan->year)
                ->whereMonth('tanggal', $bulan->month)
                ->sum('jumlah');
        }

        $topItems = (clone $itemQuery)
            ->withSum(['stockOuts as total_terjual' => function ($q) {
                $q->where('jenis', 'penjualan')->where('status', 'approved');
            }], 'jumlah')
            ->orderByDesc('total_terjual')
            ->take(5)
            ->get();

        $stockItems = (clone $itemQuery)
            ->with('cabang')
            ->orderBy('nama_items')
            ->get();
        $cabangs = $isAdmin
            ? Cabang::orderBy('nama_cabang')->get()
            : Cabang::whereKey($user->cabang_id)->get();

        return view('dashboard.index', compact(
            'totalItems', 'totalStok', 'pendingApproval',
            'bulanLabel', 'dataIn', 'dataOut', 'topItems', 'isAdmin', 'stockItems', 'cabangs'
        ));
    }
}
