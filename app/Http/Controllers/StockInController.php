<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockInEditRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockInController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $query = Item::with(['cabang', 'latestStockIn'])
            ->whereHas('stockIns');

        if (! $user->isAdminHo()) {
            $query->where('cabang_id', $user->cabang_id);
        } elseif ($request->filled('cabang_id')) {
            $query->where('cabang_id', $request->cabang_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($itemQuery) use ($search): void {
                $itemQuery->where('kode_items', 'like', "%{$search}%")
                    ->orWhere('nama_items', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('nama_items')->paginate(15)->withQueryString();
        $cabangs = Cabang::orderBy('nama_cabang')->get();

        return view('stockin.index', compact('items', 'cabangs'));
    }

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();
        $items = $user->isAdminHo() ? Item::orderBy('nama_items')->get() : Item::where('cabang_id', $user->cabang_id)->orderBy('nama_items')->get();

        return view('stockin.create', compact('items'));
    }

    public function show(StockIn $stockIn)
    {
        $this->authorizeCabang($stockIn);
        $stockIn->load([
            'item.stockIns' => fn ($query) => $query->with(['cabang', 'user'])->latest('tanggal')->latest('id'),
            'cabang',
            'user',
        ]);

        return view('stockin.show', compact('stockIn'));
    }

    public function edit(StockIn $stockIn)
    {
        $this->authorizeCabang($stockIn);
        /** @var User $user */
        $user = Auth::user();
        $items = $user->isAdminHo()
            ? Item::orderBy('nama_items')->get()
            : Item::where('cabang_id', $user->cabang_id)->orderBy('nama_items')->get();

        $stockIn->load(['item', 'cabang']);

        return view('stockin.edit', compact('stockIn', 'items'));
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

    public function update(Request $request, StockIn $stockIn)
    {
        $this->authorizeCabang($stockIn);
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'item_id' => 'required|exists:items,id',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'sumber' => 'nullable|string|max:150',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $newItem = Item::findOrFail($data['item_id']);
        if (! $user->isAdminHo() && $newItem->cabang_id !== $user->cabang_id) {
            abort(403, 'Anda tidak memiliki akses ke item cabang lain.');
        }

        if (! $user->isAdminHo()) {
            if (StockInEditRequest::where('stock_in_id', $stockIn->id)->where('status', 'pending')->exists()) {
                return back()->with('error', 'Perubahan barang masuk ini masih menunggu approval administrator.');
            }

            StockInEditRequest::create([
                'stock_in_id' => $stockIn->id,
                'old_item_id' => $stockIn->item_id,
                'new_item_id' => $newItem->id,
                'old_jumlah' => $stockIn->jumlah,
                'new_jumlah' => $data['jumlah'],
                'old_tanggal' => $stockIn->tanggal,
                'new_tanggal' => $data['tanggal'],
                'old_sumber' => $stockIn->sumber,
                'new_sumber' => $data['sumber'] ?? null,
                'old_keterangan' => $stockIn->keterangan,
                'new_keterangan' => $data['keterangan'] ?? null,
                'requested_by' => $user->id,
                'status' => 'pending',
            ]);

            return redirect()->route('stockin.index')->with('success', 'Perubahan barang masuk diajukan dan menunggu approval administrator.');
        }

        DB::transaction(function () use ($data, $stockIn) {
            $oldItem = Item::whereKey($stockIn->item_id)->lockForUpdate()->firstOrFail();
            $newItem = Item::whereKey($data['item_id'])->lockForUpdate()->firstOrFail();

            if ($oldItem->is($newItem)) {
                $quantityDifference = (int) $data['jumlah'] - (int) $stockIn->jumlah;
                if ($quantityDifference > 0 && $oldItem->stok_items < $quantityDifference) {
                    throw ValidationException::withMessages([
                        'jumlah' => 'Stok saat ini tidak mencukupi untuk menambah jumlah barang masuk.',
                    ]);
                }

                if ($quantityDifference < 0 && $oldItem->stok_items < abs($quantityDifference)) {
                    throw ValidationException::withMessages([
                        'jumlah' => 'Jumlah tidak dapat dikurangi karena sebagian stok sudah terpakai.',
                    ]);
                }

                if ($quantityDifference > 0) {
                    $oldItem->increment('stok_items', $quantityDifference);
                } elseif ($quantityDifference < 0) {
                    $oldItem->decrement('stok_items', abs($quantityDifference));
                }
            } else {
                if ($oldItem->stok_items < $stockIn->jumlah) {
                    throw ValidationException::withMessages([
                        'item_id' => 'Barang masuk ini tidak dapat dipindahkan karena stok item sebelumnya sudah terpakai.',
                    ]);
                }

                $oldItem->decrement('stok_items', $stockIn->jumlah);
                $newItem->increment('stok_items', $data['jumlah']);
            }

            $stockIn->update([
                'item_id' => $newItem->id,
                'cabang_id' => $newItem->cabang_id,
                'jumlah' => $data['jumlah'],
                'tanggal' => $data['tanggal'],
                'sumber' => $data['sumber'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
            ]);
        });

        return redirect()->route('stockin.index')->with('success', 'Barang masuk berhasil diperbarui.');
    }

    public function destroy(StockIn $stockIn)
    {
        $this->authorizeCabang($stockIn);

        $deleted = DB::transaction(function () use ($stockIn): bool {
            $item = Item::whereKey($stockIn->item_id)->lockForUpdate()->firstOrFail();
            if ($item->stok_items < $stockIn->jumlah) {
                return false;
            }

            $item->decrement('stok_items', $stockIn->jumlah);
            $stockIn->delete();

            return true;
        });

        if (! $deleted) {
            return back()->with('error', 'Barang masuk tidak dapat dihapus karena stoknya sudah terpakai.');
        }

        return redirect()->route('stockin.index')->with('success', 'Barang masuk berhasil dihapus.');
    }

    private function authorizeCabang(StockIn $stockIn): void
    {
        /** @var User $user */
        $user = Auth::user();
        if (! $user->isAdminHo() && $stockIn->cabang_id !== $user->cabang_id) {
            abort(403, 'Anda tidak memiliki akses ke barang masuk cabang lain.');
        }
    }
}
