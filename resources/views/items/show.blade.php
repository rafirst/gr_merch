@extends('layouts.app')
@section('title', 'Detail Item')

@section('content')
<div class="card">
    <div class="card-header item-detail-header">
        <h3 class="card-title mb-0">Detail Item</h3>
        <a href="{{ route('items.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0 text-center">
                @if($item->foto)
                    <img src="{{ url('storage/'.ltrim($item->foto, '/')) }}" alt="Foto {{ $item->nama_items }}" class="img-fluid item-detail-photo">
                @else
                    <div class="item-detail-photo item-detail-photo-empty">
                        <i class="fas fa-image"></i>
                        <span>Belum ada foto</span>
                    </div>
                @endif
            </div>
            <div class="col-md-8">
                <div class="item-detail-list">
                    <div class="item-detail-row"><span>Kode Items</span><strong>{{ $item->kode_items }}</strong></div>
                    <div class="item-detail-row"><span>Nama Items</span><strong>{{ $item->nama_items }}</strong></div>
                    <div class="item-detail-row"><span>Kategori</span><strong>{{ $item->kategori ? ucfirst($item->kategori) : '-' }}</strong></div>
                    <div class="item-detail-row"><span>Harga Beli</span><strong>Rp {{ number_format($item->harga_items, 0, ',', '.') }}</strong></div>
                    <div class="item-detail-row"><span>Harga Jual</span><strong>Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</strong></div>
                    <div class="item-detail-row"><span>Stok Saat Ini</span><strong>{{ $item->stok_items }}</strong></div>
                    <div class="item-detail-row"><span>Cabang</span><strong>{{ $item->cabang->nama_cabang ?? '-' }}</strong></div>
                    <div class="item-detail-row"><span>Dibuat</span><strong>{{ $item->created_at?->format('d-m-Y H:i') ?? '-' }}</strong></div>
                    <div class="item-detail-row"><span>Diperbarui</span><strong>{{ $item->updated_at?->format('d-m-Y H:i') ?? '-' }}</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .item-detail-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }

    .item-detail-header .card-title {
        margin-right: auto;
    }

    .item-detail-photo {
        width: 100%;
        max-width: 280px;
        height: 280px;
        object-fit: contain;
        border: 1px solid rgba(105, 143, 176, 0.45);
        border-radius: 6px;
        background: rgba(0, 0, 0, 0.25);
    }

    .item-detail-photo-empty {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 0.5rem;
        color: rgba(244, 247, 250, 0.55);
        font-size: 0.95rem;
    }

    .item-detail-photo-empty i {
        font-size: 2rem;
    }

    .item-detail-row {
        display: grid;
        grid-template-columns: 28% 72%;
        align-items: center;
        min-height: 53px;
        border-bottom: 1px solid var(--gazoo-border);
    }

    .item-detail-row span,
    .item-detail-row strong {
        min-width: 0;
        padding: 0.7rem;
    }

    .item-detail-row span {
        color: rgba(244, 247, 250, 0.65);
    }

    .item-detail-row strong {
        color: #ffffff;
        font-weight: 500;
    }

    @media (max-width: 767.98px) {
        .item-detail-row {
            grid-template-columns: 40% 60%;
        }
    }
</style>
@endpush