@extends('layouts.app')
@section('title', 'Detail Barang Masuk')

@section('content')
<div class="card stockin-detail-card">
    <div class="card-header stockin-detail-header">
        <h3 class="card-title mb-0">Detail Barang Masuk</h3>
        <div class="stockin-detail-actions">
            <a href="{{ route('stockin.edit', $stockIn) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-pen mr-1"></i> Edit
            </a>
            <a href="{{ route('stockin.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="stockin-detail-summary">
            <div class="stockin-detail-item">
                @if($stockIn->item?->foto)
                    <img src="{{ url('storage/'.ltrim($stockIn->item->foto, '/')) }}" alt="Foto {{ $stockIn->item->nama_items }}" class="stockin-detail-photo">
                @else
                    <div class="stockin-detail-photo stockin-detail-photo-empty"><i class="fas fa-image"></i></div>
                @endif
                <div class="stockin-detail-item-copy">
                    <span class="stockin-detail-kicker">Barang Masuk</span>
                    <strong>{{ $stockIn->item->nama_items ?? '-' }}</strong>
                    <small>{{ $stockIn->item->kode_items ?? '-' }} <span>|</span> {{ $stockIn->cabang->nama_cabang ?? '-' }}</small>
                </div>
            </div>
            <div class="stockin-detail-metric">
                <i class="fas fa-cubes"></i>
                <div><span>Jumlah Masuk</span><strong>+{{ $stockIn->jumlah }} unit</strong></div>
            </div>
            <div class="stockin-detail-metric">
                <i class="fas fa-coins"></i>
                <div><span>Nilai Barang</span><strong>{{ $stockIn->item->harga_jual ? 'Rp ' . number_format($stockIn->item->harga_jual, 0, ',', '.') : '-' }}</strong></div>
            </div>
        </div>

        <div class="stockin-detail-panel">
            <div class="stockin-panel-title"><i class="fas fa-file-invoice"></i><span>Informasi Transaksi</span></div>
            <div class="stockin-detail-grid">
                <div class="stockin-detail-row"><i class="far fa-calendar-alt"></i><span>Tanggal</span><strong>{{ $stockIn->tanggal?->format('d-m-Y') ?? '-' }}</strong></div>
                <div class="stockin-detail-row"><i class="fas fa-link"></i><span>Sumber</span><strong>{{ $stockIn->sumber ?: '-' }}</strong></div>
                <div class="stockin-detail-row"><i class="fas fa-building"></i><span>Cabang</span><strong>{{ $stockIn->cabang->nama_cabang ?? '-' }}</strong></div>
                <div class="stockin-detail-row"><i class="fas fa-user"></i><span>Dicatat Oleh</span><strong>{{ $stockIn->user->name ?? '-' }}</strong></div>
                <div class="stockin-detail-row stockin-detail-row-wide"><i class="far fa-comment-alt"></i><span>Keterangan</span><strong>{{ $stockIn->keterangan ?: '-' }}</strong></div>
            </div>
        </div>

        <div class="stockin-detail-panel stockin-system-panel">
            <div class="stockin-panel-title"><i class="fas fa-cog"></i><span>Informasi Sistem</span></div>
            <div class="stockin-detail-grid stockin-system-grid">
                <div class="stockin-detail-row"><i class="far fa-clock"></i><span>Dibuat</span><strong>{{ $stockIn->created_at?->format('d-m-Y H:i') ?? '-' }}</strong></div>
                <div class="stockin-detail-row"><i class="fas fa-sync-alt"></i><span>Diperbarui</span><strong>{{ $stockIn->updated_at?->format('d-m-Y H:i') ?? '-' }}</strong></div>
            </div>
        </div>

        <div class="stockin-detail-panel stockin-history-panel">
            <div class="stockin-panel-title"><i class="fas fa-history"></i><span>Histori Barang Masuk</span></div>
            <div class="stockin-history-table-wrap">
                <table class="stockin-history-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Sumber</th>
                            <th>Cabang</th>
                            <th>Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockIn->item?->stockIns ?? [] as $history)
                            <tr>
                                <td>{{ $history->tanggal?->format('d-m-Y') ?? '-' }}</td>
                                <td><span class="stockin-history-quantity">+{{ $history->jumlah }}</span></td>
                                <td>{{ $history->sumber ?: '-' }}</td>
                                <td>{{ $history->cabang->nama_cabang ?? '-' }}</td>
                                <td>{{ $history->user->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="stockin-history-empty">Belum ada histori barang masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stockin-detail-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        gap: 1rem;
    }

    .stockin-detail-header .card-title {
        margin-right: auto;
    }

    .stockin-detail-actions {
        display: flex;
        flex: 0 0 auto;
        gap: 0.45rem;
        margin-left: auto;
    }

    .stockin-detail-actions .btn-warning {
        border-color: #e7a900;
        background: linear-gradient(180deg, #ffe477 0%, #e7a900 48%, #a76500 100%);
        color: #271900;
    }

    .stockin-detail-highlight {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        margin-bottom: 1.25rem;
        padding: 1rem 1.1rem;
        border-left: 3px solid #f70008;
        background: rgba(105, 143, 176, 0.1);
    }

    .stockin-detail-kicker,
    .stockin-detail-quantity span,
    .stockin-detail-row span {
        display: block;
        color: rgba(244, 247, 250, 0.58);
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .stockin-detail-highlight strong {
        display: block;
        margin-top: 0.2rem;
        color: #ffffff;
        font-size: 1.1rem;
    }

    .stockin-detail-highlight small {
        display: block;
        margin-top: 0.25rem;
        color: rgba(244, 247, 250, 0.62);
    }

    .stockin-detail-quantity {
        min-width: 145px;
        padding-left: 1rem;
        border-left: 1px solid rgba(105, 143, 176, 0.35);
        text-align: right;
    }

    .stockin-detail-quantity strong {
        color: #54e47a;
        font-size: 1.35rem;
    }

    .stockin-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0 1.25rem;
    }

    .stockin-detail-row {
        display: grid;
        grid-template-columns: 38% minmax(0, 1fr);
        align-items: center;
        min-height: 48px;
        border-bottom: 1px solid var(--gazoo-border);
    }

    .stockin-detail-row strong {
        min-width: 0;
        color: #ffffff;
        font-weight: 500;
        overflow-wrap: anywhere;
    }

    .stockin-detail-row-wide {
        grid-column: 1 / -1;
    }

    .stockin-detail-summary {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) minmax(180px, 0.75fr) minmax(180px, 0.75fr);
        align-items: center;
        gap: 0.7rem;
        margin-bottom: 0.7rem;
        padding: 0.7rem;
        border: 1px solid rgba(105, 143, 176, 0.28);
        border-radius: 4px;
        background: rgba(4, 16, 26, 0.72);
    }

    .stockin-detail-item {
        display: flex;
        align-items: center;
        min-width: 0;
        gap: 0.7rem;
    }

    .stockin-detail-photo {
        display: flex;
        flex: 0 0 72px;
        width: 72px;
        height: 58px;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 1px solid rgba(105, 143, 176, 0.35);
        border-radius: 4px;
        background: rgba(0, 0, 0, 0.28);
        color: rgba(244, 247, 250, 0.42);
    }

    .stockin-detail-photo:not(.stockin-detail-photo-empty) {
        object-fit: cover;
    }

    .stockin-detail-photo-empty i {
        font-size: 1.25rem;
    }

    .stockin-detail-item-copy {
        min-width: 0;
    }

    .stockin-detail-item-copy strong {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .stockin-detail-item-copy small span {
        margin: 0 0.2rem;
        color: #f70008;
    }

    .stockin-detail-metric {
        display: flex;
        align-items: center;
        min-height: 58px;
        gap: 0.6rem;
        padding: 0.55rem 0.7rem;
        border: 1px solid rgba(105, 143, 176, 0.18);
        border-radius: 4px;
        background: rgba(10, 29, 45, 0.62);
    }

    .stockin-detail-metric > i {
        color: #62dceb;
        font-size: 1rem;
    }

    .stockin-detail-metric span,
    .stockin-detail-metric strong {
        display: block;
    }

    .stockin-detail-metric span {
        color: rgba(244, 247, 250, 0.55);
        font-size: 0.63rem;
        text-transform: uppercase;
    }

    .stockin-detail-metric strong {
        margin-top: 0.12rem;
        color: #54e47a;
        font-size: 0.95rem;
    }

    .stockin-detail-panel {
        margin-top: 0.7rem;
        padding: 0.65rem 0.8rem 0.25rem;
        border: 1px solid rgba(105, 143, 176, 0.28);
        border-radius: 4px;
        background: rgba(4, 16, 26, 0.62);
    }

    .stockin-history-panel {
        margin-top: 0.7rem;
        padding-bottom: 0.65rem;
    }

    .stockin-history-table-wrap {
        overflow-x: auto;
    }

    .stockin-history-table {
        width: 100%;
        min-width: 620px;
        border-collapse: collapse;
    }

    .stockin-history-table th,
    .stockin-history-table td {
        padding: 0.58rem 0.55rem;
        border-bottom: 1px solid rgba(105, 143, 176, 0.2);
        text-align: left;
        white-space: nowrap;
    }

    .stockin-history-table th {
        color: rgba(244, 247, 250, 0.56);
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .stockin-history-table td {
        color: #ffffff;
        font-size: 0.76rem;
    }

    .stockin-history-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .stockin-history-quantity {
        color: #54e47a;
        font-weight: 700;
    }

    .stockin-history-empty {
        color: rgba(244, 247, 250, 0.55) !important;
        text-align: center !important;
    }

    .stockin-panel-title {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid rgba(105, 143, 176, 0.2);
        color: #ffffff;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .stockin-panel-title i {
        color: #f70008;
    }

    .stockin-detail-panel .stockin-detail-grid {
        gap: 0;
    }

    .stockin-detail-panel .stockin-detail-row {
        grid-template-columns: 20px 31% minmax(0, 1fr);
        min-height: 43px;
        gap: 0.35rem;
    }

    .stockin-detail-panel .stockin-detail-row > i {
        color: rgba(244, 247, 250, 0.68);
        font-size: 0.72rem;
        text-align: center;
    }

    .stockin-detail-panel .stockin-detail-row span {
        font-size: 0.62rem;
    }

    .stockin-detail-panel .stockin-detail-row strong {
        font-size: 0.78rem;
    }

    .stockin-detail-panel .stockin-detail-row:nth-child(odd):not(.stockin-detail-row-wide) {
        border-right: 1px solid rgba(105, 143, 176, 0.22);
        padding-right: 0;
    }

    .stockin-detail-panel .stockin-detail-row:nth-child(even):not(.stockin-detail-row-wide) {
        padding-left: 0;
    }

    .stockin-detail-panel .stockin-detail-row-wide {
        grid-template-columns: 20px 15% minmax(0, 1fr);
    }

    .stockin-system-panel {
        margin-bottom: 0.15rem;
    }

    .stockin-system-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    @media (max-width: 767.98px) {
        .stockin-detail-header,
        .stockin-detail-highlight {
            align-items: flex-start;
            flex-direction: column;
        }

        .stockin-detail-actions {
            width: 100%;
        }

        .stockin-detail-actions .btn {
            flex: 1;
        }

        .stockin-detail-quantity {
            width: 100%;
            padding-top: 0.75rem;
            padding-left: 0;
            border-top: 1px solid rgba(105, 143, 176, 0.35);
            border-left: 0;
            text-align: left;
        }

        .stockin-detail-summary {
            grid-template-columns: 1fr;
        }

        .stockin-detail-metric {
            min-height: 48px;
        }

        .stockin-detail-grid,
        .stockin-system-grid {
            grid-template-columns: 1fr;
        }

        .stockin-detail-row-wide {
            grid-column: auto;
        }

        .stockin-detail-panel .stockin-detail-row {
            grid-template-columns: 20px 34% minmax(0, 1fr);
        }

        .stockin-detail-panel .stockin-detail-row-wide {
            grid-template-columns: 20px 34% minmax(0, 1fr);
        }

        .stockin-detail-panel .stockin-detail-row:nth-child(odd):not(.stockin-detail-row-wide) {
            border-right: 0;
            padding-right: 0;
        }

        .stockin-detail-panel .stockin-detail-row:nth-child(even):not(.stockin-detail-row-wide) {
            padding-left: 0;
        }
    }
</style>
@endpush
