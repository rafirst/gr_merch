<?php

namespace App\Exports;

use App\Models\StockOut;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HistoryExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        /** @var User $user */
        $user = Auth::user();
        $query = StockOut::with(['item', 'cabang', 'user']);

        if (! $user->isAdminHo()) {
            $query->where('cabang_id', $user->cabang_id);
        }

        return $query->latest('tanggal')->get();
    }

    public function headings(): array
    {
        return ['Tanggal', 'Kode Items', 'Nama Items', 'Cabang', 'Jumlah', 'Jenis', 'Status', 'Harga Jual', 'Total', 'Diinput Oleh'];
    }

    public function map($row): array
    {
        return [
            $row->tanggal->format('Y-m-d'),
            $row->item->kode_items ?? '-',
            $row->item->nama_items ?? '-',
            $row->cabang->nama_cabang ?? '-',
            $row->jumlah,
            ucfirst($row->jenis),
            ucfirst($row->status),
            $row->harga_jual,
            $row->total,
            $row->user->name ?? '-',
        ];
    }
}
