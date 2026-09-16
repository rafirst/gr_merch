@extends('layouts.app')
@section('title', 'Histori Transaksi')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <form class="form-inline" method="GET">
            <select name="tipe" class="form-control mr-2" onchange="this.form.submit()">
                <option value="">-- Ringkasan Terbaru --</option>
                <option value="in" {{ ($tipe ?? '') == 'in' ? 'selected' : '' }}>Barang Masuk</option>
                <option value="out" {{ ($tipe ?? '') == 'out' ? 'selected' : '' }}>Barang Keluar</option>
            </select>
            <input type="text" name="item" class="form-control mr-2" placeholder="Nama item" value="{{ request('item') }}">
            <input type="date" name="dari" class="form-control mr-2" value="{{ request('dari') }}">
            <input type="date" name="sampai" class="form-control mr-2" value="{{ request('sampai') }}">
            <button class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
        </form>
        <a href="{{ route('history.export') }}" class="btn btn-success"><i class="fas fa-file-excel"></i> Export Excel</a>
    </div>
    <div class="card-body p-0">

    @if(($tipe ?? null) === 'in')
        <table class="table table-striped mb-0">
            <thead><tr><th>Tanggal</th><th>Item</th><th>Cabang</th><th>Jumlah</th><th>Sumber</th><th>Oleh</th></tr></thead>
            <tbody>
            @forelse($riwayat as $row)
                <tr>
                    <td>{{ $row->tanggal->format('d-m-Y') }}</td>
                    <td>{{ $row->item->nama_items ?? '-' }}</td>
                    <td>{{ $row->cabang->nama_cabang ?? '-' }}</td>
                    <td><span class="badge badge-success">+{{ $row->jumlah }}</span></td>
                    <td>{{ $row->sumber ?? '-' }}</td>
                    <td>{{ $row->user->name ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Tidak ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $riwayat->links() }}</div>

    @elseif(($tipe ?? null) === 'out')
        <table class="table table-striped mb-0">
            <thead><tr><th>Tanggal</th><th>Item</th><th>Cabang</th><th>Jumlah</th><th>Jenis</th><th>Status</th><th>Oleh</th><th>Approved By</th></tr></thead>
            <tbody>
            @forelse($riwayat as $row)
                <tr>
                    <td>{{ $row->tanggal->format('d-m-Y') }}</td>
                    <td>{{ $row->item->nama_items ?? '-' }}</td>
                    <td>{{ $row->cabang->nama_cabang ?? '-' }}</td>
                    <td><span class="badge badge-danger">-{{ $row->jumlah }}</span></td>
                    <td>{{ ucfirst($row->jenis) }}</td>
                    <td>{{ ucfirst($row->status) }}</td>
                    <td>{{ $row->user->name ?? '-' }}</td>
                    <td>{{ $row->approver->name ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">Tidak ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $riwayat->links() }}</div>

    @else
        <div class="row p-3">
            <div class="col-md-6">
                <h5>Barang Masuk Terbaru</h5>
                <table class="table table-sm table-striped">
                    <thead><tr><th>Tanggal</th><th>Item</th><th>Jumlah</th></tr></thead>
                    <tbody>
                    @forelse($stockIns as $row)
                        <tr><td>{{ $row->tanggal->format('d-m-Y') }}</td><td>{{ $row->item->nama_items ?? '-' }}</td><td class="text-success">+{{ $row->jumlah }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center">Belum ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <h5>Barang Keluar Terbaru</h5>
                <table class="table table-sm table-striped">
                    <thead><tr><th>Tanggal</th><th>Item</th><th>Jumlah</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($stockOuts as $row)
                        <tr><td>{{ $row->tanggal->format('d-m-Y') }}</td><td>{{ $row->item->nama_items ?? '-' }}</td><td class="text-danger">-{{ $row->jumlah }}</td><td>{{ ucfirst($row->status) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center">Belum ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    </div>
</div>
@endsection
