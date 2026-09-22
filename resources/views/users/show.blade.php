@extends('layouts.app')
@section('title', 'Detail User')

@section('content')
<div class="card">
    <div class="card-header users-detail-header">
        <h3 class="card-title mb-0">Detail User</h3>
        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm users-glossy">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <table class="table table-striped mb-0 users-detail-table">
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Nama</td><td>{{ $user->name }}</td></tr>
                <tr><td>Username</td><td>{{ $user->username }}</td></tr>
                <tr><td>Role</td><td>{{ $user->role === 'admin_ho' ? 'Admin HO' : 'Staff Cabang' }}</td></tr>
                <tr><td>Cabang</td><td>{{ $user->cabang->nama_cabang ?? '-' }}</td></tr>
                <tr><td>Dibuat</td><td>{{ $user->created_at?->format('d-m-Y H:i') ?? '-' }}</td></tr>
                <tr><td>Diperbarui</td><td>{{ $user->updated_at?->format('d-m-Y H:i') ?? '-' }}</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<style>
    .users-detail-header {
        display: flex;
        align-items: center;
    }

    .users-detail-header .card-title {
        margin-right: auto;
    }

    .users-detail-header .btn {
        flex: 0 0 auto;
    }

    .users-detail-table th:first-child,
    .users-detail-table td:first-child {
        width: 28%;
    }
</style>
@endpush
