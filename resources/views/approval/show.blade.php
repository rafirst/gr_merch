@extends('layouts.app')
@section('title', 'Detail Approval')

@section('content')
@php
    $isStockInEdit = $approvalType === 'edit_stok';
    $approvalLabel = $isStockInEdit
        ? 'Edit Stok'
        : ($stockOut->jenis === 'penjualan' ? 'Penjualan' : ucfirst($stockOut->jenis));
    $item = $isStockInEdit ? $editRequest->oldItem : $stockOut->item;
    $cabang = $isStockInEdit ? $editRequest->oldItem?->cabang : $stockOut->cabang;
    $requester = $isStockInEdit ? $editRequest->requester : $stockOut->user;
    $approveRoute = $isStockInEdit ? route('approval.stockin.edit.approve', $editRequest) : route('approval.approve', $stockOut);
    $rejectRoute = $isStockInEdit ? route('approval.stockin.edit.reject', $editRequest) : route('approval.reject', $stockOut);
@endphp

@if($isStockInEdit)
    <div class="card approval-detail-card">
        <div class="card-header approval-detail-header">
            <div>
                <span class="approval-detail-kicker">Review Request</span>
                <h3 class="card-title mb-0">Detail Approval</h3>
            </div>
            <span class="stockout-detail-status stockout-detail-status-pending">
                <i class="fas fa-circle"></i> Pending
            </span>
        </div>
        <div class="card-body">
            <div class="approval-detail-type approval-type-edit">
                <i class="fas fa-pen"></i>
                <span>{{ $approvalLabel }}</span>
            </div>

            <section class="approval-information-panel" aria-labelledby="approvalInformationTitle">
                <div class="approval-information-header">
                    <i class="fas fa-file-alt"></i>
                    <h4 id="approvalInformationTitle">Informasi Request</h4>
                </div>
                <div class="approval-detail-grid">
                    <div class="approval-detail-row">
                        <span>Item</span>
                        <strong>{{ $item->nama_items ?? '-' }}</strong>
                    </div>
                    <div class="approval-detail-row">
                        <span>Cabang</span>
                        <strong>{{ $cabang->nama_cabang ?? '-' }}</strong>
                    </div>
                    <div class="approval-detail-row">
                        <span>User Pengaju</span>
                        <strong>{{ $requester->name ?? '-' }}</strong>
                    </div>
                    <div class="approval-detail-row">
                        <span>Jumlah Perubahan</span>
                        <strong>{{ $editRequest->old_jumlah }} &rarr; {{ $editRequest->new_jumlah }} {{ $editRequest->newItem->harga_jual ? 'Rp ' . number_format($editRequest->newItem->harga_jual, 0, ',', '.') : '-' }}</strong>
                    </div>
                    <div class="approval-detail-row approval-detail-row-wide">
                        <span>Keterangan</span>
                        <strong>{{ $editRequest->new_keterangan ?: '-' }}</strong>
                    </div>
                    <div class="approval-detail-row">
                        <span>Tipe Transaksi</span>
                        <strong>{{ $approvalLabel }}</strong>
                    </div>
                </div>
            </section>

            <div class="approval-detail-actions">
                <a href="{{ route('approval.index') }}" class="btn btn-secondary approval-detail-back-button">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <form action="{{ $approveRoute }}" method="POST" class="approval-detail-action-form">
                    @csrf
                    <button type="submit" class="btn approval-detail-approve-button">
                        <i class="fas fa-check"></i> Approve
                    </button>
                </form>
                <form action="{{ $rejectRoute }}" method="POST" class="approval-detail-action-form">
                    @csrf
                    <button type="submit" class="btn approval-detail-reject-button">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </form>
            </div>
        </div>
    </div>
@else
    @php
        $subtotal = (float) ($stockOut->jenis === 'penjualan'
            ? ($stockOut->item->harga_jual ?? 0)
            : ($stockOut->harga_jual !== null
                ? $stockOut->harga_jual
                : ($stockOut->jenis === 'DO' ? ($stockOut->item->harga_items ?? 0) : 0))) * (int) $stockOut->jumlah;
        $ppnAmount = $stockOut->jenis === 'DO' ? (int) round($subtotal * 0.11) : 0;
        $totalAkhir = match ($stockOut->jenis) {
            'DO' => $subtotal + $ppnAmount,
            'penjualan' => $subtotal * (1 - ((float) ($stockOut->discount ?? 0) / 100)),
            default => $stockOut->total !== null ? (float) $stockOut->total : 0,
        };
        $discountAmount = $stockOut->jenis === 'DO' ? 0 : $subtotal - $totalAkhir;
        $unitPrice = $stockOut->jenis === 'penjualan'
            ? (float) ($stockOut->item->harga_jual ?? 0)
            : ($stockOut->harga_jual !== null
                ? (float) $stockOut->harga_jual
                : ($stockOut->jenis === 'DO' ? (float) ($stockOut->item->harga_items ?? 0) : null));
        $lineTotal = $stockOut->jenis === 'penjualan'
            ? $unitPrice * (int) $stockOut->jumlah
            : ($stockOut->total !== null
                ? (float) $stockOut->total
                : ($stockOut->jenis === 'DO' ? (float) ($stockOut->item->harga_items ?? 0) * (int) $stockOut->jumlah : null));
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

    <div class="card stockout-form-card stockout-detail-card approval-stockout-card">
        <div class="card-header stockout-detail-header">
            <div>
                <span class="stockout-detail-kicker">Review Request</span>
                <h3 class="card-title mb-0">Barang Keluar #{{ str_pad((string) $stockOut->id, 6, '0', STR_PAD_LEFT) }}</h3>
            </div>
            <span class="stockout-detail-status stockout-detail-status-pending">
                <i class="fas fa-circle"></i> Pending
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
                        <label>Potongan</label>
                        <div class="form-control stockout-readonly-value">
                            {{ $stockOut->jenis === 'DO' ? 'Rp '.number_format($discountAmount, 0, ',', '.') : $stockOut->discountLabel() }}
                        </div>
                    </div> --}}
                    <div class="form-group stockout-detail-full-width">
                        <label>Keterangan</label>
                        <div class="form-control stockout-readonly-value stockout-readonly-multiline">{{ $stockOut->keterangan ?: '-' }}</div>
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
                            <tr>
                                <td>{{ $stockOut->item->nama_items ?? '-' }}</td>
                                <td>{{ $stockOut->item->kode_items ?? '-' }}</td>
                                <td>{{ $unitPrice !== null ? 'Rp '.number_format($unitPrice, 0, ',', '.') : '-' }}</td>
                                <td>{{ $stockOut->jumlah }} {{ $stockOut->item->satuan ?? '' }}</td>
                                <td>{{ $lineTotal !== null ? 'Rp '.number_format($lineTotal, 0, ',', '.') : '-' }}</td>
                            </tr>
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

            <div class="approval-detail-actions stockout-detail-actions">
                <a href="{{ route('approval.index') }}" class="btn btn-secondary approval-detail-back-button">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <form action="{{ $approveRoute }}" method="POST" class="approval-detail-action-form">
                    @csrf
                    <button type="submit" class="btn approval-detail-approve-button">
                        <i class="fas fa-check"></i> Approve
                    </button>
                </form>
                <form action="{{ $rejectRoute }}" method="POST" class="approval-detail-action-form">
                    @csrf
                    <button type="submit" class="btn approval-detail-reject-button">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@push('styles')
@include('stockout.partial.form-styles')
<style>
    .approval-detail-header,
    .stockout-detail-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border-top: 3px solid #f70008;
    }

    .approval-detail-header > div,
    .stockout-detail-header > div {
        flex: 0 1 auto;
    }

    .approval-detail-kicker,
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

    .stockout-detail-status-pending {
        border-color: rgba(255, 228, 119, 0.35);
        background: rgba(231, 169, 0, 0.12);
        color: #ffe477;
    }

    .approval-type-edit,
    .approval-type-transaction {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 0.8rem;
        padding: 0.45rem 0.7rem;
        border: 1px solid rgba(255, 255, 255, 0.24);
        font-size: 0.78rem;
        font-weight: 700;
    }

    .approval-type-transaction {
        background: linear-gradient(145deg, #69d8ed 0%, #159fbe 48%, #08728c 100%);
        color: #ffffff;
    }

    .approval-type-edit {
        background: linear-gradient(145deg, #ffe477 0%, #e7a900 48%, #a76500 100%);
        color: #271900;
    }

    .approval-information-panel {
        overflow: hidden;
        border: 1px solid rgba(105, 143, 176, 0.28);
        background: rgba(2, 10, 17, 0.35);
    }

    .approval-information-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.7rem 0.85rem;
        border-bottom: 1px solid rgba(105, 143, 176, 0.2);
        background: rgba(5, 14, 23, 0.7);
        color: rgba(244, 247, 250, 0.82);
    }

    .approval-information-header h4 {
        margin: 0;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .approval-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .approval-detail-row {
        position: relative;
        display: grid;
        grid-template-columns: 38% minmax(0, 1fr);
        align-items: center;
        min-height: 52px;
        padding: 0 0.85rem;
        border-bottom: 1px solid var(--gazoo-border);
    }

    .approval-detail-row span {
        color: rgba(244, 247, 250, 0.58);
        font-size: 0.72rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .approval-detail-row strong {
        min-width: 0;
        color: #ffffff;
        font-weight: 500;
        overflow-wrap: anywhere;
    }

    .approval-detail-row-wide {
        grid-column: 1 / -1;
        grid-template-columns: 18.3% minmax(0, 1fr);
    }

    .stockout-detail-fields {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0 0.85rem;
    }

    .stockout-detail-full-width {
        grid-column: 1 / -1;
    }

    .approval-stockout-card .stockout-readonly-value {
        display: flex;
        align-items: center;
        min-height: 34px;
        height: auto;
        overflow-wrap: anywhere;
        background-color: rgba(2, 10, 17, 0.52);
        color: rgba(244, 247, 250, 0.9);
        line-height: 1.45;
    }

    .approval-stockout-card .stockout-readonly-multiline {
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

    .stockout-detail-summary-table {
        min-width: 480px;
        table-layout: fixed;
    }

    .stockout-detail-summary-table th,
    .stockout-detail-summary-table td {
        width: 33.333%;
    }

    .stockout-detail-grand-total {
        color: #ff777c !important;
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

    .approval-detail-actions,
    .stockout-detail-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.6rem;
        margin-top: 1.1rem;
        padding-top: 0.9rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .approval-detail-action-form {
        margin: 0;
    }

    .approval-detail-actions .btn,
    .stockout-detail-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        padding: 0.35rem 0.65rem;
        font-size: 0.75rem;
    }

    .approval-detail-actions .approval-detail-approve-button,
    .approval-detail-actions .approval-detail-reject-button {
        position: relative;
        overflow: hidden;
        min-width: 80px;
        border: 1px solid rgba(255, 255, 255, 0.24);
        color: #ffffff;
        font-weight: 700;
    }

    .approval-detail-actions .approval-detail-approve-button {
        background: linear-gradient(145deg, #5be58a 0%, #19ae53 48%, #078138 100%);
        order: 2;
    }

    .approval-detail-actions .approval-detail-reject-button {
        background: linear-gradient(145deg, #ff7180 0%, #ed3348 48%, #b6122b 100%);
        order: 1;
    }

    .approval-detail-action-form {
        flex: 0 0 auto;
    }

    .approval-detail-action-form .btn {
        height: 100%;
    }

    .approval-detail-back-button {
        margin-right: auto;
    }

    @media (max-width: 767.98px) {
        .approval-detail-grid {
            grid-template-columns: 1fr;
        }

        .approval-detail-row-wide {
            grid-column: auto;
            grid-template-columns: 38% minmax(0, 1fr);
        }

        .stockout-detail-fields,
        .stockout-payment-fields {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .approval-detail-actions {
            flex-wrap: wrap;
        }
    }

    @media (max-width: 479.98px) {
        .approval-detail-header,
        .stockout-detail-header {
            align-items: flex-start;
        }

        .stockout-detail-fields,
        .stockout-payment-fields {
            grid-template-columns: minmax(0, 1fr);
        }

        .stockout-detail-full-width {
            grid-column: auto;
        }

        .approval-detail-actions {
            align-items: stretch;
        }

        .approval-detail-actions .btn {
            width: 100%;
        }

        .approval-detail-action-form {
            flex: 1 1 calc(50% - 0.3rem);
        }

        .approval-detail-actions .approval-detail-back-button {
            flex-basis: 100%;
        }
    }
</style>
@endpush
