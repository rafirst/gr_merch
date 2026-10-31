<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        /** @var User $user */
        $user = Auth::user();
        $query = Item::with('cabang');

        if (! $user->canViewAllData()) {
            $query->where('cabang_id', $user->cabang_id);
        }

        return $query->orderBy('nama_items')->get();
    }

    public function headings(): array
    {
        return ['Kode Items', 'Nama Items', 'Kategori', 'Harga Items', 'Stok Items', 'Harga Jual', 'Cabang'];
    }

    public function map($item): array
    {
        return [
            $item->kode_items,
            $item->nama_items,
            $item->kategori,
            $item->harga_items,
            $item->stok_items,
            $item->harga_jual,
            $item->cabang->nama_cabang ?? '-',
        ];
    }
}
