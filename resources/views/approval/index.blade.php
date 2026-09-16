@extends('layouts.app')
@section('title', 'Approval Barang Keluar')

@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Daftar Transaksi Menunggu Approval</h3></div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Tanggal</th><th>Item</th><th>Cabang</th><th>Jumlah</th><th>Jenis</th><th>Keterangan</th><th>Diajukan Oleh</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($pendingList as $row)
                    <tr>
                        <td>{{ $row->tanggal->format('d-m-Y') }}</td>
                        <td>{{ $row->item->nama_items ?? '-' }} <br><small class="text-muted">Stok saat ini: {{ $row->item->stok_items ?? 0 }}</small></td>
                        <td>{{ $row->cabang->nama_cabang ?? '-' }}</td>
                        <td><span class="badge badge-secondary">{{ $row->jumlah }}</span></td>
                        <td><span class="badge badge-info">{{ ucfirst($row->jenis) }}</span></td>
                        <td>{{ $row->keterangan ?? '-' }}</td>
                        <td>{{ $row->user->name ?? '-' }}</td>
                        <td>
                            <form action="{{ route('approval.approve', $row) }}" method="POST" class="d-inline" onsubmit="return confirm('Approve transaksi ini?')">
                                @csrf
                                <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Approve</button>
                            </form>
                            <form action="{{ route('approval.reject', $row) }}" method="POST" class="d-inline" onsubmit="return confirm('Tolak transaksi ini?')">
                                @csrf
                                <button class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Reject</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">Tidak ada transaksi yang menunggu approval.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $pendingList->links() }}</div>
</div>
@endsection
