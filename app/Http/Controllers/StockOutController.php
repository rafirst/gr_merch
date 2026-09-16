<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockOut;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockOutController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $query = StockOut::with(['item', 'cabang', 'user', 'approver']);

        if (! $user->isAdminHo()) {
            $query->where('cabang_id', $user->cabang_id);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $stockOuts = $query->latest('tanggal')->paginate(15)->withQueryString();

        return view('stockout.index', compact('stockOuts'));
    }

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();
        $items = $user->isAdminHo() ? Item::orderBy('nama_items')->get() : Item::where('cabang_id', $user->cabang_id)->orderBy('nama_items')->get();

        return view('stockout.create', compact('items'));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'item_id' => 'required|exists:items,id',
            'jumlah' => 'required|integer|min:1',
            'jenis' => 'required|in:penjualan,hadiah,request',
            'harga_jual' => 'nullable|numeric|min:0',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $item = Item::findOrFail($data['item_id']);

        if (! $user->isAdminHo() && $item->cabang_id !== $user->cabang_id) {
            abort(403, 'Anda tidak memiliki akses ke item cabang lain.');
        }

        if ($data['jumlah'] > $item->stok_items) {
            return back()->withErrors(['jumlah' => 'Stok tidak mencukupi. Stok tersedia: '.$item->stok_items])->withInput();
        }

        // Aturan: Penjualan langsung approved & potong stok. Hadiah/Request perlu approval dulu (status pending).
        $isPenjualan = $data['jenis'] === 'penjualan';

        $hargaJual = $isPenjualan ? ($data['harga_jual'] ?? $item->harga_items) : null;
        $total = $isPenjualan ? $hargaJual * $data['jumlah'] : null;

        DB::transaction(function () use ($data, $item, $user, $isPenjualan, $hargaJual, $total) {
            $stockOut = StockOut::create([
                'item_id' => $item->id,
                'cabang_id' => $item->cabang_id,
                'jumlah' => $data['jumlah'],
                'jenis' => $data['jenis'],
                'harga_jual' => $hargaJual,
                'total' => $total,
                'status' => $isPenjualan ? 'approved' : 'pending',
                'tanggal' => $data['tanggal'],
                'keterangan' => $data['keterangan'] ?? null,
                'user_id' => $user->id,
                'approved_by' => $isPenjualan ? $user->id : null,
                'approved_at' => $isPenjualan ? now() : null,
            ]);

            if ($isPenjualan) {
                $item->decrement('stok_items', $data['jumlah']);
            }
        });

        $pesan = $isPenjualan
            ? 'Penjualan berhasil dicatat & stok berkurang.'
            : 'Permintaan barang keluar berhasil diajukan, menunggu approval Admin Pusat.';

        return redirect()->route('stockout.index')->with('success', $pesan);
    }
}
