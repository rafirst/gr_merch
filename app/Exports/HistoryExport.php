<?php

namespace App\Exports;

use App\Models\StockIn;
use App\Models\StockInEditRequest;
use App\Models\StockOut;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HistoryExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  array{tipe?: string|null, jenis?: string|null, item?: string|null, dari?: string|null, sampai?: string|null}  $filters
     */
    public function __construct(private readonly array $filters = []) {}

    public function collection(): Collection
    {
        return match ($this->filters['tipe'] ?? null) {
            'in' => $this->stockInQuery()->latest('tanggal')->get(),
            'out' => $this->stockOutQuery()->latest('tanggal')->get(),
            'approval' => $this->approvalCollection(),
            default => $this->stockInQuery()->get()
                ->concat($this->stockOutQuery()->get())
                ->sortByDesc(fn (StockIn|StockOut $row) => $row->tanggal->getTimestamp())
                ->values(),
        };
    }

    private function stockInQuery(): Builder
    {
        $query = StockIn::with(['item', 'cabang', 'user']);

        $this->applyBranchScope($query);
        $this->applyItemAndDateFilters($query, 'tanggal');

        return $query;
    }

    private function stockOutQuery(): Builder
    {
        $query = StockOut::with(['item', 'cabang', 'user']);

        $this->applyBranchScope($query);
        $this->applyItemAndDateFilters($query, 'tanggal');

        if (($this->filters['tipe'] ?? null) === 'out') {
            $query->when($this->filters['jenis'] ?? null, fn (Builder $query, string $jenis) => $query->where('jenis', $jenis));
        }

        return $query;
    }

    private function approvalCollection(): Collection
    {
        $stockOutApprovals = StockOut::with(['item', 'cabang', 'user', 'approver'])
            ->whereIn('status', ['approved', 'rejected'])
            ->whereNotNull('approved_by');
        $this->applyBranchScope($stockOutApprovals);
        $this->applyItemAndDateFilters($stockOutApprovals, 'approved_at');

        $stockInApprovals = StockInEditRequest::with(['oldItem.cabang', 'requester', 'approver'])
            ->whereIn('status', ['approved', 'rejected']);

        /** @var User $user */
        $user = Auth::user();
        if (! $user->isAdminHo()) {
            $stockInApprovals->whereHas('oldItem', fn (Builder $query) => $query->where('cabang_id', $user->cabang_id));
        }

        if ($this->filters['item'] ?? null) {
            $stockInApprovals->whereHas('oldItem', fn (Builder $query) => $query->where('nama_items', 'like', '%'.$this->filters['item'].'%'));
        }

        $stockInApprovals
            ->when($this->filters['dari'] ?? null, fn (Builder $query, string $date) => $query->whereDate('approved_at', '>=', $date))
            ->when($this->filters['sampai'] ?? null, fn (Builder $query, string $date) => $query->whereDate('approved_at', '<=', $date));

        return $stockOutApprovals->get()
            ->concat($stockInApprovals->get())
            ->sortByDesc(fn (StockOut|StockInEditRequest $row) => ($row->approved_at ?? $row->updated_at)->getTimestamp())
            ->values();
    }

    private function applyBranchScope(Builder $query): void
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAdminHo()) {
            $query->where('cabang_id', $user->cabang_id);
        }
    }

    private function applyItemAndDateFilters(Builder $query, string $dateColumn): void
    {
        if ($this->filters['item'] ?? null) {
            $query->whereHas('item', fn (Builder $itemQuery) => $itemQuery->where('nama_items', 'like', '%'.$this->filters['item'].'%'));
        }

        $query
            ->when($this->filters['dari'] ?? null, fn (Builder $query, string $date) => $query->whereDate($dateColumn, '>=', $date))
            ->when($this->filters['sampai'] ?? null, fn (Builder $query, string $date) => $query->whereDate($dateColumn, '<=', $date));
    }

    public function headings(): array
    {
        return match ($this->filters['tipe'] ?? null) {
            'in' => ['Tanggal', 'Item', 'Cabang', 'Jumlah', 'Oleh'],
            'out' => ['Tanggal', 'Item', 'Jumlah', 'Jenis', 'Referensi', 'Status', 'Pembayaran'],
            'approval' => ['Tanggal', 'Item', 'Jumlah', 'User Pengaju', 'Tipe', 'Status', 'Approved By', 'Catatan'],
            default => $this->summaryHeadings(),
        };
    }

    /**
     * @return array<int, string>
     */
    private function summaryHeadings(): array
    {
        return ['Tanggal', 'Tipe Transaksi', 'Kode Items', 'Nama Items', 'Cabang', 'Jumlah', 'Sumber', 'Jenis', 'Status', 'Harga Jual', 'Total', 'Diinput Oleh'];
    }

    /**
     * @return array<int, mixed>
     */
    public function map(mixed $row): array
    {
        if (($this->filters['tipe'] ?? null) === 'approval') {
            if ($row instanceof StockInEditRequest) {
                return [
                    ($row->approved_at ?? $row->updated_at)->format('Y-m-d'),
                    $row->oldItem->nama_items ?? '-',
                    $row->old_jumlah.' -> '.$row->new_jumlah,
                    $row->requester->name ?? '-',
                    'Edit Stok',
                    ucfirst($row->status),
                    $row->approver->name ?? '-',
                    $row->catatan_approval ?: $row->new_keterangan ?: '-',
                ];
            }

            return [
                ($row->approved_at ?? $row->updated_at)->format('Y-m-d'),
                $row->item->nama_items ?? '-',
                $row->jumlah,
                $row->user->name ?? '-',
                'Approval Transaksi',
                ucfirst($row->status),
                $row->approver->name ?? '-',
                $row->catatan_approval ?: $row->keterangan ?: '-',
            ];
        }

        if (($this->filters['tipe'] ?? null) === 'in') {
            return [
                $row->tanggal->format('Y-m-d'),
                $row->item->nama_items ?? '-',
                $row->cabang->nama_cabang ?? '-',
                $row->jumlah,
                $row->user->name ?? '-',
            ];
        }

        if (($this->filters['tipe'] ?? null) === 'out') {
            return [
                $row->tanggal->format('Y-m-d'),
                $row->item->nama_items ?? '-',
                $row->jumlah,
                ucfirst($row->jenis),
                match ($row->jenis) {
                    'penjualan' => $row->nama_customer ?: '-',
                    'DO' => $row->nomor_spk ?: '-',
                    'request' => $row->nomor_im ?: '-',
                    default => '-',
                },
                ucfirst($row->status),
                $row->jenis_pembayaran ? ucfirst($row->jenis_pembayaran) : '-',
            ];
        }

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
