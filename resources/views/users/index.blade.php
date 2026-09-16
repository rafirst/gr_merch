@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Daftar User</h3>
        <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah User</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Nama</th><th>Username</th><th>Role</th><th>Cabang</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->username }}</td>
                        <td><span class="badge badge-{{ $u->role == 'admin_ho' ? 'primary' : 'secondary' }}">{{ $u->role == 'admin_ho' ? 'Admin HO' : 'Staff Cabang' }}</span></td>
                        <td>{{ $u->cabang->nama_cabang ?? '-' }}</td>
                        <td>
                            <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            @if($u->id !== auth()->id())
                            <form action="{{ route('users.destroy', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">Belum ada data user.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $users->links() }}</div>
</div>
@endsection
