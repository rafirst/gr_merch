<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockIn;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockInController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $query = StockIn::with(['item', 'cabang', 'user']);

        if (! $user->isAdminHo()) {
            $query->where('cabang_id', $user->cabang_id);
        }

        $stockIns = $query->latest('tanggal')->paginate(15);

        return view('stockin.index', compact('stockIns'));
    }

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();
        $items = $user->isAdminHo() ? Item::orderBy('nama_items')->get() : Item::where('cabang_id', $user->cabang_id)->orderBy('nama_items')->get();

        return view('stockin.create', compact('items'));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'item_id' => 'required|exists:items,id',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'sumber' => 'nullable|string|max:150',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $item = Item::findOrFail($data['item_id']);

        if (! $user->isAdminHo() && $item->cabang_id !== $user->cabang_id) {
            abort(403, 'Anda tidak memiliki akses ke item cabang lain.');
        }

        DB::transaction(function () use ($data, $item, $user) {
            StockIn::create([
                'item_id' => $item->id,
                'cabang_id' => $item->cabang_id,
                'jumlah' => $data['jumlah'],
                'tanggal' => $data['tanggal'],
                'sumber' => $data['sumber'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
                'user_id' => $user->id,
            ]);

            // Barang masuk langsung menambah stok
            $item->increment('stok_items', $data['jumlah']);
        });

        return redirect()->route('stockin.index')->with('success', 'Barang masuk berhasil dicatat & stok bertambah.');
    }
}
