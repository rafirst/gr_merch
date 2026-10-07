@extends('layouts.app')
@section('title', 'Detail Barang Keluar')

@section('content')
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
    $discountDisplay = $stockOut->jenis === 'DO'
        ? 'Rp '.number_format($discountAmount, 0, ',', '.')
        : $stockOut->discountLabel();
    $bundleDisplay = match ($stockOut->paket_bundling) {
        'paket_a' => 'Paket A',
        'paket_b' => 'Paket B',
        'paket_c' => 'Paket C',
        default => '-',
    };
    $paymentDisplay = match ($stockOut->jenis_pembayaran) {
        'qris' => 'QRIS',
        'transfer' => 'Transfer',
        default => '-',
    };
@endphp

<div class="card stockout-form-card stockout-detail-card">
    <div class="card-header stockout-detail-header">
        <div>
            <span class="stockout-detail-kicker">Detail Transaksi</span>
            <h3 class="card-title mb-0">Barang Keluar #{{ str_pad((string) $stockOut->id, 6, '0', STR_PAD_LEFT) }}</h3>
        </div>
        <span class="stockout-detail-status stockout-detail-status-{{ $stockOut->status }}">
            <i class="fas fa-circle"></i> {{ ucfirst($stockOut->status) }}
        </span>
    </div>

    <div class="card-body">
        <section class="stockout-form-section" aria-labelledby="transactionDataTitle">
            <h3 class="stockout-form-section-title" id="transactionDataTitle">Data Transaksi</h3>
            <div class="stockout-detail-fields">
                <div class="form-group">
                    <label>Jenis Keluar</label>
                    <div class="form-control stockout-readonly-value">{{ $stockOut->jenis === 'DO' ? 'DO' : ucfirst($stockOut->jenis) }}</div>
                </div>
                <div class="form-group">
                    <label>PIC</label>
                    <div class="form-control stockout-readonly-value">{{ $stockOut->pic_penjualan ?: '-' }}</div>
                </div>
                @if($stockOut->jenis === 'DO')
                    <div class="form-group">
                        <label>Nomor SPK</label>
                        <div class="form-control stockout-readonly-value">{{ $stockOut->nomor_spk ?: '-' }}</div>
                    </div>
                    <div class="form-group">
                        <label>Paket Bundling</label>
                        <div class="form-control stockout-readonly-value">{{ $bundleDisplay }}</div>
                    </div>
                @elseif($stockOut->jenis === 'request')
                    <div class="form-group">
                        <label>Nomor IM</label>
                        <div class="form-control stockout-readonly-value">{{ $stockOut->nomor_im ?: '-' }}</div>
                    </div>
                @endif
                <div class="form-group">
                    <label>Cabang</label>
                    <div class="form-control stockout-readonly-value">{{ $stockOut->cabang->nama_cabang ?? '-' }}</div>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <div class="form-control stockout-readonly-value">{{ $stockOut->tanggal?->format('d-m-Y') ?? '-' }}</div>
                </div>
                @if($stockOut->jenis === 'penjualan')
                    <div class="form-group">
                        <label>Jenis Pembayaran</label>
                        <div class="form-control stockout-readonly-value">{{ $paymentDisplay }}</div>
                    </div>
                    <div class="form-group">
                        <label>Discount</label>
                        <div class="form-control stockout-readonly-value">{{ $stockOut->discountLabel() }}</div>
                    </div>
                @endif
                {{-- <div class="form-group">
                    <label>Dibuat Oleh</label>
                    <div class="form-control stockout-readonly-value">{{ $stockOut->user->name ?? '-' }}</div>
                </div>
                <div class="form-group">
                    <label>Disetujui Oleh</label>
                    <div class="form-control stockout-readonly-value">{{ $stockOut->approver->name ?? '-' }}</div>
                </div>
                <div class="form-group">
                    <label>Potongan</label>
                    <div class="form-control stockout-readonly-value">{{ $discountDisplay }}</div>
                </div> --}}
                <div class="form-group stockout-detail-full-width">
                    <label>Keterangan</label>
                    <div class="form-control stockout-readonly-value stockout-readonly-multiline">{{ $stockOut->keterangan ?: '-' }}</div>
                </div>
                <div class="form-group stockout-detail-full-width">
                    <label>Catatan Approval</label>
                    <div class="form-control stockout-readonly-value stockout-readonly-multiline">{{ $stockOut->catatan_approval ?: '-' }}</div>
                </div>
            </div>
        </section>

        @if($stockOut->jenis === 'penjualan')
            <section class="stockout-form-section" aria-labelledby="customerDataTitle">
                <h3 class="stockout-form-section-title" id="customerDataTitle">Data Customer</h3>
                <div class="stockout-detail-fields stockout-customer-fields">
                    <div class="form-group">
                        <label>Nama Customer</label>
                        <div class="form-control stockout-readonly-value">{{ $stockOut->nama_customer ?: '-' }}</div>
                    </div>
                    @if($stockOut->isTagMemberSale())
                        <div class="form-group">
                            <label>Jabatan</label>
                            <div class="form-control stockout-readonly-value">{{ $stockOut->jabatan ?: '-' }}</div>
                        </div>
                    @else
                        <div class="form-group">
                            <label>NIK KTP</label>
                            <div class="form-control stockout-readonly-value">{{ $stockOut->nik_ktp ?: '-' }}</div>
                        </div>
                    @endif
                    <div class="form-group">
                        <label>No. Telepon</label>
                        <div class="form-control stockout-readonly-value">{{ $stockOut->nomor_telepon ?: '-' }}</div>
                    </div>
                    <div class="form-group stockout-detail-full-width">
                        <label>Alamat</label>
                        <div class="form-control stockout-readonly-value stockout-readonly-multiline">{{ $stockOut->alamat_customer ?: '-' }}</div>
                    </div>
                </div>
            </section>
        @endif

        <section class="stockout-form-section" aria-labelledby="paymentDataTitle">
            <h3 class="stockout-form-section-title" id="paymentDataTitle">Payment</h3>
            <div class="stockout-detail-fields stockout-payment-fields">
                <div class="form-group">
                    <label>Kode Pembayaran</label>
                    <div class="form-control stockout-readonly-value">{{ $stockOut->kode_pembayaran ?: '-' }}</div>
                </div>
                <div class="form-group">
                    <label>Bukti Pembayaran</label>
                    @if($stockOut->bukti_pembayaran)
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($stockOut->bukti_pembayaran) }}" class="form-control stockout-readonly-value stockout-proof-link" target="_blank" rel="noopener">
                            <i class="fas fa-file-pdf"></i> Lihat Bukti Pembayaran
                        </a>
                    @else
                        <div class="form-control stockout-readonly-value">-</div>
                    @endif
                </div>
            </div>
        </section>

        <section class="stockout-form-section" aria-labelledby="itemsTitle">
            <h3 class="stockout-form-section-title" id="itemsTitle">Detail Barang</h3>
            <div class="stockout-detail-table-wrap">
                <table class="stockout-detail-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Kode</th>
                            <th>Harga Jual</th>
                            <th>Jumlah</th>
                            <th>Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockOuts as $row)
                            @php
                                $unitPrice = $stockOut->jenis === 'penjualan'
                                    ? (float) ($row->item->harga_jual ?? 0)
                                    : ($row->harga_jual !== null
                                        ? (float) $row->harga_jual
                                        : ($stockOut->jenis === 'DO' ? (float) ($row->item->harga_items ?? 0) : null));
                                $lineTotal = $stockOut->jenis === 'penjualan'
                                    ? $unitPrice * (int) $row->jumlah
                                    : ($row->total !== null
                                        ? (float) $row->total
                                        : ($stockOut->jenis === 'DO' ? (float) ($row->item->harga_items ?? 0) * (int) $row->jumlah : null));
                            @endphp
                            <tr>
                                <td>{{ $row->item->nama_items ?? '-' }}</td>
                                <td>{{ $row->item->kode_items ?? '-' }}</td>
                                <td>{{ $unitPrice !== null ? 'Rp '.number_format($unitPrice, 0, ',', '.') : '-' }}</td>
                                <td>{{ $row->jumlah }} {{ $row->item->satuan ?? '' }}</td>
                                <td>{{ $lineTotal !== null ? 'Rp '.number_format($lineTotal, 0, ',', '.') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="stockout-form-section" aria-labelledby="summaryTitle">
            <h3 class="stockout-form-section-title" id="summaryTitle">Ringkasan</h3>
            <div class="stockout-detail-table-wrap">
                <table class="stockout-detail-table stockout-detail-summary-table">
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
                            <td class="stockout-detail-grand-total">{{ $totalAkhir > 0 ? 'Rp '.number_format($totalAkhir, 0, ',', '.') : '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @if($stockOut->jenis === 'DO')
                <p class="stockout-detail-tax">PPN 11%: <strong>Rp {{ number_format($ppnAmount, 0, ',', '.') }}</strong> (termasuk dalam total akhir)</p>
            @endif
        </section>

        <div class="stockout-detail-actions">
            <a href="{{ route('stockout.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            @if($stockOut->status === 'approved')
                <a href="{{ route('stockout.invoice', $stockOut) }}" class="btn stockout-invoice-button" title="Cetak invoice" target="_blank" rel="noopener">
                    <i class="fas fa-print"></i> Invoice
                </a>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
@include('stockout.partial.form-styles')
<style>
    .stockout-detail-card .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border-top: 3px solid #f70008;
    }

    .stockout-detail-card .card-header > div {
        flex: 0 1 auto;
    }

    .stockout-detail-kicker {
        display: block;
        margin-bottom: 0.2rem;
        color: rgba(244, 247, 250, 0.55);
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .stockout-detail-status {
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        gap: 0.4rem;
        margin-left: auto;
        padding: 0.35rem 0.65rem;
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.06);
        color: rgba(244, 247, 250, 0.82);
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .stockout-detail-status-approved {
        border-color: rgba(111, 232, 160, 0.35);
        background: rgba(23, 167, 90, 0.15);
        color: #7df0a8;
    }

    .stockout-detail-status-rejected {
        border-color: rgba(255, 98, 104, 0.35);
        background: rgba(247, 0, 8, 0.12);
        color: #ff858a;
    }

    .stockout-detail-fields {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0 0.85rem;
    }

    .stockout-detail-full-width {
        grid-column: 1 / -1;
    }

    .stockout-detail-card .stockout-readonly-value {
        display: flex;
        align-items: center;
        min-height: 34px;
        height: auto;
        overflow-wrap: anywhere;
        background-color: rgba(2, 10, 17, 0.52);
        color: rgba(244, 247, 250, 0.9);
        line-height: 1.45;
    }

    .stockout-detail-card .stockout-readonly-multiline {
        min-height: 52px;
        align-items: flex-start;
        white-space: pre-wrap;
    }

    .stockout-payment-fields {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .stockout-proof-link {
        gap: 0.45rem;
        color: #ffe477;
        font-weight: 700;
        text-decoration: none;
    }

    .stockout-proof-link:hover,
    .stockout-proof-link:focus {
        color: #fff0a8;
        text-decoration: underline;
    }

    .stockout-detail-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .stockout-detail-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        color: #f4f7fa;
    }

    .stockout-detail-table th,
    .stockout-detail-table td {
        padding: 0.7rem;
        border-right: 1px solid var(--gazoo-border);
        border-bottom: 1px solid var(--gazoo-border);
        text-align: left;
        vertical-align: middle;
    }

    .stockout-detail-table th:first-child,
    .stockout-detail-table td:first-child {
        border-left: 1px solid var(--gazoo-border);
    }

    .stockout-detail-table thead th {
        background: rgba(0, 0, 0, 0.22);
        color: #ffffff;
        font-size: 0.76rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .stockout-detail-table tbody td {
        color: rgba(244, 247, 250, 0.88);
        font-size: 0.78rem;
    }

    .stockout-detail-table tbody tr:last-child td:first-child {
        border-bottom-left-radius: 8px;
    }

    .stockout-detail-table tbody tr:last-child td:last-child {
        border-bottom-right-radius: 8px;
    }

    .stockout-detail-summary-table {
        min-width: 480px;
        table-layout: fixed;
    }

    .stockout-detail-summary-table th,
    .stockout-detail-summary-table td {
        width: 33.333%;
    }

    .stockout-detail-table tbody .stockout-detail-grand-total {
        color: #ff777c;
        font-weight: 700;
    }

    .stockout-detail-tax {
        margin: 0.55rem 0 0;
        color: rgba(244, 247, 250, 0.65);
        font-size: 0.72rem;
        text-align: right;
    }

    .stockout-detail-tax strong {
        color: #f4f7fa;
    }

    .stockout-detail-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.6rem;
        margin-top: 1.1rem;
        padding-top: 0.9rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .stockout-detail-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        padding: 0.35rem 0.65rem;
        font-size: 0.75rem;
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

    @media (max-width: 767.98px) {
        .stockout-detail-card .card-header {
            align-items: flex-start;
        }

        .stockout-detail-fields,
        .stockout-payment-fields {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

    }

    @media (max-width: 479.98px) {
        .stockout-detail-card .card-header {
            flex-direction: column;
        }

        .stockout-detail-fields,
        .stockout-payment-fields {
            grid-template-columns: minmax(0, 1fr);
        }

        .stockout-detail-full-width {
            grid-column: auto;
        }

        .stockout-detail-actions {
            flex-direction: column-reverse;
        }

        .stockout-detail-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush
