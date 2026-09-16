@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $totalItems }}</h3><p>Total Items</p></div>
            <div class="icon"><i class="fas fa-box"></i></div>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $totalStok }}</h3><p>Total Stok</p></div>
            <div class="icon"><i class="fas fa-warehouse"></i></div>
        </div>
    </div>
    {{-- <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $stokMenipis->count() }}</h3><p>Item Stok Menipis</p></div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div> --}}
    <div class="col-lg-4 col-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ $pendingApproval }}</h3><p>Items Pending</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            {{-- @if($isAdmin)
            <a href="{{ route('approval.index') }}" class="small-box-footer text-white">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
            @endif --}}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Grafik Barang Masuk vs Keluar (6 Bulan Terakhir)</h3></div>
            <div class="card-body"><canvas id="chartInOut" style="min-height: 300px;"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Top 5 Item Terlaris</h3></div>
            <ul class="list-group list-group-flush">
                @forelse($topItems as $item)
                    <li class="list-group-item d-flex justify-content-between">
                        {{ $item->nama_items }}
                        <span class="badge badge-primary badge-pill">{{ $item->total_terjual ?? 0 }} terjual</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Belum ada data penjualan.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title text-warning"><i class="fas fa-exclamation-triangle"></i> Item Stok Menipis</h3></div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Kode</th><th>Nama Item</th><th>Cabang</th><th>Stok</th><th>Stok Minimum</th></tr></thead>
            <tbody>
                @forelse($stokMenipis as $item)
                    <tr>
                        <td>{{ $item->kode_items }}</td>
                        <td>{{ $item->nama_items }}</td>
                        <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
                        <td><span class="badge badge-danger">{{ $item->stok_items }}</span></td>
                        <td>{{ $item->stok_minimum }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">Semua stok aman.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const ctx = document.getElementById('chartInOut');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($bulanLabel),
            datasets: [
                { label: 'Barang Masuk', data: @json($dataIn), backgroundColor: '#28a745' },
                { label: 'Barang Keluar', data: @json($dataOut), backgroundColor: '#dc3545' },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    ticks: { color: 'rgba(255, 255, 255, 0.72)' },
                    grid: { color: 'rgba(255, 255, 255, 0.14)' },
                    border: { color: 'rgba(255, 255, 255, 0.5)' }
                },
                y: {
                    ticks: { color: 'rgba(255, 255, 255, 0.72)' },
                    grid: { color: 'rgba(255, 255, 255, 0.14)' },
                    border: { color: 'rgba(255, 255, 255, 0.5)' }
                }
            }
        }
    });
</script>
@endpush
