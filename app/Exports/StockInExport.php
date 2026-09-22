<?php

namespace App\Exports;

use App\Models\StockIn;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockInExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        /** @var User $user */
        $user = Auth::user();
        $query = StockIn::with(['item', 'cabang', 'user']);

        if (! $user->isAdminHo()) {
            $query->where('cabang_id', $user->cabang_id);
        }

        return $query->latest('tanggal')->get();
    }

    public function headings(): array
    {
        return ['kode_items', 'jumlah', 'kode_cabang', 'sumber'];
    }

    public function map($row): array
    {
        return [
            $row->item->kode_items ?? '-',
            $row->jumlah,
            $row->cabang->kode_cabang ?? '-',
            $row->sumber,
        ];
    }
}
