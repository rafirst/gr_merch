<?php

namespace App\Http\Controllers;

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

        $stockItems = Item::query()
            ->with('cabang')
            ->orderBy('nama_items')
            ->get()
            ->groupBy(fn (Item $item): string => json_encode([$item->kode_items, $item->nama_items], JSON_UNESCAPED_UNICODE))
            ->map(fn ($items): array => [
                'item' => $items->first(),
                'total_stok' => (int) $items->sum('stok_items'),
            ])
            ->values();

        return view('dashboard.index', compact(
            'totalItems', 'totalStok', 'pendingApproval',
            'bulanLabel', 'dataIn', 'dataOut', 'topItems', 'isAdmin', 'stockItems'
        ));
    }

    public function showStock(Item $item)
    {
        $item->load('cabang');
        $stockByCabang = Item::with('cabang')
            ->where('kode_items', $item->kode_items)
            ->where('nama_items', $item->nama_items)
            ->orderBy('cabang_id')
            ->get();
        $totalStock = (int) $stockByCabang->sum('stok_items');

        return view('dashboard.show', compact('item', 'stockByCabang', 'totalStock'));
    }
}
