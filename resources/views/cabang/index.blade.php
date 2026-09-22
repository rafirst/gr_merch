@extends('layouts.app')
@section('title', 'Data Cabang')

@section('content')
<div class="card">
    <div class="card-header cabang-toolbar">
        <h3 class="card-title mb-0">Daftar Cabang</h3>
        <a href="{{ route('cabang.create') }}" class="btn btn-primary cabang-glossy" title="Tambah cabang" aria-label="Tambah cabang">
            <i class="fas fa-plus mr-1"></i> Cabang
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Kode</th><th>Nama Cabang</th><th>Alamat</th><th>Jumlah Item</th><th>Jumlah User</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($cabangs as $c)
                    <tr>
                        <td>{{ $c->kode_cabang }}</td>
                        <td>{{ $c->nama_cabang }}</td>
                        <td>{{ $c->alamat ?? '-' }}</td>
                        <td>{{ $c->items_count }}</td>
                        <td>{{ $c->users_count }}</td>
                        <td>
                            <a href="{{ route('cabang.show', $c) }}" class="btn btn-sm btn-info cabang-glossy" title="Lihat detail cabang" aria-label="Lihat detail cabang">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('cabang.edit', $c) }}" class="btn btn-sm btn-warning cabang-glossy" title="Edit cabang" aria-label="Edit cabang">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('cabang.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus cabang ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger cabang-glossy" title="Hapus cabang" aria-label="Hapus cabang"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada data cabang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $cabangs->links() }}</div>
</div>
@endsection

@push('styles')
<style>
    .cabang-toolbar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .cabang-toolbar .card-title {
        margin-right: auto;
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

    .cabang-glossy.btn-primary {
        background: linear-gradient(90deg, #f20b18 0%, #b00813 42%, #5b050b 100%) !important;
        border-color: #f20b18 !important;
    }

    .cabang-glossy.btn-info {
        background: linear-gradient(145deg, #5fdff2 0%, #0fa5bd 48%, #08788f 100%);
    }

    .cabang-glossy.btn-warning {
        background: linear-gradient(145deg, #ffe87a 0%, #f8c400 48%, #d18d00 100%);
        color: #17212b;
        text-shadow: 0 1px 1px rgba(255, 255, 255, 0.35);
    }

    .cabang-glossy.btn-danger {
        background: linear-gradient(145deg, #ff7180 0%, #ed3348 48%, #b6122b 100%);
    }
</style>
@endpush
