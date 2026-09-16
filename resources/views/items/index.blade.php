@extends('layouts.app')
@section('title', 'Data Items')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <form class="form-inline" method="GET">
            <input type="text" name="search" class="form-control mr-2" placeholder="Cari nama/kode item" value="{{ request('search') }}">
            @if(auth()->user()->isAdminHo())
                <select name="cabang_id" class="form-control mr-2">
                    <option value="">Semua Cabang</option>
                    @foreach($cabangs as $c)
                        <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama_cabang }}</option>
                    @endforeach
                </select>
            @endif
            <button class="btn btn-secondary"><i class="fas fa-search"></i></button>
        </form>
        <div>
            <a href="{{ route('items.export') }}" class="btn btn-success"><i class="fas fa-file-excel"></i> Export</a>
            <a href="{{ route('items.import.form') }}" class="btn btn-info"><i class="fas fa-file-import"></i> Import</a>
            <a href="{{ route('items.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Item</a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Kode</th><th>Nama Item</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Cabang</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->kode_items }}</td>
                        <td>{{ $item->nama_items }}</td>
                        <td>{{ $item->kategori ?? '-' }}</td>
                        <td>Rp {{ number_format($item->harga_items, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $item->isStokMenipis() ? 'badge-danger' : 'badge-success' }}">
                                {{ $item->stok_items }} {{ $item->satuan }}
                            </span>
                        </td>
                        <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
                        <td>
                            <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus item ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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
