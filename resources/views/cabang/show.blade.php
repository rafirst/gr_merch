@extends('layouts.app')
@section('title', 'Detail Cabang')

@section('content')
<div class="card">
    <div class="card-header cabang-detail-header">
        <h3 class="card-title mb-0">Detail Cabang</h3>
        <a href="{{ route('cabang.index') }}" class="btn btn-secondary btn-sm cabang-glossy">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <table class="table table-striped mb-0 cabang-detail-table">
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Kode Cabang</td><td>{{ $cabang->kode_cabang }}</td></tr>
                <tr><td>Nama Cabang</td><td>{{ $cabang->nama_cabang }}</td></tr>
                <tr><td>Alamat</td><td>{{ $cabang->alamat ?: '-' }}</td></tr>
                <tr><td>Telepon</td><td>{{ $cabang->telepon ?: '-' }}</td></tr>
                <tr><td>Dibuat</td><td>{{ $cabang->created_at?->format('d-m-Y H:i') ?? '-' }}</td></tr>
                <tr><td>Diperbarui</td><td>{{ $cabang->updated_at?->format('d-m-Y H:i') ?? '-' }}</td></tr>
            </tbody>
        </table>

        <div class="cabang-related-section">
            <h4>Jumlah Items: {{ $cabang->items->count() }}</h4>
            <div class="table-responsive">
                <table class="table table-striped mb-0 cabang-detail-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Item</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cabang->items as $item)
                            <tr>
                                <td>{{ $item->kode_items }}</td>
                                <td>{{ $item->nama_items }}</td>
                                <td>{{ $item->kategori ? ucfirst($item->kategori) : '-' }}</td>
                                <td>Rp {{ number_format($item->harga_items, 0, ',', '.') }}</td>
                                <td>{{ $item->stok_items }} {{ $item->harga_jual ? 'Rp ' . number_format($item->harga_jual, 0, ',', '.') : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Belum ada item pada cabang ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="cabang-related-section">
            <h4>Jumlah Users: {{ $cabang->users->count() }}</h4>
            <div class="table-responsive">
                <table class="table table-striped mb-0 cabang-detail-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cabang->users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $user->role)) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">Belum ada user pada cabang ini.</td></tr>
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
    .cabang-detail-header {
        display: flex;
        align-items: center;
    }

    .cabang-detail-header .card-title {
        margin-right: auto;
    }

    .cabang-detail-header .btn {
        flex: 0 0 auto;
    }

    .cabang-detail-table th:first-child,
    .cabang-detail-table td:first-child {
        width: 28%;
    }

    .cabang-related-section {
        margin-top: 1.5rem;
    }

    .cabang-related-section h4 {
        margin: 0 0 0.65rem;
        color: #ffffff;
        font-size: 1rem;
        font-weight: 700;
    }

    .cabang-glossy {
        position: relative;
        overflow: hidden;
        border-color: rgba(255, 255, 255, 0.24) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.42), 0 4px 10px rgba(0, 0, 0, 0.25);
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.28);
        transition: filter 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
    }

    .cabang-glossy::before {
        position: absolute;
        top: 0;
        right: 8%;
        left: 8%;
        height: 44%;
        border-radius: 0 0 50% 50%;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.38), rgba(255, 255, 255, 0));
        content: '';
        pointer-events: none;
    }

    .cabang-glossy:hover,
    .cabang-glossy:focus {
        filter: brightness(1.12) saturate(1.08);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55), 0 6px 14px rgba(0, 0, 0, 0.32);
        transform: translateY(-1px);
    }

    .cabang-glossy.btn-secondary {
        background: linear-gradient(145deg, #aeb9c4 0%, #687887 48%, #465563 100%);
    }
</style>
@endpush
