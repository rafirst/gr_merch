@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')
<div class="card">
    <div class="card-header users-toolbar">
        <h3 class="card-title mb-0">Daftar User</h3>
        <a href="{{ route('users.create') }}" class="btn users-add-button" title="Tambah user" aria-label="Tambah user">
            <i class="fas fa-plus mr-1"></i> User
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Nama</th><th>Username</th><th>Role</th><th>Cabang</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->username }}</td>
                        <td><span class="badge users-role-badge users-glossy badge-{{ $u->role == 'admin_ho' ? 'primary' : 'secondary' }}">{{ $u->role == 'admin_ho' ? 'Admininistrator' : 'Staff Cabang' }}</span></td>
                        <td>{{ $u->cabang->nama_cabang ?? '-' }}</td>
                        <td>
                            <a href="{{ route('users.show', $u) }}" class="btn btn-sm btn-info users-glossy" title="Lihat detail user" aria-label="Lihat detail user">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-warning users-glossy" title="Edit user" aria-label="Edit user">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($u->id !== auth()->id())
                            <form action="{{ route('users.destroy', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger users-glossy" title="Hapus user" aria-label="Hapus user"><i class="fas fa-trash"></i></button>
                            </form>
                            @else
                            <button type="button" class="btn btn-sm btn-danger users-glossy users-delete-disabled" title="Akun yang sedang digunakan tidak dapat dihapus" aria-label="Akun yang sedang digunakan tidak dapat dihapus" disabled>
                                <i class="fas fa-trash"></i>
                            </button>
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

@push('styles')
<style>
    .users-toolbar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .users-toolbar .card-title {
        margin-right: auto;
    }

    .users-add-button {
        border-color: #f20b18 !important;
        background: linear-gradient(90deg, #f20b18 0%, #b00813 42%, #5b050b 100%) !important;
        color: #ffffff !important;
    }

    .users-glossy {
        position: relative;
        overflow: hidden;
        border-color: rgba(255, 255, 255, 0.24) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.42), 0 4px 10px rgba(0, 0, 0, 0.25);
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.28);
        transition: filter 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
    }

    .users-glossy::before {
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

    .users-glossy:hover,
    .users-glossy:focus {
        filter: brightness(1.12) saturate(1.08);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55), 0 6px 14px rgba(0, 0, 0, 0.32);
        transform: translateY(-1px);
    }

    .users-glossy.btn-info {
        background: linear-gradient(145deg, #5fdff2 0%, #0fa5bd 48%, #08788f 100%);
    }

    .users-glossy.btn-warning {
        background: linear-gradient(145deg, #ffe87a 0%, #f8c400 48%, #d18d00 100%);
        color: #17212b;
        text-shadow: 0 1px 1px rgba(255, 255, 255, 0.35);
    }

    .users-glossy.btn-danger {
        background: linear-gradient(145deg, #ff7180 0%, #ed3348 48%, #b6122b 100%);
    }

    .users-delete-disabled {
        cursor: not-allowed;
        opacity: 0.55;
    }

    .users-role-badge {
        display: inline-block;
        border: 1px solid rgba(255, 255, 255, 0.24);
        padding: 0.3rem 0.45rem;
    }

    .users-role-badge.badge-primary {
        background: linear-gradient(145deg, #61b8ff 0%, #087fe5 48%, #0456ad 100%);
    }

    .users-role-badge.badge-secondary {
        background: linear-gradient(145deg, #aeb9c4 0%, #687887 48%, #465563 100%);
    }
</style>
@endpush


