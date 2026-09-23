@extends('layouts.app')
@section('title', 'Barang Keluar')

@section('content')
<div class="card">
    <div class="card-header stockout-card-header">
        <form class="form-inline stockout-filter-form" method="GET">
            <input type="text" name="search" class="form-control" placeholder="Cari customer/item" value="{{ request('search') }}">
            <select name="jenis" class="form-control mr-2" onchange="this.form.submit()">
                <option value="">Semua Jenis</option>
                <option value="penjualan" {{ request('jenis') == 'penjualan' ? 'selected' : '' }}>Penjualan</option>
                <option value="hadiah" {{ request('jenis') == 'hadiah' ? 'selected' : '' }}>Hadiah</option>
                <option value="request" {{ request('jenis') == 'request' ? 'selected' : '' }}>Request</option>
            </select>
            <select name="status" class="form-control mr-2" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="btn stockout-filter-button" title="Cari transaksi" aria-label="Cari transaksi"><i class="fas fa-search"></i></button>
            <a href="{{ route('stockout.index') }}" class="btn stockout-reset-button" title="Reset filter" aria-label="Reset filter"><i class="fas fa-undo-alt"></i></a>
        </form>
        <a href="{{ route('stockout.create') }}" class="btn stockout-create-button"><i class="fas fa-plus"></i> Items</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Item</th><th>Nama Customer</th><th>Cabang</th><th>Jenis</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($stockOuts as $row)
                    <tr>
                        <td>
                            {{ $row->item->nama_items ?? '-' }}
                            @if(($row->item_count ?? 1) > 1)
                                <span class="stockout-more-items">+{{ $row->item_count - 1 }}</span>
                            @endif
                        </td>
                        <td>{{ $row->nama_customer ?: '-' }}</td>
                        <td>{{ $row->cabang->nama_cabang ?? '-' }}</td>
                        <td><span class="badge stockout-badge stockout-type-badge">{{ ucfirst($row->jenis) }}</span></td>
                        <td>
                            @if($row->status == 'pending')
                                <span class="badge stockout-badge stockout-status-pending">Pending</span>
                            @elseif($row->status == 'approved')
                                <span class="badge stockout-badge stockout-status-approved">Approved</span>
                            @else
                                <span class="badge stockout-badge stockout-status-rejected">Rejected</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('stockout.show', $row) }}" class="btn btn-sm stockout-action-button" title="Lihat detail" aria-label="Lihat detail transaksi">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($row->status === 'approved')
                                <a href="{{ route('stockout.invoice', $row) }}" class="btn btn-sm stockout-invoice-button" title="Cetak invoice" aria-label="Cetak invoice" target="_blank" rel="noopener">
                                    <i class="fas fa-print"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada data barang keluar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $stockOuts->links() }}</div>
</div>
@endsection

@push('styles')
<style>
    .stockout-card-header {
        display: flex !important;
        align-items: center;
        justify-content: flex-start !important;
        gap: 1rem;
    }

    .stockout-card-header form {
        margin-bottom: 0;
    }

    .stockout-filter-form {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        min-width: 0;
    }

    .stockout-filter-form input {
        width: 340px;
        min-width: 330px;
    }

    .stockout-filter-form select {
        width: 203px;
        min-width: 203px;
        flex: 0 0 203px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
    }

    .stockout-filter-form .form-control {
        margin-right: 0 !important;
    }

    .stockout-filter-button,
    .stockout-reset-button {
        display: inline-flex;
        width: 38px;
        height: 38px;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid rgba(255, 255, 255, 0.28) !important;
        border-radius: 4px;
        color: #ffffff !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.58), 0 4px 9px rgba(0, 0, 0, 0.28);
    }

    .stockout-filter-button,
    .stockout-reset-button {
        background: linear-gradient(145deg, #aeb9c4 0%, #687887 48%, #465563 100%);
    }

    .stockout-card-header .stockout-create-button {
        flex: 0 0 auto;
        margin-left: auto;
    }

    .stockout-create-button {
        border-color: #f20b18 !important;
        background: linear-gradient(90deg, #f20b18 0%, #b00813 42%, #5b050b 100%) !important;
        color: #ffffff !important;
    }

    .stockout-badge,
    .stockout-action-button {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.28) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.38), 0 2px 5px rgba(0, 0, 0, 0.28);
        color: #ffffff !important;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.42);
    }

    .stockout-badge::before,
    .stockout-action-button::before {
        position: absolute;
        top: 0;
        right: 12%;
        left: 12%;
        height: 42%;
        border-radius: inherit;
        background: rgba(255, 255, 255, 0.28);
        content: '';
        filter: blur(1px);
        pointer-events: none;
    }

    .stockout-type-badge {
        background: linear-gradient(180deg, #8795a5 0%, #596a7c 48%, #344554 100%) !important;
    }

    .stockout-status-pending {
        background: linear-gradient(180deg, #ffd45a 0%, #e49a08 48%, #a85b00 100%) !important;
    }

    .stockout-status-approved {
        background: linear-gradient(180deg, #54e47a 0%, #16a34a 48%, #08752f 100%) !important;
    }

    .stockout-status-rejected {
        background: linear-gradient(180deg, #ff6570 0%, #dc2638 48%, #8d0d1d 100%) !important;
    }

    .stockout-action-button {
        background: linear-gradient(180deg, #69d8ed 0%, #159fbe 48%, #08728c 100%) !important;
    }

    .stockout-invoice-button {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.28) !important;
        background: linear-gradient(180deg, #ffe477 0%, #e7a900 48%, #a76500 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.42), 0 2px 5px rgba(0, 0, 0, 0.28);
        color: #271900 !important;
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

    .stockout-more-items {
        display: inline-block;
        margin-left: 0.35rem;
        padding: 0.1rem 0.35rem;
        border-radius: 4px;
        background: #e30613;
        color: #ffffff;
        font-size: 0.72rem;
        font-weight: 700;
    }

    @media (max-width: 767.98px) {
        .stockout-card-header {
            align-items: stretch;
            flex-wrap: wrap;
        }

        .stockout-filter-form {
            width: 100%;
            flex-wrap: wrap;
        }

        .stockout-filter-form input {
            flex: 1 1 180px;
        }

        .stockout-card-header .stockout-create-button {
            margin-left: 0;
        }
    }
</style>
@endpush
