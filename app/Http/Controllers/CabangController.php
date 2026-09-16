<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use Illuminate\Http\Request;

class CabangController extends Controller
{
    public function index()
    {
        $cabangs = Cabang::withCount('items', 'users')->paginate(10);

        return view('cabang.index', compact('cabangs'));
    }

    public function create()
    {
        return view('cabang.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_cabang' => 'required|string|max:20|unique:cabangs,kode_cabang',
            'nama_cabang' => 'required|string|max:150',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:30',
        ]);

        Cabang::create($data);

        return redirect()->route('cabang.index')->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function edit(Cabang $cabang)
    {
        return view('cabang.edit', compact('cabang'));
    }

    public function update(Request $request, Cabang $cabang)
    {
        $data = $request->validate([
            'kode_cabang' => 'required|string|max:20|unique:cabangs,kode_cabang,'.$cabang->id,
            'nama_cabang' => 'required|string|max:150',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:30',
        ]);

        $cabang->update($data);

        return redirect()->route('cabang.index')->with('success', 'Cabang berhasil diperbarui.');
    }

    public function destroy(Cabang $cabang)
    {
        $cabang->delete();

        return redirect()->route('cabang.index')->with('success', 'Cabang berhasil dihapus.');
    }
}
