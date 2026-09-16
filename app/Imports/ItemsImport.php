<?php

namespace App\Imports;

use App\Models\Cabang;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

/**
 * Format kolom Excel yang diharapkan (header baris pertama):
 * kode_items | nama_items | kategori | harga_items | stok_items | stok_minimum | satuan | kode_cabang
 *
 * Jika kode_items + cabang sudah ada -> data akan di-update (termasuk stok).
 * Jika belum ada -> akan dibuat item baru.
 */
class ItemsImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row): void
    {
        $data = $row->toArray();
        /** @var User $user */
        $user = Auth::user();

        $cabang = $user->isAdminHo()
            ? Cabang::where('kode_cabang', $data['kode_cabang'] ?? null)->first()
            : $user->cabang;

        if (! $cabang || empty($data['kode_items']) || empty($data['nama_items'])) {
            return; // baris tidak valid dilewati
        }

        Item::updateOrCreate(
            ['kode_items' => $data['kode_items'], 'cabang_id' => $cabang->id],
            [
                'nama_items' => $data['nama_items'],
                'kategori' => $data['kategori'] ?? null,
                'harga_items' => $data['harga_items'] ?? 0,
                'stok_items' => $data['stok_items'] ?? 0,
                'stok_minimum' => $data['stok_minimum'] ?? 5,
                'satuan' => $data['satuan'] ?? 'pcs',
            ]
        );
    }
}
