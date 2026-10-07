@extends('layouts.app')
@section('title', 'Detail Barang Keluar')

@section('content')
<div class="card">
    <div class="card-header stockout-detail-header">
        <h3 class="card-title mb-0">Detail Barang Keluar</h3>
        <div class="stockout-detail-actions">
            @if($stockOut->status === 'approved')
                <a href="{{ route('stockout.invoice', $stockOut) }}" class="btn btn-sm stockout-invoice-button" title="Cetak invoice" aria-label="Cetak invoice" target="_blank" rel="noopener">
                    <i class="fas fa-print"></i> Invoice
                </a>
            @endif
            <a href="{{ route('stockout.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        @php
            $subtotal = $stockOuts->sum(function ($row) {
                $unitPrice = $row->jenis === 'penjualan'
                    ? (float) ($row->item->harga_jual ?? 0)
                    : ($row->harga_jual !== null
                        ? (float) $row->harga_jual
                        : ($row->jenis === 'DO' ? (float) ($row->item->harga_items ?? 0) : 0));

                return $unitPrice * (int) $row->jumlah;
            });
            $ppnAmount = $stockOut->jenis === 'DO' ? (int) round($subtotal * 0.11) : 0;
            $totalAkhir = match ($stockOut->jenis) {
                'DO' => $subtotal + $ppnAmount,
                'penjualan' => $stockOuts->sum(fn ($row) => (float) ($row->item->harga_jual ?? 0)
                    * (int) $row->jumlah
                    * (1 - ((float) ($row->discount ?? 0) / 100))),
                default => $stockOuts->sum(fn ($row) => $row->total !== null ? (float) $row->total : 0),
            };
            $discountAmount = $stockOut->jenis === 'DO'
                ? 0
                : $subtotal - $totalAkhir;
        @endphp

        @if($stockOut->jenis === 'penjualan')
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
                        @php($isMemberSale = $stockOut->isTagMemberSale())
                        @if($isMemberSale)
                            <tr><td>Jabatan</td><td>{{ $stockOut->jabatan ?: '-' }}</td></tr>
                        @else
                            <tr><td>NIK KTP</td><td>{{ $stockOut->nik_ktp ?: '-' }}</td></tr>
                        @endif
                        <tr><td>Nomor Telpon</td><td>{{ $stockOut->nomor_telepon ?: '-' }}</td></tr>
                        <tr><td>Alamat</td><td>{{ $stockOut->alamat_customer ?: '-' }}</td></tr>
                    </tbody>
                </table>
            </div>
        @endif

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
                    @if($stockOut->jenis === 'penjualan')
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
                    <tr><td>Potongan</td><td>{{ $stockOut->jenis === 'DO' ? 'Rp '.number_format($discountAmount, 0, ',', '.') : $stockOut->discountLabel() }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="stockout-section">
            <h4 class="stockout-section-title">Payment</h4>
            <table class="stockout-table stockout-info-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Kode Pembayaran</td>
                        <td>
                            @if($stockOut->kode_pembayaran)
                                <span class="stockout-payment-code">{{ $stockOut->kode_pembayaran }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Bukti Pembayaran</td>
                        <td>
                            @if($stockOut->bukti_pembayaran)
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($stockOut->bukti_pembayaran) }}" class="stockout-payment-link" target="_blank" rel="noopener">
                                    <i class="fas fa-file-pdf"></i> Lihat Bukti Pembayaran
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
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
                            <th>Harga Jual</th>
                            <th>Jumlah</th>
                            <th>Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockOuts as $row)
                            <tr>
                                <td>{{ $row->item->nama_items ?? '-' }}</td>
                                <td>{{ $row->item->kode_items ?? '-' }}</td>
                                @php($unitPrice = $stockOut->jenis === 'penjualan' ? (float) ($row->item->harga_jual ?? 0) : ($row->harga_jual !== null ? (float) $row->harga_jual : ($stockOut->jenis === 'DO' ? (float) ($row->item->harga_items ?? 0) : null)))
                                @php($lineTotal = $stockOut->jenis === 'penjualan' ? $unitPrice * (int) $row->jumlah : ($row->total !== null ? (float) $row->total : ($stockOut->jenis === 'DO' ? (float) ($row->item->harga_items ?? 0) * (int) $row->jumlah : null)))
                                <td>{{ $unitPrice !== null ? 'Rp '.number_format($unitPrice, 0, ',', '.') : '-' }}</td>
                                <td>{{ $row->jumlah }} {{ $row->item->satuan ?? '' }}</td>
                                <td>{{ $lineTotal !== null ? 'Rp '.number_format($lineTotal, 0, ',', '.') : '-' }}</td>
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
                            <th>Diskon</th>
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

    .stockout-detail-actions {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        gap: 0.5rem;
        margin-left: auto;
    }

    .stockout-detail-actions .btn {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .stockout-invoice-button {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.28) !important;
        background: linear-gradient(180deg, #ffe477 0%, #e7a900 48%, #a76500 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.42), 0 2px 5px rgba(0, 0, 0, 0.28);
        color: #271900 !important;
        font-weight: 700;
    }

    .stockout-invoice-button::before {
        position: absolute;
        top: 0;
        right: 12%;
        left: 12%;
        height: 42%;
        border-radius: inherit;
        background: rgba(255, 255, 255, 0.3);
        content: '';
        filter: blur(1px);
        pointer-events: none;
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

    .stockout-payment-code {
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        color: #7ef0aa;
        font-family: SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 0.78rem;
        font-weight: 700;
        text-overflow: ellipsis;
        vertical-align: middle;
        white-space: nowrap;
    }

    .stockout-payment-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: #ffe477;
        font-weight: 700;
        text-decoration: none;
    }

    .stockout-payment-link:hover,
    .stockout-payment-link:focus {
        color: #fff0a8;
        text-decoration: underline;
    }

</style>
@endpush