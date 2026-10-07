@extends('layouts.app')
@section('title', 'Histori Transaksi')

@section('content')
<div class="card">
    <div class="card-header history-toolbar">
        <form class="history-filter-form {{ ($tipe ?? '') !== 'out' ? 'history-filter-form-expanded' : '' }}" method="GET">
            <label class="history-field history-select-field">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                <select name="tipe" class="form-control" onchange="this.form.submit()" aria-label="Pilih ringkasan">
                <option value="">-- Summary --</option>
                <option value="in" {{ ($tipe ?? '') == 'in' ? 'selected' : '' }}>Items Masuk</option>
                <option value="out" {{ ($tipe ?? '') == 'out' ? 'selected' : '' }}>Items Keluar</option>
                <option value="approval" {{ ($tipe ?? '') == 'approval' ? 'selected' : '' }}>Items Approval</option>
                </select>
            </label>
            @if(($tipe ?? '') === 'out')
                <label class="history-field history-jenis-field">
                    <i class="fas fa-filter" aria-hidden="true"></i>
                    <select name="jenis" class="form-control" onchange="this.form.submit()" aria-label="Pilih jenis barang keluar">
                        <option value="">-- Jenis Keluar --</option>
                        <option value="penjualan" {{ request('jenis') === 'penjualan' ? 'selected' : '' }}>Penjualan</option>
                        <option value="request" {{ request('jenis') === 'request' ? 'selected' : '' }}>Request</option>
                        <option value="DO" {{ request('jenis') === 'DO' ? 'selected' : '' }}>DO</option>
                    </select>
                </label>
            @endif
            <label class="history-field history-search-field">
                <i class="fas fa-search" aria-hidden="true"></i>
                <input type="text" name="item" class="form-control" placeholder="Nama / kode item" value="{{ request('item') }}" aria-label="Nama item atau kode item">
            </label>
            <label class="history-field history-date-field">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                <input type="date" name="dari" class="form-control" value="{{ request('dari') }}" aria-label="Tanggal mulai">
            </label>
            <label class="history-field history-date-field">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                <input type="date" name="sampai" class="form-control" value="{{ request('sampai') }}" aria-label="Tanggal sampai">
            </label>
            <button class="btn history-filter-button" title="Terapkan filter" aria-label="Terapkan filter"><i class="fas fa-filter" aria-hidden="true"></i></button>
            <a href="{{ route('history.index') }}" class="btn history-reset-button" title="Reset filter" aria-label="Reset filter">
                <i class="fas fa-undo-alt"></i>
            </a>
        </form>
        <button type="button" class="btn history-export-button" id="historyExportButton" title="Ekspor histori" aria-label="Pilih format ekspor" aria-haspopup="dialog" aria-controls="historyExportModal"><i class="fas fa-file-pdf" aria-hidden="true"></i></button>
    </div>
    <div class="card-body p-0">

    @if(($tipe ?? null) === 'in')
        <table class="table table-striped mb-0 history-data-table history-in-table">
            <thead><tr><th>Tanggal <i class="fas fa-sort ml-1"></i></th><th>Item <i class="fas fa-sort ml-1"></i></th><th>Cabang</th><th>Jumlah <i class="fas fa-sort ml-1"></i></th><th>Oleh</th><th class="text-center">Aksi</th></tr></thead>
            <tbody>
            @forelse($riwayat as $row)
                <tr>
                    <td class="history-date-cell">{{ $row->tanggal->format('d-m-Y') }}</td>
                    <td class="history-item-cell">{{ $row->item->nama_items ?? '-' }}</td>
                    <td>{{ $row->cabang->nama_cabang ?? '-' }}</td>
                    <td><span class="badge history-quantity-badge history-in-badge">+{{ $row->jumlah }}</span></td>
                    <td>{{ $row->user->name ?? '-' }}</td>
                    <td class="text-center"><a href="{{ route('history.show', ['type' => 'in', 'id' => $row->id]) }}" class="btn btn-sm history-action-button" title="Lihat detail" aria-label="Lihat detail histori barang masuk"><i class="fas fa-eye"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">Tidak ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $riwayat->links() }}</div>

    @elseif(($tipe ?? null) === 'out')
        <table class="table table-striped mb-0 history-data-table history-out-table">
            <thead><tr><th>Tanggal <i class="fas fa-sort ml-1"></i></th><th>Item <i class="fas fa-sort ml-1"></i></th><th>Jumlah <i class="fas fa-sort ml-1"></i></th><th>Jenis</th><th>Status</th><th>Kode Pembayaran</th><th>Pembayaran</th><th class="text-center">Aksi</th></tr></thead>
            <tbody>
            @forelse($riwayat as $row)
                <tr>
                    <td class="history-date-cell">{{ $row->tanggal->format('d-m-Y') }}</td>
                    <td class="history-item-cell">{{ $row->item->nama_items ?? '-' }}</td>
                    <td><span class="badge history-quantity-badge history-out-badge">-{{ $row->jumlah }}</span></td>
                    <td><span class="badge history-type-badge">{{ ucfirst($row->jenis) }}</span></td>
                    <td><span class="badge history-status-badge">{{ ucfirst($row->status) }}</span></td>
                    <td>
                        @if($row->kode_pembayaran)
                            <span class="history-payment-code" title="{{ $row->kode_pembayaran }}">{{ $row->kode_pembayaran }}</span>
                        @else
                            <span class="history-payment-code-empty">-</span>
                        @endif
                    </td>
                    <td>{{ $row->jenis_pembayaran ? ucfirst($row->jenis_pembayaran) : '-' }}</td>
                    <td class="text-center"><a href="{{ route('history.show', ['type' => 'out', 'id' => $row->id]) }}" class="btn btn-sm history-action-button" title="Lihat detail" aria-label="Lihat detail histori barang keluar"><i class="fas fa-eye"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">Tidak ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $riwayat->links() }}</div>

    @elseif(($tipe ?? null) === 'approval')
        <table class="table table-striped mb-0 history-data-table history-approval-table">
            <thead><tr><th>Item</th><th>Jumlah</th><th>User Pengaju</th><th>Tipe</th><th>Status</th><th>Approved By</th><th class="text-center">Aksi</th></tr></thead>
            <tbody>
            @forelse($approvalHistory as $row)
                <tr>
                    <td class="history-item-cell">{{ $row['item'] }}</td>                    <td><span class="badge history-quantity-badge {{ $row['type'] === 'Edit Stok' ? 'history-source-badge' : 'history-in-badge' }}">{{ $row['jumlah'] }}</span></td>
                    <td>{{ $row['requester'] }}</td>
                    <td><span class="badge history-type-badge">{{ $row['type'] }}</span></td>
                    <td><span class="badge history-status-badge {{ $row['status'] === 'rejected' ? 'history-rejected-badge' : '' }}">{{ ucfirst($row['status']) }}</span></td>
                    <td>{{ $row['approver'] }}</td>
                    <td class="text-center"><a href="{{ $row['history_route'] }}" class="btn btn-sm history-action-button" title="Lihat detail" aria-label="Lihat detail histori approval"><i class="fas fa-eye"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">Belum ada histori approval.</td></tr>
            @endforelse
            </tbody>
        </table>

    @else
        <div class="row history-summary-grid">
            <div class="col-md-6 history-summary-panel">
                <h5 class="history-summary-title">Barang Masuk Terbaru</h5>
                <table class="table table-sm table-striped history-summary-table history-summary-in-table">
                    <thead><tr><th>Tanggal <i class="fas fa-sort ml-1"></i></th><th>Item <i class="fas fa-sort ml-1"></i></th><th>Jumlah <i class="fas fa-sort ml-1"></i></th></tr></thead>
                    <tbody>
                    @forelse($stockIns as $row)
                        <tr>
                            <td class="history-date-cell">{{ $row->tanggal->format('d-m-Y') }}</td>
                            <td class="history-item-cell">{{ $row->item->nama_items ?? '-' }}</td>
                            <td><span class="badge history-quantity-badge history-in-badge">+{{ $row->jumlah }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center">Belum ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="col-md-6 history-summary-panel">
                <h5 class="history-summary-title">Barang Keluar Terbaru</h5>
                <table class="table table-sm table-striped history-summary-table history-summary-out-table">
                    <thead><tr><th>Tanggal <i class="fas fa-sort ml-1"></i></th><th>Item <i class="fas fa-sort ml-1"></i></th><th>Jumlah <i class="fas fa-sort ml-1"></i></th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($stockOuts as $row)
                        <tr>
                            <td class="history-date-cell">{{ $row->tanggal->format('d-m-Y') }}</td>
                            <td class="history-item-cell">{{ $row->item->nama_items ?? '-' }}</td>
                            <td><span class="badge history-quantity-badge history-out-badge">-{{ $row->jumlah }}</span></td>
                            <td><span class="badge history-status-badge">{{ ucfirst($row->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center">Belum ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    </div>
</div>
@endsection

<div class="history-export-modal" id="historyExportModal" aria-hidden="true">
    <div class="history-export-backdrop" data-history-export-close></div>
    <section class="history-export-dialog" role="dialog" aria-modal="true" aria-labelledby="historyExportTitle">
        <div class="history-export-dialog-header">
            <div>
                <span class="history-export-kicker">Histori Transaksi</span>
                <h3 id="historyExportTitle">Pilih Format Ekspor</h3>
            </div>
            <button type="button" class="history-export-close" data-history-export-close aria-label="Tutup pilihan ekspor">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>
        <div class="history-export-options">
            <a href="{{ route('history.export') }}" class="history-export-option history-export-option-pdf">
                <span class="history-export-option-icon"><i class="fas fa-file-pdf" aria-hidden="true"></i></span>
                <span class="history-export-option-copy"><strong>Export PDF</strong><small>Unduh laporan dalam format PDF</small></span>
                <i class="fas fa-chevron-right history-export-option-arrow" aria-hidden="true"></i>
            </a>
            @if(auth()->user()?->isAdminHo())
            <a href="{{ route('history.export.excel') }}" class="history-export-option history-export-option-excel">
                <span class="history-export-option-icon"><i class="fas fa-file-excel" aria-hidden="true"></i></span>
                <span class="history-export-option-copy"><strong>Export Excel</strong><small>Unduh data dalam format XLSX</small></span>
                <i class="fas fa-chevron-right history-export-option-arrow" aria-hidden="true"></i>
            </a>
            @endif
        </div>
        <button type="button" class="history-export-cancel" data-history-export-close>Batal</button>
    </section>
</div>

@push('styles')
<style>
    .history-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        min-height: 58px;
        padding: 0.55rem 0.85rem 0.55rem 1.35rem !important;
    }

    .history-filter-form {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        min-width: 0;
        margin: 0;
    }

    .history-field {
        position: relative;
        display: flex;
        align-items: center;
        width: 220px;
        min-width: 150px;
        margin: 0;

    }

    .history-field > i {
        position: absolute;
        left: 0.7rem;
        z-index: 1;
        color: rgba(221, 231, 239, 0.72);
        font-size: 0.72rem;
        pointer-events: none;
    }

    .history-field .form-control {
        height: 38px;
        margin: 0 !important;
        padding: 0.4rem 0.65rem 0.4rem 2rem;
        border: 1px solid rgba(105, 143, 176, 0.28);
        border-radius: 4px;
        background: rgba(1, 9, 16, 0.78);
        color: #f4f7fa;
        font-size: 0.8rem;
    }

    .history-field .form-control:focus {
        border-color: rgba(242, 11, 24, 0.78);
        box-shadow: 0 0 0 2px rgba(242, 11, 24, 0.12);
    }

    .history-select-field {
        width: 174px;
    }

    .history-search-field {
        width: 164px;
    }

    .history-date-field {
        width: 116px;
    }

    @media (min-width: 992px) {
        .history-filter-form-expanded {
            flex: 1 1 auto;
        }

        .history-filter-form-expanded .history-select-field,
        .history-filter-form-expanded .history-search-field,
        .history-filter-form-expanded .history-date-field {
            flex: 1 1 0;
            width: auto;
            min-width: 0;
        }

        .history-filter-form-expanded .history-select-field select {
            width: 100%;
        }
    }

    .history-jenis-field {
        width: 142px;
        min-width: 142px;
    }

    .history-select-field select {
        appearance: auto;
        padding-right: 0.35rem;
    }

    .history-date-field input {
        padding-left: 1.95rem;
    }

    .history-date-field input::-webkit-calendar-picker-indicator {
        filter: invert(1) opacity(0.7);
        cursor: pointer;
    }

    .history-filter-button,
    .history-reset-button,
    .history-export-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        min-width: 0;
        padding: 0;
        border: 1px solid rgba(255, 255, 255, 0.24) !important;
        border-radius: 4px;
        color: #ffffff !important;
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.38), 0 3px 8px rgba(0, 0, 0, 0.24);
        transition: filter 0.18s ease, transform 0.18s ease;
    }

    .history-filter-button {
        background: linear-gradient(145deg, #7c8e9f 0%, #526474 48%, #344451 100%) !important;
    }

    .history-reset-button {
        background: linear-gradient(145deg, #aeb9c4 0%, #687887 48%, #465563 100%) !important;
    }

    .history-export-button {
        flex: 0 0 auto;
        margin-left: auto;
        background: linear-gradient(145deg, #ff3b4b 0%, #df071a 48%, #8e000c 100%) !important;
    }

    .history-export-modal {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: grid;
        visibility: hidden;
        place-items: center;
        opacity: 0;
        transition: opacity 0.2s ease, visibility 0.2s ease;
    }

    .history-export-modal.is-visible {
        visibility: visible;
        opacity: 1;
    }

    .history-export-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 4, 9, 0.78);
        backdrop-filter: blur(4px);
    }

    .history-export-dialog {
        position: relative;
        width: min(430px, calc(100% - 2rem));
        padding: 1.25rem;
        border: 1px solid rgba(105, 143, 176, 0.4);
        border-top: 3px solid #df071a;
        border-radius: 6px;
        background: linear-gradient(145deg, #172532, #050c14 72%);
        box-shadow: 0 20px 55px rgba(0, 0, 0, 0.6);
        transform: translateY(12px);
        transition: transform 0.2s ease;
    }

    .history-export-modal.is-visible .history-export-dialog {
        transform: translateY(0);
    }

    .history-export-dialog-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .history-export-kicker {
        color: #ff6570;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .history-export-dialog h3 {
        margin: 0.25rem 0 0;
        color: #ffffff;
        font-size: 1.15rem;
    }

    .history-export-close {
        display: inline-grid;
        width: 34px;
        height: 34px;
        flex: 0 0 auto;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 4px;
        background: rgba(255, 255, 255, 0.06);
        color: rgba(244, 247, 250, 0.8);
        cursor: pointer;
    }

    .history-export-options {
        display: grid;
        gap: 0.6rem;
    }

    .history-export-option {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        min-width: 0;
        padding: 0.75rem;
        border: 1px solid rgba(105, 143, 176, 0.28);
        border-radius: 4px;
        background: rgba(255, 255, 255, 0.035);
        color: #ffffff;
        text-decoration: none;
        transition: border-color 0.16s ease, background-color 0.16s ease;
    }

    .history-export-option:hover,
    .history-export-option:focus {
        border-color: rgba(255, 255, 255, 0.45);
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff;
        text-decoration: none;
    }

    .history-export-option-icon {
        display: inline-grid;
        width: 38px;
        height: 38px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 4px;
        background: rgba(223, 7, 26, 0.16);
        color: #ff5a68;
        font-size: 1.1rem;
    }

    .history-export-option-excel .history-export-option-icon {
        background: rgba(25, 174, 83, 0.16);
        color: #5be58a;
    }

    .history-export-option-copy {
        display: flex;
        min-width: 0;
        flex: 1;
        flex-direction: column;
        gap: 0.18rem;
    }

    .history-export-option-copy strong {
        font-size: 0.9rem;
    }

    .history-export-option-copy small {
        color: rgba(244, 247, 250, 0.58);
        font-size: 0.74rem;
    }

    .history-export-option-arrow {
        color: rgba(244, 247, 250, 0.45);
        font-size: 0.75rem;
    }

    .history-export-cancel {
        display: block;
        min-height: 36px;
        margin: 0.9rem 0 0 auto;
        padding: 0.4rem 0.8rem;
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 4px;
        background: transparent;
        color: rgba(244, 247, 250, 0.78);
        cursor: pointer;
        font-size: 0.8rem;
    }

    .history-action-button {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        width: 30px;
        height: 30px;
        padding: 0;
        border: 1px solid rgba(255, 255, 255, 0.26) !important;
        background: linear-gradient(145deg, #69d8ed 0%, #159fbe 48%, #08728c 100%) !important;
        color: #ffffff !important;
        line-height: 1;
        vertical-align: middle;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.42), 0 2px 5px rgba(0, 0, 0, 0.24);
    }

    .history-action-button::before {
        position: absolute;
        top: 0;
        right: 10%;
        left: 10%;
        height: 42%;
        border-radius: inherit;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.08));
        content: '';
        pointer-events: none;
    }

    .history-filter-button:hover,
    .history-filter-button:focus,
    .history-reset-button:hover,
    .history-reset-button:focus,
    .history-export-button:hover,
    .history-export-button:focus {
        filter: brightness(1.12) saturate(1.08);
        transform: translateY(-1px);
    }

    .history-data-table {
        min-width: 760px;
        margin-bottom: 0 !important;
        font-size: 0.8rem;
    }

    .history-data-table thead th {
        padding: 0.62rem 0.7rem;
        border-bottom-width: 1px;
        color: rgba(244, 247, 250, 0.82);
        font-size: 0.69rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .history-data-table thead th i {
        color: rgba(221, 231, 239, 0.48);
        font-size: 0.58rem;
    }

    .history-data-table tbody td {
        height: 44px;
        padding: 0.52rem 0.7rem;
        border-top-color: rgba(105, 143, 176, 0.16);
        color: rgba(244, 247, 250, 0.88);
        vertical-align: middle;
        white-space: nowrap;
    }

    .history-data-table tbody tr {
        transition: background-color 0.16s ease;
    }

    .history-data-table tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.055) !important;
    }

    .history-date-cell {
        color: rgba(244, 247, 250, 0.72) !important;
        font-variant-numeric: tabular-nums;
    }

    .history-item-cell {
        min-width: 180px;
        color: #ffffff !important;
        font-weight: 700;
    }

    .history-quantity-badge,
    .history-source-badge,
    .history-type-badge,
    .history-status-badge {
        display: inline-flex;
        align-items: center;
        min-height: 20px;
        padding: 0.2rem 0.48rem;
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 999px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.42), 0 2px 5px rgba(0, 0, 0, 0.24);
        color: #ffffff;
        font-size: 0.67rem;
        font-weight: 700;
        line-height: 1;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
    }

    .history-rejected-badge {
        background: linear-gradient(145deg, #ff7180 0%, #ed3348 48%, #b6122b 100%);
    }

    .history-in-badge {
        background: linear-gradient(145deg, #5be58a 0%, #19ae53 48%, #078138 100%);
    }

    .history-out-badge {
        background: linear-gradient(145deg, #ff7180 0%, #ed3348 48%, #b6122b 100%);
    }

    .history-source-badge {
        background: linear-gradient(145deg, #56d5e8 0%, #159fbe 48%, #08728c 100%);
    }

    .history-type-badge {
        background: linear-gradient(145deg, #b9c6d2 0%, #778795 48%, #4b5a68 100%);
    }

    .history-status-badge {
        background: linear-gradient(145deg, #5be58a 0%, #19ae53 48%, #078138 100%);
    }

    .history-payment-code {
        display: inline-block;
        max-width: 190px;
        overflow: hidden;
        color: #7ef0aa;
        font-family: SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-overflow: ellipsis;
        vertical-align: middle;
        white-space: nowrap;
    }

    .history-payment-code-empty {
        color: rgba(244, 247, 250, 0.45);
    }

    .history-data-table tbody small {
        display: inline-block;
        margin-top: 0.18rem;
        color: rgba(244, 247, 250, 0.5) !important;
        font-size: 0.66rem;
    }

    .history-data-table + .p-3 {
        border-top: 1px solid rgba(105, 143, 176, 0.16);
    }

    .history-summary-grid {
        margin: 0;
        padding: 0.85rem 0.9rem 1rem;
    }

    .history-summary-panel {
        padding: 0 0.45rem;
    }

    .history-summary-title {
        margin: 0 0 0.55rem;
        color: rgba(244, 247, 250, 0.92);
        font-size: 0.92rem;
        font-weight: 700;
    }

    .history-summary-table {
        min-width: 0;
        margin-bottom: 0;
        font-size: 0.78rem;
    }

    .history-summary-table thead th {
        padding: 0.55rem 0.58rem;
        border-bottom-width: 1px;
        color: rgba(244, 247, 250, 0.8);
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.035em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .history-summary-table thead th i {
        color: rgba(221, 231, 239, 0.46);
        font-size: 0.55rem;
    }

    .history-summary-table tbody td {
        height: 40px;
        padding: 0.45rem 0.58rem;
        border-top-color: rgba(105, 143, 176, 0.14);
        vertical-align: middle;
        white-space: nowrap;
    }

    .history-summary-table tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }

    .history-summary-table .history-item-cell {
        min-width: 0;
    }

    .history-summary-out-table .history-item-cell {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (max-width: 991.98px) {
        .history-toolbar {
            align-items: stretch;
            flex-wrap: wrap;
        }

        .history-filter-form {
            flex: 1 1 100%;
            flex-wrap: wrap;
        }

        .history-export-button {
            margin-left: auto;
        }
    }

    @media (max-width: 575.98px) {
        .history-filter-form,
        .history-field {
            width: 100%;
        }

        .history-export-button {
            margin-left: auto;
        }

        .history-data-table {
            display: block;
            max-width: 100%;
            overflow-x: auto;
        }

        .history-summary-grid {
            padding: 0.75rem 0.45rem;
        }

        .history-summary-panel {
            padding: 0;
        }

        .history-summary-panel + .history-summary-panel {
            margin-top: 1rem;
        }

        .history-summary-table {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('historyExportModal');
        const openButton = document.getElementById('historyExportButton');
        const pdfLink = modal.querySelector('.history-export-option-pdf');
        let previouslyFocusedElement = null;

        function closeModal() {
            modal.classList.remove('is-visible');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            previouslyFocusedElement?.focus();
        }

        openButton.addEventListener('click', function () {
            previouslyFocusedElement = document.activeElement;
            modal.classList.add('is-visible');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            pdfLink.focus();
        });

        modal.querySelectorAll('[data-history-export-close]').forEach(function (element) {
            element.addEventListener('click', closeModal);
        });

        modal.querySelectorAll('.history-export-option').forEach(function (link) {
            link.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-visible')) {
                closeModal();
            }
        });
    });
</script>
@endpush
