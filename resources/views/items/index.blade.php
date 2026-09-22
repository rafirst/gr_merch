@extends('layouts.app')
@section('title', 'Data Items')

@section('content')
<div class="card">
    <div class="card-header items-toolbar">
        <form class="form-inline items-filter-form" method="GET">
            <input type="text" name="search" class="form-control mr-2" placeholder="Cari nama/kode item" value="{{ request('search') }}">
            @if(auth()->user()->isAdminHo())
                <select name="cabang_id" class="form-control mr-2">
                    <option value="">Semua Cabang</option>
                    @foreach($cabangs as $c)
                        <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama_cabang }}</option>
                    @endforeach
                </select>
            @endif
            <button class="btn btn-secondary items-glossy"><i class="fas fa-search"></i></button>
        </form>
        <div class="items-toolbar-actions">
            <a href="{{ route('items.index') }}" class="btn btn-secondary items-glossy" title="Reset filter" aria-label="Reset filter">
                <i class="fas fa-undo-alt"></i>
            </a>
            <a href="{{ route('items.export') }}" class="btn btn-success items-glossy" title="Export item" aria-label="Export item">
                <i class="fas fa-file-excel"></i>
            </a>
            <a href="{{ route('items.import.form') }}" class="btn btn-info items-glossy" title="Import item" aria-label="Import item">
                <i class="fas fa-file-import"></i>
            </a>
            <a href="{{ route('items.create') }}" class="btn btn-primary items-glossy" title="Tambah item" aria-label="Tambah item">
                <i class="fas fa-plus"></i>
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Kode</th><th>Nama Item</th><th>Kategori</th><th>Harga</th><th>Cabang</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->kode_items }}</td>
                        <td>{{ $item->nama_items }}</td>
                        <td>{{ $item->kategori ?? '-' }}</td>
                        <td>Rp {{ number_format($item->harga_items, 0, ',', '.') }}</td>
                        <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
                        <td>
                            <a href="{{ route('items.show', $item) }}" class="btn btn-sm btn-info items-glossy" title="Lihat detail" aria-label="Lihat detail item">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-warning items-glossy"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus item ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger items-glossy"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Belum ada data item.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $items->links() }}</div>
</div>
@endsection

@push('styles')
<style>
    .items-toolbar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: nowrap;
    }

    .items-filter-form,
    .items-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .items-toolbar-actions {
        order: 2;
        flex: 0 0 auto;
        margin-left: auto;
    }

    .items-filter-form {
        order: 1;
        min-width: 0;
        flex: 1 1 auto;
    }

    .items-filter-form input[name="search"] {
        min-width: 180px;
        flex: 1 1 auto;
    }

    .items-filter-form select[name="cabang_id"] {
        width: 220px;
        flex: 0 0 220px;
    }

    .items-filter-form .form-control,
    .items-filter-form .custom-select {
        margin-right: 0 !important;
    }

    .items-toolbar-actions .btn {
        min-width: 38px;
    }

    .items-glossy {
        position: relative;
        overflow: hidden;
        border-color: rgba(255, 255, 255, 0.24) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.42), 0 4px 10px rgba(0, 0, 0, 0.25);
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.28);
        transition: filter 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
    }

    .items-glossy::before {
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

    .items-glossy:hover,
    .items-glossy:focus {
        filter: brightness(1.12) saturate(1.08);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55), 0 6px 14px rgba(0, 0, 0, 0.32);
        transform: translateY(-1px);
    }

    .items-glossy.btn-secondary {
        background: linear-gradient(145deg, #aeb9c4 0%, #687887 48%, #465563 100%);
    }

    .items-glossy.btn-success {
        background: linear-gradient(145deg, #55d889 0%, #18a957 48%, #087c3b 100%);
    }

    .items-glossy.btn-info {
        background: linear-gradient(145deg, #5fdff2 0%, #0fa5bd 48%, #08788f 100%);
    }

    .items-glossy.btn-primary {
        border-color: #f20b18 !important;
        background: linear-gradient(90deg, #f20b18 0%, #b00813 42%, #5b050b 100%) !important;
    }

    .items-glossy.btn-warning {
        background: linear-gradient(145deg, #ffe87a 0%, #f8c400 48%, #d18d00 100%);
        color: #17212b;
        text-shadow: 0 1px 1px rgba(255, 255, 255, 0.35);
    }

    .items-glossy.btn-danger {
        background: linear-gradient(145deg, #ff7180 0%, #ed3348 48%, #b6122b 100%);
    }

    @media (max-width: 767.98px) {
        .items-toolbar {
            flex-wrap: wrap;
        }

        .items-filter-form,
        .items-toolbar-actions {
            width: 100%;
            flex-wrap: wrap;
        }

        .items-filter-form input,
        .items-filter-form select {
            min-width: 0;
            flex: 1 1 180px;
        }

        .items-toolbar-actions {
            order: 2;
            margin-left: 0;
        }

        .items-filter-form {
            order: 1;
        }
    }
</style>
@endpush
