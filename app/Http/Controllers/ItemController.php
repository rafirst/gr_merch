<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Item;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $query = Item::with('cabang');

        if (! $user->isAdminHo()) {
            $query->where('cabang_id', $user->cabang_id);
        } elseif ($request->filled('cabang_id')) {
            $query->where('cabang_id', $request->cabang_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_items', 'like', "%{$s}%")
                    ->orWhere('kode_items', 'like', "%{$s}%");
            });
        }

        $items = $query->orderBy('nama_items')->paginate(15)->withQueryString();
        $cabangs = Cabang::orderBy('nama_cabang')->get();

        return view('items.index', compact('items', 'cabangs'));
    }

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();
        $cabangs = $user->isAdminHo() ? Cabang::orderBy('nama_cabang')->get() : collect([$user->cabang]);
        $kategoriOptions = Item::kategoriOptions();

        return view('items.create', compact('cabangs', 'kategoriOptions'));
    }

    public function show(Item $item)
    {
        $this->authorizeCabang($item);
        /** @var User $user */
        $user = Auth::user();

        $item->load('cabang');
        $stockByCabang = Item::with('cabang')
            ->where('kode_items', $item->kode_items)
            ->where('nama_items', $item->nama_items)
            ->when(! $user->isAdminHo(), fn ($query) => $query->where('cabang_id', $user->cabang_id))
            ->orderBy('cabang_id')
            ->get();
        $totalStock = (int) $stockByCabang->sum('stok_items');

        return view('items.show', compact('item', 'stockByCabang', 'totalStock'));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $request->merge([
            'harga_items' => $this->normalizeHarga($request->input('harga_items')),
            'harga_jual' => $this->normalizeHarga($request->input('harga_jual')),
        ]);

        $data = $request->validate([
            'kode_items' => 'required|string|max:50',
            'nama_items' => 'required|string|max:150',
            'kategori' => ['nullable', Rule::in(Item::kategoriOptions())],
            'harga_items' => 'required|numeric|min:0',
            'harga_jual' => 'nullable|numeric|min:0',
            'cabang_id' => $user->isAdminHo() ? 'required|exists:cabangs,id' : 'nullable',
            'foto' => 'nullable|image|max:2048',
        ]);

        $data['stok_items'] = 0;

        if (! $user->isAdminHo()) {
            $data['cabang_id'] = $user->cabang_id;
        }

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('items', 'public');
        }

        Item::create($data);

        return redirect()->route('items.index')->with('success', 'Item berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        $this->authorizeCabang($item);
        /** @var User $user */
        $user = Auth::user();
        $cabangs = $user->isAdminHo() ? Cabang::orderBy('nama_cabang')->get() : collect([$user->cabang]);
        $kategoriOptions = Item::kategoriOptions();

        return view('items.edit', compact('item', 'cabangs', 'kategoriOptions'));
    }

    public function update(Request $request, Item $item)
    {
        $this->authorizeCabang($item);
        /** @var User $user */
        $user = Auth::user();
        $request->merge([
            'harga_items' => $this->normalizeHarga($request->input('harga_items')),
            'harga_jual' => $this->normalizeHarga($request->input('harga_jual')),
        ]);

        $data = $request->validate([
            'kode_items' => 'required|string|max:50',
            'nama_items' => 'required|string|max:150',
            'kategori' => ['nullable', Rule::in(Item::kategoriOptions())],
            'harga_items' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'cabang_id' => $user->isAdminHo() ? 'required|exists:cabangs,id' : 'nullable',
            'foto' => 'nullable|image|max:2048',
        ]);

        if (! $user->isAdminHo()) {
            unset($data['cabang_id']);
        }

        if ($request->hasFile('foto')) {
            if ($item->foto) {
                Storage::disk('public')->delete($item->foto);
            }
            $data['foto'] = $request->file('foto')->store('items', 'public');
        }

        $item->update($data);

        return redirect()->route('items.index')->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        $this->authorizeCabang($item);
        $item->delete();

        return redirect()->route('items.index')->with('success', 'Item berhasil dihapus.');
    }

    private function authorizeCabang(Item $item): void
    {
        /** @var User $user */
        $user = Auth::user();
        if (! $user->isAdminHo() && $item->cabang_id !== $user->cabang_id) {
            abort(403, 'Anda tidak memiliki akses ke item cabang lain.');
        }
    }

    private function normalizeHarga(mixed $harga): ?string
    {
        if ($harga === null || $harga === '') {
            return null;
        }

        $harga = (string) $harga;

        if (str_contains($harga, ',')) {
            return str_replace(',', '.', str_replace('.', '', $harga));
        }

        if (substr_count($harga, '.') === 1) {
            [$whole, $fraction] = explode('.', $harga, 2);

            if (strlen($fraction) < 3) {
                return $harga;
            }
        }

        return str_replace('.', '', $harga);
    }
}
