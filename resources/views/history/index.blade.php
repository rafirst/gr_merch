@extends('layouts.app')
@section('title', 'Histori Transaksi')

@section('content')
<div class="card">
    <div class="card-header history-toolbar">
        <form class="history-filter-form" method="GET">
            <label class="history-field history-select-field">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                <select name="tipe" class="form-control" onchange="this.form.submit()" aria-label="Pilih ringkasan">
                <option value="">-- Summary --</option>
                <option value="in" {{ ($tipe ?? '') == 'in' ? 'selected' : '' }}>Items Masuk</option>
                <option value="out" {{ ($tipe ?? '') == 'out' ? 'selected' : '' }}>Items Keluar</option>
                <option value="approval" {{ ($tipe ?? '') == 'approval' ? 'selected' : '' }}>Items Approval</option>
                </select>
            </label>
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
            <button class="btn history-filter-button"><i class="fas fa-filter"></i><span>Filter</span></button>
            <a href="{{ route('history.index') }}" class="btn history-reset-button" title="Reset filter" aria-label="Reset filter">
                <i class="fas fa-undo-alt"></i><span>Reset</span>
            </a>
        </form>
        <a href="{{ route('history.export') }}" class="btn history-export-button"><i class="fas fa-file-excel"></i><span>Export</span></a>
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
            <thead><tr><th>Tanggal <i class="fas fa-sort ml-1"></i></th><th>Item <i class="fas fa-sort ml-1"></i></th><th>Jumlah <i class="fas fa-sort ml-1"></i></th><th>Jenis</th><th>Status</th><th>Oleh</th><th>Approved By</th><th class="text-center">Aksi</th></tr></thead>
            <tbody>
            @forelse($riwayat as $row)
                <tr>
                    <td class="history-date-cell">{{ $row->tanggal->format('d-m-Y') }}</td>
                    <td class="history-item-cell">{{ $row->item->nama_items ?? '-' }}</td>
                    <td><span class="badge history-quantity-badge history-out-badge">-{{ $row->jumlah }}</span></td>
                    <td><span class="badge history-type-badge">{{ ucfirst($row->jenis) }}</span></td>
                    <td><span class="badge history-status-badge">{{ ucfirst($row->status) }}</span></td>
                    <td>{{ $row->user->name ?? '-' }}</td>
                    <td>{{ $row->approver->name ?? '-' }}</td>
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
        gap: 0.4rem;
        height: 38px;
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
        min-width: 66px;
        background: linear-gradient(145deg, #7c8e9f 0%, #526474 48%, #344451 100%) !important;
    }

    .history-reset-button {
        min-width: 70px;
        background: linear-gradient(145deg, #aeb9c4 0%, #687887 48%, #465563 100%) !important;
    }

    .history-export-button {
        flex: 0 0 auto;
        margin-left: auto;
        min-width: 116px;
        background: linear-gradient(145deg, #ff3b4b 0%, #df071a 48%, #8e000c 100%) !important;
    }

    .history-action-button {
        position: relative;
        overflow: hidden;
        width: 30px;
        height: 30px;
        padding: 0;
        border: 1px solid rgba(255, 255, 255, 0.26) !important;
        background: linear-gradient(145deg, #69d8ed 0%, #159fbe 48%, #08728c 100%) !important;
        color: #ffffff !important;
        line-height: 28px;
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
        .history-field,
        .history-select-field,
        .history-search-field,
        .history-date-field,
        .history-filter-button,
        .history-reset-button {
            width: 100%;
        }

        .history-export-button {
            width: 100%;
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
