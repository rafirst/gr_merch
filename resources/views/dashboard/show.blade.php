@extends('layouts.app')
@section('title', 'Detail Stok Item')

@section('content')
<div class="card">
    <div class="card-header dashboard-stock-detail-header">
        <h3 class="card-title mb-0">Detail Item</h3>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0 text-center">
                @if($item->foto)
                    <img src="{{ url('storage/'.ltrim($item->foto, '/')) }}" alt="Foto {{ $item->nama_items }}" class="img-fluid dashboard-stock-detail-photo">
                @else
                    <div class="dashboard-stock-detail-photo dashboard-stock-detail-photo-empty">
                        <i class="fas fa-image"></i>
                        <span>Belum ada foto</span>
                    </div>
                @endif
            </div>
            <div class="col-md-8">
                <div class="dashboard-stock-detail-list">
                    <div class="dashboard-stock-detail-row"><span>Kode Items</span><strong>{{ $item->kode_items }}</strong></div>
                    <div class="dashboard-stock-detail-row"><span>Nama Items</span><strong>{{ $item->nama_items }}</strong></div>
                    <div class="dashboard-stock-detail-row"><span>Kategori</span><strong>{{ $item->kategori ? ucfirst($item->kategori) : '-' }}</strong></div>
                    <div class="dashboard-stock-detail-row"><span>Harga Beli</span><strong>Rp {{ number_format($item->harga_items, 0, ',', '.') }}</strong></div>
                    <div class="dashboard-stock-detail-row"><span>Harga Jual</span><strong>Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</strong></div>
                    <div class="dashboard-stock-detail-row"><span>Total Stok Semua Cabang</span><strong>{{ number_format($totalStock, 0, ',', '.') }}</strong></div>
                    <div class="dashboard-stock-detail-row"><span>Cakupan</span><strong>Semua cabang</strong></div>
                    <div class="dashboard-stock-detail-row"><span>Dibuat</span><strong>{{ $item->created_at?->format('d-m-Y H:i') ?? '-' }}</strong></div>
                    <div class="dashboard-stock-detail-row"><span>Diperbarui</span><strong>{{ $item->updated_at?->format('d-m-Y H:i') ?? '-' }}</strong></div>
                </div>
            </div>
        </div>
        <section class="dashboard-branch-stock-section">
            <h3><i class="fas fa-code-branch"></i> Stok per Cabang</h3>
            <div class="table-responsive">
                <table class="table dashboard-branch-stock-table mb-0">
                    <thead><tr><th>Cabang</th><th class="text-right">Stok</th></tr></thead>
                    <tbody>
                        @forelse($stockByCabang as $branchItem)
                            <tr>
                                <td>{{ $branchItem->cabang->nama_cabang ?? '-' }}</td>
                                <td class="text-right">{{ number_format((int) $branchItem->stok_items, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center">Tidak ada stok item.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
    .dashboard-stock-detail-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }

    .dashboard-stock-detail-header .card-title {
        margin-right: auto;
    }

    .dashboard-stock-detail-photo {
        width: 100%;
        max-width: 280px;
        height: 280px;
        object-fit: contain;
        border: 1px solid rgba(105, 143, 176, 0.45);
        border-radius: 6px;
        background: rgba(0, 0, 0, 0.25);
    }

    .dashboard-stock-detail-photo-empty {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 0.5rem;
        color: rgba(244, 247, 250, 0.55);
        font-size: 0.95rem;
    }

    .dashboard-stock-detail-photo-empty i {
        font-size: 2rem;
    }

    .dashboard-stock-detail-row {
        display: grid;
        grid-template-columns: 28% 72%;
        align-items: center;
        min-height: 53px;
        border-bottom: 1px solid var(--gazoo-border);
    }

    .dashboard-stock-detail-row span,
    .dashboard-stock-detail-row strong {
        min-width: 0;
        padding: 0.7rem;
    }

    .dashboard-stock-detail-row span {
        color: rgba(244, 247, 250, 0.65);
    }

    .dashboard-stock-detail-row strong {
        color: #ffffff;
        font-weight: 500;
    }

    .dashboard-branch-stock-section {
        margin-top: 1.25rem;
        border: 1px solid rgba(105, 143, 176, 0.3);
        border-radius: 4px;
        background: rgba(0, 0, 0, 0.12);
    }

    .dashboard-branch-stock-section h3 {
        margin: 0;
        padding: 0.75rem 0.9rem;
        border-bottom: 1px solid rgba(105, 143, 176, 0.22);
        color: #ffffff;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .dashboard-branch-stock-section h3 i {
        margin-right: 0.4rem;
        color: #f70008;
    }

    .dashboard-branch-stock-table {
        color: #f4f7fa;
    }

    .dashboard-branch-stock-table th,
    .dashboard-branch-stock-table td {
        padding: 0.65rem 0.9rem;
        border-color: rgba(105, 143, 176, 0.18);
    }

    .dashboard-branch-stock-table th {
        color: rgba(244, 247, 250, 0.62);
        font-size: 0.72rem;
        text-transform: uppercase;
    }

    @media (max-width: 767.98px) {
        .dashboard-stock-detail-row {
            grid-template-columns: 40% 60%;
        }
    }
</style>
@endpush