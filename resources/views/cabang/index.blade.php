@extends('layouts.app')
@section('title', 'Data Cabang')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Daftar Cabang</h3>
        <a href="{{ route('cabang.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Cabang</a>
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
                            <a href="{{ route('cabang.edit', $c) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('cabang.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus cabang ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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
