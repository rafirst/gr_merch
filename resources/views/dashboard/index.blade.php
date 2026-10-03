@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-lg-4 col-6">
        <a href="{{ route('items.index') }}" class="small-box dashboard-stat-box dashboard-link-box" title="Buka Data Items">
            <div class="inner"><h3>{{ $totalItems }}</h3><p>Total Items</p></div>
        </a>
    </div>
    <div class="col-lg-4 col-6">
        <a href="{{ $isAdmin ? route('stockin.index') : route('items.index') }}" class="small-box total-stock-box dashboard-link-box" title="{{ $isAdmin ? 'Buka Data Stock Masuk' : 'Buka Data Items' }}">
            <div class="inner">
                <h3>{{ $totalStok }}</h3>
                <p>Total Stok</p>
            </div>
        </a>
    </div>
    {{-- <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $stokMenipis->count() }}</h3><p>Item Stok Menipis</p></div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div> --}}
    <div class="col-lg-4 col-6">
        <a href="{{ $isAdmin ? route('approval.index') : route('stockout.index') }}" class="small-box dashboard-final-box dashboard-link-box" title="{{ $isAdmin ? 'Buka Approval Items' : 'Buka Barang Keluar' }}">
            <div class="inner"><h3>{{ $pendingApproval }}</h3><p>{{ $isAdmin ? 'Items Approval' : 'Items Request' }}</p></div>
        </a>
    </div>
</div>

<div class="row dashboard-content-row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Grafik Barang Masuk vs Keluar (6 Bulan Terakhir)</h3></div>
            <div class="card-body"><canvas id="chartInOut" style="min-height: 300px;"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Top 5 Item Terlaris</h3></div>
            <div class="top-items-list">
                @php($topSales = max(1, (int) ($topItems->max('total_terjual') ?? 0)))
                @forelse($topItems as $rank => $item)
                    @php($sales = (int) ($item->total_terjual ?? 0))
                    <div class="top-item-row">
                        <span class="top-item-rank">{{ $rank + 1 }}</span>
                        <div class="top-item-content">
                            <div class="top-item-meta">
                                <span class="top-item-name">{{ $item->nama_items }}</span>
                                <span class="top-item-sales">{{ $sales }}</span>
                            </div>
                            <div class="top-item-track">
                                <span style="width: {{ ($sales / $topSales) * 100 }}%"></span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="top-items-empty">Belum ada data penjualan.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card stock-check-card">
    <div class="card-header stock-check-header">
        <div>
            <h3 class="card-title">Ringkasan Stok</h3>        </div>
        </div>
    <div class="card-body">
        <div class="row stock-filter-row">
            <div class="col-12 form-group">
                <label for="stockItemFilter">Cari item</label>
                <div class="stock-search-input">
                    <i class="fas fa-search"></i>
                    <input type="search" id="stockItemFilter" class="form-control" placeholder="Cari kode atau nama item..." autocomplete="off">
                </div>
            </div>
        </div>
        <div class="stock-table-wrap">
            <table class="table stock-check-table">
                <colgroup>
                    <col class="stock-column-item">
                    <col class="stock-column-code">
                    <col class="stock-column-quantity">
                    <col class="stock-column-action">
                </colgroup>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Kode</th>
                        <th class="text-right">Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="stockCheckRows">
                    @forelse($stockItems as $stockGroup)
                        @php($item = $stockGroup['item'])
                        <tr class="stock-check-row" data-search="{{ strtolower($item->kode_items.' '.$item->nama_items) }}">
                            <td>
                                <strong>{{ $item->nama_items }}</strong>
                            </td>
                            <td>{{ $item->kode_items }}</td>
                            <td class="text-right stock-value">{{ $stockGroup['total_stok'] }}</td>
                            <td>
                                <a href="{{ route('dashboard.stock.show', $item) }}" class="btn btn-sm btn-info items-glossy" title="Lihat detail" aria-label="Lihat detail stok lintas cabang">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="stock-empty">Belum ada data stok.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <p id="stockFilterEmpty" class="stock-empty" hidden>Item tidak ditemukan.</p>
        </div>
    </div>
</div>

{{-- <div class="card">
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
</div> --}}
@endsection

@push('styles')
<style>
    .content .dashboard-stat-box,
    .content .total-stock-box,
    .content .dashboard-final-box {
        min-height: 160px;
        height: 160px;
        position: relative;
        top: 0;
        background-color: transparent !important;
        background-position: center;
        background-repeat: no-repeat;
        background-size: 100% 100%;
        border: 0 !important;
    }

    .content a.dashboard-link-box {
        display: block;
        color: inherit;
        text-decoration: none;
        transition: filter 0.2s ease, transform 0.2s ease;
    }

    .content a.dashboard-link-box:hover,
    .content a.dashboard-link-box:focus {
        color: inherit;
        filter: brightness(1.12);
        outline: 0;
        text-decoration: none;
        transform: translateY(-2px);
    }

    .content a.dashboard-link-box:focus-visible {
        box-shadow: 0 0 0 3px rgba(247, 0, 8, 0.5);
    }

    .content .dashboard-stat-box {
        background-image: url('{{ asset('assets/images/box-content-1.png') }}') !important;
        min-height: 160px;
        height: 160px;
        width: 330px;
        position: relative;
        top: 0;
        background-color: transparent !important;
        background-position: center;
        background-repeat: no-repeat;
        background-size: 100% 100%;
        border: 0 !important;
    }

    .content .total-stock-box {
        background-image: url('{{ asset('assets/images/box-content-2.png') }}') !important;
        min-height: 160px;
        height: 180px;
        width: 380px;
        position: relative;
        top: -13px;
        left: -35px;
        background-color: transparent !important;
        background-position: center;
        background-repeat: no-repeat;
        background-size: 100% 100%;
        border: 0 !important;
    }

    .content .dashboard-final-box {
        background-image: url('{{ asset('assets/images/box-content-3.png') }}') !important;
        min-height: 205px;
        height: 180px;
        width: 340px;
        position: relative;
        top: -22px;
        left: -19px;
        background-color: transparent !important;
        background-position: center;
        background-repeat: no-repeat;
        background-size: 100% 100%;
        border: 0 !important;
    }

    .content .dashboard-stat-box::before,
    .content .dashboard-stat-box::after,
    .content .total-stock-box::before,
    .content .total-stock-box::after,
    .content .dashboard-final-box::before,
    .content .dashboard-final-box::after {
        display: none;
    }

    .content .dashboard-stat-box .inner,
    .content .total-stock-box .inner,
    .content .dashboard-final-box .inner {
        font-family: 'syota Type', sans-serif;
        font-weight: 700;
        display: flex;
        height: 100%;
        flex-direction: column;
        justify-content: center;
        padding: 16px 44px;
    }

    .content .total-stock-box .inner {
        padding: 22px 44px 16px 65px;
    }

    .content .dashboard-content-row {
        margin-top: -58px;
    }

    .content .dashboard-content-row > .col-md-4 {
        display: flex;
    }

    .content .dashboard-content-row > .col-md-4 > .card {
        display: flex;
        width: 100%;
        height: calc(100% - 16px);
        flex-direction: column;
    }

    .content .top-items-list {
        display: flex;
        flex: 1;
        min-height: 0;
        flex-direction: column;
        padding: 0.12rem 0.45rem 0.3rem;
        background: rgba(3, 12, 20, 0.9);
    }

    .content .top-item-row {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        min-height: 52px;
        flex: 1;
        padding: 0.2rem 0.12rem;
        border-bottom: 1px solid rgba(105, 143, 176, 0.2);
    }

    .content .top-item-row:last-child {
        border-bottom: 0;
    }

    .content .top-item-rank {
        display: inline-flex;
        width: 26px;
        height: 26px;
        flex: 0 0 26px;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        background: linear-gradient(135deg, #ed1b2f, #8f0714);
        color: #ffffff;
        font-size: 0.82rem;
        font-weight: 700;
        box-shadow: 0 0 5px rgba(239, 29, 47, 0.35);
    }

    .content .top-item-thumb {
        display: inline-flex;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 4px;
        background: rgba(105, 143, 176, 0.18);
        color: rgba(255, 255, 255, 0.72);
    }

    .content .top-item-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .content .top-item-content {
        min-width: 0;
        flex: 1;
    }

    .content .top-item-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.35rem;
        margin-bottom: 0;
        color: #f4f7fa;
        font-size: 0.86rem;
    }

    .content .top-item-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .content .top-item-sales {
        flex: 0 0 auto;
        padding: 0.2rem 0.55rem;
        border: 1px solid rgba(255, 78, 91, 0.78);
        border-radius: 4px;
        background: linear-gradient(90deg, #c9142a, #850713);
        box-shadow: 0 0 5px rgba(239, 29, 47, 0.35);
        color: #ffffff;
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1.1;
        white-space: nowrap;
    }

    .content .top-item-sales::after {
        content: ' terjual';
    }

    .content .top-item-track {
        display: none;
    }

    .content .top-item-track span {
        display: block;
        height: 100%;
        min-width: 4px;
        border-radius: inherit;
        background: #ef1d2f;
        box-shadow: 0 0 8px rgba(239, 29, 47, 0.45);
    }

    .content .top-items-empty {
        padding: 1rem 0.35rem;
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.85rem;
    }

    .content .stock-check-card {
        margin-top: 1.25rem;
        border: 1px solid rgba(105, 143, 176, 0.42);
        background: rgba(3, 12, 20, 0.78);
    }

    .content .stock-check-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(105, 143, 176, 0.24);
    }

    .content .stock-check-subtitle {
        margin: 0.3rem 0 0;
        color: rgba(244, 247, 250, 0.58);
        font-size: 0.78rem;
    }

    .content .stock-live-indicator {
        padding: 0.3rem 0.55rem;
        border: 1px solid rgba(67, 214, 137, 0.45);
        border-radius: 4px;
        color: #62e59b;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .content .stock-live-indicator i {
        margin-right: 0.25rem;
        font-size: 0.5rem;
        vertical-align: middle;
    }

    .content .stock-filter-row {
        margin-bottom: 0.35rem;
    }

    .content .stock-filter-row label {
        color: rgba(244, 247, 250, 0.72);
        font-size: 0.78rem;
        font-weight: 700;
    }

    .content .stock-search-input {
        position: relative;
    }

    .content .stock-search-input i {
        position: absolute;
        z-index: 1;
        top: 50%;
        left: 0.85rem;
        color: rgba(244, 247, 250, 0.5);
        transform: translateY(-50%);
    }

    .content .stock-search-input input {
        padding-left: 2.35rem;
    }

    .content .stock-table-wrap {
        overflow-x: auto;
    }

    .content .stock-check-table {
        width: 100%;
        min-width: 680px;
        margin-bottom: 0;
        table-layout: fixed;
        color: #f4f7fa;
    }

    .content .stock-check-table .stock-column-item {
        width: 35%;
    }

    .content .stock-check-table .stock-column-code {
        width: 20%;
    }

    .content .stock-check-table .stock-column-quantity {
        width: 20%;
    }

    .content .stock-check-table .stock-column-action {
        width: 15%;
    }

    .content .stock-check-table th {
        padding: 0.18rem 0.10rem;
        border-top: 0;
        border-bottom: 1px solid rgba(105, 143, 176, 0.34);
        color: rgba(244, 247, 250, 0.62);
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        line-height: 1.1;
        text-transform: uppercase;
    }

    .content .stock-check-table td {
        padding: 0.35rem 0.25rem;
        border-top: 1px solid rgba(105, 143, 176, 0.16);
        vertical-align: middle;
    }

    .content .stock-check-table th:nth-child(3),
    .content .stock-check-table td:nth-child(3) {
        padding-right: 1rem;
        padding-left: 0.25rem;
        text-align: right !important;
    }

    .content .stock-check-table th:last-child,
    .content .stock-check-table td:last-child {
        padding-right: 2rem;
        text-align: right;
    }

    .content .stock-check-table td strong,
    .content .stock-check-table td small {
        display: block;
    }

    .content .stock-check-table td small {
        margin-top: 0.2rem;
        color: rgba(244, 247, 250, 0.5);
        font-size: 0.72rem;
    }

    .content .stock-check-table td strong {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .content .stock-value {
        color: #ffffff;
        font-size: 1rem;
        font-weight: 700;
    }

    .content .stock-status {
        display: inline-block;
        min-width: 62px;
        padding: 0.25rem 0.45rem;
        border-radius: 4px;
        font-size: 0.72rem;
        font-weight: 700;
        text-align: center;
    }

    .content .stock-status.is-safe {
        background: rgba(67, 214, 137, 0.14);
        color: #62e59b;
    }

    .content .stock-status.is-low {
        background: rgba(247, 0, 8, 0.16);
        color: #ff6970;
    }

    .content .stock-empty {
        margin: 0;
        padding: 1.1rem;
        color: rgba(244, 247, 250, 0.58);
        text-align: center;
    }

    @media (max-width: 767.98px) {
        .content .container-fluid > .row:first-child > [class*="col-"] {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .content .dashboard-stat-box,
        .content .total-stock-box,
        .content .dashboard-final-box {
            top: 0;
            left: 0;
            width: 100%;
            height: 125px;
            min-height: 125px;
            background-size: 100% 100%;
        }

        .content .dashboard-stat-box .inner,
        .content .total-stock-box .inner,
        .content .dashboard-final-box .inner {
            padding: 0.85rem 1rem;
        }

        .content .dashboard-content-row {
            margin-top: 0;
        }

        .content .dashboard-content-row > .col-md-8,
        .content .dashboard-content-row > .col-md-4 {
            margin-bottom: 1rem;
        }

        .content .stock-check-header {
            align-items: flex-start;
            gap: 0.75rem;
            flex-direction: column;
        }
    }

    .content .dashboard-stat-box h3,
    .content .dashboard-stat-box p,
    .content .total-stock-box h3,
    .content .total-stock-box p,
    .content .dashboard-final-box h3,
    .content .dashboard-final-box p {
        color: #f4f7fa;
    }
</style>
@endpush

@push('scripts')
<script>
    const ctx = document.getElementById('chartInOut');
    const chartDataIn = @json($dataIn);
    const chartDataOut = @json($dataOut);
    const chartMaximum = Math.max(...chartDataIn, ...chartDataOut, 0);
    const chartYAxisMaximum = Math.max(20, Math.ceil(chartMaximum / 20) * 20);

    const stockItemFilter = document.getElementById('stockItemFilter');
    const stockRows = Array.from(document.querySelectorAll('.stock-check-row'));
    const stockFilterEmpty = document.getElementById('stockFilterEmpty');

    function filterStockRows() {
        const search = stockItemFilter.value.trim().toLowerCase();
        let visibleRows = 0;

        stockRows.forEach((row) => {
            const matchesSearch = !search || row.dataset.search.includes(search);
            const isVisible = matchesSearch;

            row.hidden = !isVisible;
            visibleRows += isVisible ? 1 : 0;
        });

        stockFilterEmpty.hidden = visibleRows > 0;
    }

    stockItemFilter.addEventListener('input', filterStockRows);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($bulanLabel),
            datasets: [
                {
                    label: 'Barang Keluar',
                    data: @json($dataOut),
                    borderColor: '#ef1d2f',
                    backgroundColor: '#ef1d2f',
                    pointBackgroundColor: '#ef1d2f',
                    pointBorderColor: '#ef1d2f',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2,
                    tension: 0.35,
                    fill: false,
                },
                {
                    label: 'Barang Masuk',
                    data: @json($dataIn),
                    borderColor: '#c5ccd3',
                    backgroundColor: '#c5ccd3',
                    pointBackgroundColor: '#c5ccd3',
                    pointBorderColor: '#c5ccd3',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2,
                    tension: 0.35,
                    fill: false,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: 'rgba(255, 255, 255, 0.72)',
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 18,
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.72)',
                        autoSkip: false,
                    },
                    grid: { color: 'rgba(255, 255, 255, 0.14)' },
                    border: { color: 'rgba(255, 255, 255, 0.5)' }
                },
                y: {
                    min: 0,
                    max: chartYAxisMaximum,
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.72)',
                        stepSize: 20,
                    },
                    grid: { color: 'rgba(255, 255, 255, 0.14)' },
                    border: { color: 'rgba(255, 255, 255, 0.5)' }
                }
            }
        }
    });
</script>
@endpush
