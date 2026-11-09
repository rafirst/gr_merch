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
                        @php($roleLabel = match ($u->role) { 'admin_ho' => 'Administrator', 'admin' => 'Admin', 'adh' => 'ADH', default => 'Staff Cabang' })
                        @php($roleBadge = match ($u->role) { 'admin_ho' => 'primary', 'admin' => 'info', 'adh' => 'success', default => 'secondary' })
                        <td><span class="badge users-role-badge users-glossy badge-{{ $roleBadge }}">{{ $roleLabel }}</span></td>
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
    <div class="card-footer users-pagination">
        <span class="users-pagination-summary">
            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
        </span>
        @if($users->hasPages())
            <nav class="users-pagination-controls" role="navigation" aria-label="Navigasi halaman manajemen user">
                @if($users->onFirstPage())
                    <span class="users-pagination-arrow is-disabled" aria-disabled="true" aria-label="Halaman sebelumnya">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="users-pagination-arrow" rel="prev" aria-label="Halaman sebelumnya">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </a>
                @endif
                <span class="users-pagination-counter" aria-current="page">
                    <span class="users-pagination-current">{{ str_pad((string) $users->currentPage(), 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="users-pagination-divider">/</span>
                    <span class="users-pagination-total">{{ str_pad((string) $users->lastPage(), 2, '0', STR_PAD_LEFT) }}</span>
                </span>
                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="users-pagination-arrow" rel="next" aria-label="Halaman berikutnya">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </a>
                @else
                    <span class="users-pagination-arrow is-disabled" aria-disabled="true" aria-label="Halaman berikutnya">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </span>
                @endif
            </nav>
        @endif
    </div>
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

    .users-role-badge.badge-info {
        background: linear-gradient(145deg, #5fdff2 0%, #0fa5bd 48%, #08788f 100%);
    }

    .users-role-badge.badge-success {
        background: linear-gradient(145deg, #75e6a1 0%, #20a85a 48%, #13783d 100%);
    }

    .users-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border-top: 1px solid rgba(105, 143, 176, 0.16) !important;
        background: rgba(3, 10, 17, 0.88) !important;
    }

    .users-pagination-summary {
        margin: 0;
        color: rgba(244, 247, 250, 0.58);
        font-size: 0.75rem;
    }

    .users-pagination-controls {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin-left: auto;
    }

    .users-pagination-arrow {
        display: inline-flex;
        width: 1.8rem;
        height: 1.8rem;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(58, 78, 95, 0.58);
        border-radius: 50%;
        background: transparent;
        color: rgba(244, 247, 250, 0.78);
        font-size: 0.62rem;
        text-decoration: none;
        transition: border-color 0.18s ease, background 0.18s ease, color 0.18s ease;
    }

    .users-pagination-arrow:hover,
    .users-pagination-arrow:focus {
        border-color: rgba(247, 0, 8, 0.68);
        background: rgba(247, 0, 8, 0.12);
        color: #ffffff;
        outline: none;
        text-decoration: none;
    }

    .users-pagination-arrow.is-disabled {
        border-color: rgba(45, 61, 75, 0.4);
        color: rgba(244, 247, 250, 0.28);
        cursor: not-allowed;
    }

    .users-pagination-counter {
        display: inline-flex;
        min-width: 5.6rem;
        height: 1.8rem;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0 0.75rem;
        border: 1px solid rgba(58, 78, 95, 0.48);
        border-radius: 999px;
        background: linear-gradient(145deg, rgba(16, 28, 39, 0.94), rgba(5, 12, 20, 0.96));
        color: rgba(244, 247, 250, 0.58);
        font-size: 0.72rem;
        font-weight: 600;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    .users-pagination-current {
        color: #ff343e;
        font-weight: 700;
    }

    .users-pagination-divider {
        color: rgba(120, 148, 171, 0.55);
    }

    .users-pagination-total {
        color: rgba(244, 247, 250, 0.72);
    }

    @media (max-width: 575.98px) {
        .users-pagination {
            align-items: flex-start;
            flex-direction: column;
        }

        .users-pagination-controls {
            align-self: flex-end;
        }
    }
</style>
@endpush
