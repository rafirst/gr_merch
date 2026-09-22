<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockItemsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        /** @var User $user */
        $user = Auth::user();
        $query = Item::with('cabang');

        if (! $user->isAdminHo()) {
            $query->where('cabang_id', $user->cabang_id);
        }

        return $query->orderBy('nama_items')->get();
    }

    public function headings(): array
    {
        return ['kode_items', 'nama_items', 'kategori', 'kode_cabang', 'stok_items', 'status_stok'];
    }

    public function map($item): array
    {
        return [
            $item->kode_items,
            $item->nama_items,
            $item->kategori,
            $item->cabang->kode_cabang ?? '-',
            $item->stok_items,
            $item->isStokMenipis() ? 'Menipis' : 'Aman',
        ];
    }
}
