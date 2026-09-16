@extends('layouts.app')
@section('title', 'Barang Keluar')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <form class="form-inline" method="GET">
            <select name="jenis" class="form-control mr-2" onchange="this.form.submit()">
                <option value="">Semua Jenis</option>
                <option value="penjualan" {{ request('jenis') == 'penjualan' ? 'selected' : '' }}>Penjualan</option>
                <option value="hadiah" {{ request('jenis') == 'hadiah' ? 'selected' : '' }}>Hadiah</option>
                <option value="request" {{ request('jenis') == 'request' ? 'selected' : '' }}>Request</option>
            </select>
            <select name="status" class="form-control mr-2" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </form>
        <a href="{{ route('stockout.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Input Barang Keluar</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Tanggal</th><th>Item</th><th>Cabang</th><th>Jumlah</th><th>Jenis</th><th>Total</th><th>Status</th><th>Oleh</th></tr></thead>
            <tbody>
                @forelse($stockOuts as $row)
                    <tr>
                        <td>{{ $row->tanggal->format('d-m-Y') }}</td>
                        <td>{{ $row->item->nama_items ?? '-' }}</td>
                        <td>{{ $row->cabang->nama_cabang ?? '-' }}</td>
                        <td><span class="badge badge-danger">-{{ $row->jumlah }}</span></td>
                        <td><span class="badge badge-secondary">{{ ucfirst($row->jenis) }}</span></td>
                        <td>{{ $row->total ? 'Rp '.number_format($row->total,0,',','.') : '-' }}</td>
                        <td>
                            @if($row->status == 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($row->status == 'approved')
                                <span class="badge badge-success">Approved</span>
                            @else
                                <span class="badge badge-danger">Rejected</span>
                            @endif
                        </td>
                        <td>{{ $row->user->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">Belum ada data barang keluar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $stockOuts->links() }}</div>
</div>
@endsection
