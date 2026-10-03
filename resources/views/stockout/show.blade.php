@extends('layouts.app')
@section('title', 'Detail Barang Keluar')

@section('content')
<div class="card">
    <div class="card-header stockout-detail-header">
        <h3 class="card-title mb-0">Detail Barang Keluar</h3>
        <a href="{{ route('stockout.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        @php
            $subtotal = $stockOuts->sum(function ($row) {
                $unitPrice = $row->harga_jual !== null
                    ? (float) $row->harga_jual
                    : ($row->jenis === 'DO' ? (float) ($row->item->harga_items ?? 0) : 0);

                return $unitPrice * (int) $row->jumlah;
            });
            $ppnAmount = $stockOut->jenis === 'DO' ? (int) round($subtotal * 0.11) : 0;
            $totalAkhir = $stockOut->jenis === 'DO'
                ? $subtotal + $ppnAmount
                : $stockOuts->sum(fn ($row) => $row->total !== null ? (float) $row->total : 0);
            $discountAmount = $stockOut->jenis === 'DO'
                ? 0
                : $subtotal - $totalAkhir;
        @endphp

        <div class="stockout-section">
            <h4 class="stockout-section-title">Data Customer</h4>
            <table class="stockout-table stockout-info-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Nama Customer</td><td>{{ $stockOut->nama_customer ?: '-' }}</td></tr>
                    <tr><td>NIK KTP</td><td>{{ $stockOut->nik_ktp ?: '-' }}</td></tr>
                    <tr><td>Nomor Telpon</td><td>{{ $stockOut->nomor_telepon ?: '-' }}</td></tr>
                    <tr><td>Alamat</td><td>{{ $stockOut->alamat_customer ?: '-' }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="stockout-section">
            <h4 class="stockout-section-title">Data Transaksi</h4>
            <table class="stockout-table stockout-info-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @if($stockOut->jenis === 'request')
                        <tr><td>Nomor IM</td><td>{{ $stockOut->nomor_im ?: '-' }}</td></tr>
                    @elseif($stockOut->jenis === 'DO')
                        <tr><td>Nomor SPK</td><td>{{ $stockOut->nomor_spk ?: '-' }}</td></tr>
                        <tr><td>Paket Bundling</td><td>{{ match ($stockOut->paket_bundling) { 'paket_a' => 'Paket A', 'paket_b' => 'Paket B', 'paket_c' => 'Paket C', default => '-' } }}</td></tr>
                    @endif
                    <tr><td>PIC Penjualan</td><td>{{ $stockOut->pic_penjualan ?: '-' }}</td></tr>
                    <tr><td>Cabang</td><td>{{ $stockOut->cabang->nama_cabang ?? '-' }}</td></tr>
                    <tr><td>Jenis Transaksi</td><td>{{ ucfirst($stockOut->jenis) }}</td></tr>
                    @if(in_array($stockOut->jenis, ['penjualan', 'DO'], true))
                        <tr>
                            <td>Jenis Pembayaran</td>
                            <td>{{ match ($stockOut->jenis_pembayaran) { 'qris' => 'QRIS', 'transfer' => 'Transfer', default => '-' } }}</td>
                        </tr>
                    @endif
                    <tr><td>Tanggal</td><td>{{ $stockOut->tanggal?->format('d-m-Y') ?? '-' }}</td></tr>
                    <tr><td>Status</td><td>{{ ucfirst($stockOut->status) }}</td></tr>
                    <tr><td>Oleh</td><td>{{ $stockOut->user->name ?? '-' }}</td></tr>
                    <tr><td>Disetujui Oleh</td><td>{{ $stockOut->approver->name ?? '-' }}</td></tr>
                    <tr><td>Keterangan</td><td>{{ $stockOut->keterangan ?: '-' }}</td></tr>
                    <tr><td>Catatan Approval</td><td>{{ $stockOut->catatan_approval ?: '-' }}</td></tr>
                    <tr><td>Potongan</td><td>{{ $stockOut->jenis === 'DO' ? 'Rp '.number_format($discountAmount, 0, ',', '.') : rtrim(rtrim(number_format($stockOut->discount ?? 0, 2, '.', ''), '0'), '.').'%' }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="stockout-section">
            <h4 class="stockout-section-title">Detail Barang:</h4>
            <div class="stockout-table-wrapper">
                <table class="stockout-table stockout-items-table">
                    <thead>
                        <tr>
                            <th>Nama Item</th>
                            <th>Kode</th>
                            <th>Harga Satuan</th>
                            <th>Jumlah</th>
                            <th>Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockOuts as $row)
                            <tr>
                                <td>{{ $row->item->nama_items ?? '-' }}</td>
                                <td>{{ $row->item->kode_items ?? '-' }}</td>
                                <td>{{ $row->harga_jual !== null ? 'Rp '.number_format($row->harga_jual, 0, ',', '.') : ($stockOut->jenis === 'DO' ? 'Rp '.number_format($row->item->harga_items ?? 0, 0, ',', '.') : '-') }}</td>
                                <td>{{ $row->jumlah }} {{ $row->item->satuan ?? '' }}</td>
                                <td>{{ $row->total !== null ? 'Rp '.number_format($row->total, 0, ',', '.') : ($stockOut->jenis === 'DO' ? 'Rp '.number_format(($row->item->harga_items ?? 0) * $row->jumlah, 0, ',', '.') : '-') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="stockout-section">
            <h4 class="stockout-section-title">Ringkasan:</h4>
            <div class="stockout-table-wrapper">
                <table class="stockout-table stockout-summary-table">
                    <thead>
                        <tr>
                            <th>Subtotal</th>
                            <th>{{ $stockOut->jenis === 'DO' ? 'Potongan' : 'Diskon '.rtrim(rtrim(number_format($stockOut->discount ?? 0, 2, '.', ''), '0'), '.').'%' }}</th>
                            <th>Total Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $subtotal > 0 ? 'Rp '.number_format($subtotal, 0, ',', '.') : '-' }}</td>
                            <td>{{ $discountAmount > 0 ? 'Rp '.number_format($discountAmount, 0, ',', '.') : '-' }}</td>
                            <td>{{ $totalAkhir > 0 ? 'Rp '.number_format($totalAkhir, 0, ',', '.') : '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stockout-detail-header {
        display: flex;
        align-items: center;
        justify-content: flex-start;
    }

    .stockout-detail-header .card-title {
        margin-right: auto !important;
    }

    .stockout-detail-header .btn {
        flex: 0 0 auto;
        margin-left: auto;
    }

    .stockout-section {
        margin-top: 1.5rem;
    }

    .stockout-section:first-child {
        margin-top: 0;
    }

    .stockout-section-title {
        margin: 0 0 0.65rem;
        color: #ffffff;
        font-size: 1rem;
        font-weight: 700;
    }

    .stockout-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .stockout-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        color: #f4f7fa;
    }

    .stockout-table th,
    .stockout-table td {
        padding: 0.75rem 0.7rem;
        border-right: 1px solid var(--gazoo-border);
        border-bottom: 1px solid var(--gazoo-border);
        text-align: left;
        vertical-align: middle;
    }

    .stockout-table th:first-child,
    .stockout-table td:first-child {
        border-left: 1px solid var(--gazoo-border);
    }

    .stockout-table thead th {
        background: rgba(0, 0, 0, 0.22);
        color: #ffffff;
        font-weight: 700;
    }

    .stockout-table tbody td {
        color: rgba(244, 247, 250, 0.88);
    }

    .stockout-info-table th:first-child,
    .stockout-info-table td:first-child {
        width: 28%;
    }

    .stockout-info-table thead th:first-child,
    .stockout-items-table thead th:first-child,
    .stockout-summary-table thead th:first-child {
        border-top-left-radius: 8px;
    }

    .stockout-info-table thead th:last-child,
    .stockout-items-table thead th:last-child,
    .stockout-summary-table thead th:last-child {
        border-top-right-radius: 8px;
    }

    .stockout-info-table tbody tr:last-child td:first-child,
    .stockout-items-table tbody tr:last-child td:first-child,
    .stockout-summary-table tbody tr:last-child td:first-child {
        border-bottom-left-radius: 8px;
    }

    .stockout-info-table tbody tr:last-child td:last-child,
    .stockout-items-table tbody tr:last-child td:last-child,
    .stockout-summary-table tbody tr:last-child td:last-child {
        border-bottom-right-radius: 8px;
    }

    .stockout-items-table,
    .stockout-summary-table {
        min-width: 680px;
    }

    .stockout-summary-table th,
    .stockout-summary-table td {
        width: 33.333%;
    }

</style>
@endpush