@extends('layouts.app')
@section('title', 'Barang Masuk')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Riwayat Barang Masuk</h3>
        <a href="{{ route('stockin.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Input Barang Masuk</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Tanggal</th><th>Kode</th><th>Item</th><th>Cabang</th><th>Jumlah</th><th>Sumber</th><th>Diinput Oleh</th></tr></thead>
            <tbody>
                @forelse($stockIns as $row)
                    <tr>
                        <td>{{ $row->tanggal->format('d-m-Y') }}</td>
                        <td>{{ $row->item->kode_items ?? '-' }}</td>
                        <td>{{ $row->item->nama_items ?? '-' }}</td>
                        <td>{{ $row->cabang->nama_cabang ?? '-' }}</td>
                        <td><span class="badge badge-success">+{{ $row->jumlah }}</span></td>
                        <td>{{ $row->sumber ?? '-' }}</td>
                        <td>{{ $row->user->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Belum ada data barang masuk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $stockIns->links() }}</div>
</div>
@endsection
