<?php

namespace App\Exports;

use App\Models\StockIn;
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
        $stockIns = StockIn::with(['item', 'cabang', 'user']);
        $stockOuts = StockOut::with(['item', 'cabang', 'user']);

        if (! $user->isAdminHo()) {
            $stockIns->where('cabang_id', $user->cabang_id);
            $stockOuts->where('cabang_id', $user->cabang_id);
        }

        return $stockIns->get()
            ->concat($stockOuts->get())
            ->sortByDesc(fn (StockIn|StockOut $row) => $row->tanggal->getTimestamp())
            ->values();
    }

    public function headings(): array
    {
        return ['Tanggal', 'Tipe Transaksi', 'Kode Items', 'Nama Items', 'Cabang', 'Jumlah', 'Sumber', 'Jenis', 'Status', 'Harga Jual', 'Total', 'Diinput Oleh'];
    }

    /**
     * @param  StockIn|StockOut  $row
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        $isStockIn = $row instanceof StockIn;

        return [
            $row->tanggal->format('Y-m-d'),
            $isStockIn ? 'Barang Masuk' : 'Barang Keluar',
            $row->item->kode_items ?? '-',
            $row->item->nama_items ?? '-',
            $row->cabang->nama_cabang ?? '-',
            $row->jumlah,
            $isStockIn ? ($row->sumber ?: '-') : '-',
            $isStockIn ? '-' : ucfirst($row->jenis),
            $isStockIn ? '-' : ucfirst($row->status),
            $isStockIn ? '-' : $row->harga_jual,
            $isStockIn ? '-' : $row->total,
            $row->user->name ?? '-',
        ];
    }
}
