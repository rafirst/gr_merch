@extends('layouts.app')
@section('title', 'Detail Transfer')

@section('content')
@php
    $transferCode = 'TRF-'.str_pad((string) $transfer->id, 5, '0', STR_PAD_LEFT);
    $transferDate = $transfer->sent_at ?? $transfer->created_at;
    $totalQuantity = $transfer->items->sum('jumlah_dikirim');
@endphp

<div class="card transfer-detail-card">
    <header class="card-header transfer-show-heading">
        <div class="transfer-show-title">
            <span class="transfer-show-title-icon"><i class="fas fa-exchange-alt"></i></span>
            <div>
                <h2>Detail Transfer</h2>
                <p>{{ $transferCode }} <span aria-hidden="true">&bull;</span> {{ $transferDate->format('d/m/Y, H:i') }}</p>
            </div>
        </div>
        <div class="transfer-detail-actions">
            @if($transfer->status === 'proses' && auth()->user()->isAdminHo())
                <a href="{{ route('transfers.edit', $transfer) }}" class="btn transfer-detail-button transfer-detail-edit"><i class="fas fa-pen"></i> Edit</a>
            @endif
            <a href="{{ route('transfers.index') }}" class="btn transfer-detail-button transfer-detail-back"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </header>

    <div class="card-body">
    <div class="transfer-show-page">
    <section class="transfer-show-panel transfer-route-panel">
        <header class="transfer-panel-heading"><i class="fas fa-route"></i><h3>Rute Transfer</h3></header>
        <div class="transfer-route-content">
            <div class="transfer-route-stop">
                <span class="transfer-route-icon"><i class="fas fa-warehouse"></i></span>
                <div><small>Cabang Asal</small><strong>{{ $transfer->fromCabang->nama_cabang }}</strong><span>Pengirim</span></div>
            </div>
            <i class="fas fa-arrow-right transfer-route-connector" aria-hidden="true"></i>
            <div class="transfer-route-stop">
                <span class="transfer-route-icon"><i class="fas fa-warehouse"></i></span>
                <div><small>Cabang Tujuan</small><strong>{{ $transfer->toCabang->nama_cabang }}</strong><span>Penerima</span></div>
            </div>
            <div class="transfer-route-creator">
                <div class="transfer-route-creator-person">
                    <i class="fas fa-user" aria-hidden="true"></i>
                    <div><small>Dibuat Oleh</small><strong>{{ $transfer->creator->name ?? '-' }}</strong></div>
                </div>
                <div class="transfer-route-creator-status transfer-route-status-{{ $transfer->status }}" aria-label="Status transfer {{ $transfer->status }}">
                    <span class="transfer-route-status-point"></span>
                    <div><small>Status Transfer</small><strong>{{ ucfirst($transfer->status) }}</strong></div>
                </div>
            </div>
        </div>
    </section>

    <section class="transfer-show-panel transfer-products-panel">
        <header class="transfer-panel-heading transfer-products-heading">
            <div><i class="fas fa-cube"></i><h3>Data Barang</h3></div>
            <span><i class="fas fa-boxes"></i> {{ $transfer->items->count() }} Jenis <b>&middot;</b> {{ $totalQuantity }} pcs</span>
        </header>
        <div class="table-responsive">
            <table class="table mb-0 transfer-detail-table">
                <thead><tr><th>Kode</th><th>Barang</th><th>Dikirim</th><th>Diterima</th><th>Selisih</th></tr></thead>
                <tbody>
                    @foreach($transfer->items as $line)
                        <tr>
                            <td>{{ $line->sourceItem->kode_items }}</td>
                            <td>
                                <div class="transfer-product-cell">
                                    <span class="transfer-product-photo">
                                        @if($line->sourceItem->foto)
                                            <img src="{{ url('storage/'.ltrim($line->sourceItem->foto, '/')) }}" alt="{{ $line->sourceItem->nama_items }}">
                                        @else
                                            <i class="fas fa-box"></i>
                                        @endif
                                    </span>
                                    <strong>{{ $line->sourceItem->nama_items }}</strong>
                                </div>
                            </td>
                            <td>{{ $line->jumlah_dikirim }}</td>
                            <td>{{ $line->jumlah_diterima ?? '-' }}</td>
                            <td>
                                @if($line->jumlah_diterima !== null && $line->jumlah_diterima !== $line->jumlah_dikirim)
                                    <span>{{ $line->jumlah_dikirim - $line->jumlah_diterima }}</span>
                                    @if($line->catatan_selisih)<small class="transfer-detail-note">{{ $line->catatan_selisih }}</small>@endif
                                @elseif($line->catatan_selisih)
                                    <small class="transfer-detail-note">{{ $line->catatan_selisih }}</small>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <div class="transfer-show-bottom-grid">
        <section class="transfer-show-panel transfer-proof-panel">
            <header class="transfer-panel-heading"><i class="fas fa-camera"></i><h3>Foto Bukti Transfer</h3></header>
            @if($transfer->bukti_foto)
                <div class="transfer-proof-content">
                    <div class="transfer-proof-gallery">
                        @foreach($transfer->bukti_foto as $index => $proofPath)
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($proofPath) }}" target="_blank" rel="noopener" aria-label="Buka foto bukti transfer {{ $index + 1 }}">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($proofPath) }}" alt="Foto bukti transfer {{ $index + 1 }}">
                                <span class="transfer-proof-zoom"><i class="fas fa-expand-alt"></i></span>
                            </a>
                        @endforeach
                    </div>
                    <div class="transfer-proof-caption"><i class="fas fa-image"></i> {{ count($transfer->bukti_foto) }} foto <span>Klik foto untuk memperbesar</span></div>
                </div>
            @else
                <div class="transfer-proof-empty"><i class="far fa-image"></i><span>Belum ada foto bukti transfer.</span></div>
            @endif
        </section>

        <section class="transfer-show-panel transfer-info-panel">
            <header class="transfer-panel-heading"><i class="fas fa-info-circle"></i><h3>Informasi Transaksi</h3></header>
            <dl class="transfer-transaction-info">
                <dt>Kode Transfer</dt><dd>{{ $transferCode }}</dd>
                <dt>Tanggal</dt><dd>{{ $transferDate->format('d/m/Y, H:i') }}</dd>
                <dt>Dibuat Oleh</dt><dd><i class="fas fa-user"></i> {{ $transfer->creator->name ?? '-' }}</dd>
                @if($transfer->received_at)
                    <dt>Diterima Oleh</dt><dd>{{ $transfer->receiver->name ?? '-' }}</dd>
                    <dt>Waktu Diterima</dt><dd>{{ $transfer->received_at->format('d/m/Y, H:i') }}</dd>
                @endif
            </dl>
        </section>
    </div>
</div>
</div>
@endsection

@push('styles')
<style>
    .transfer-detail-card { overflow:hidden; }
    .transfer-show-page { display:grid; gap:.8rem; }
    .transfer-show-heading { display:flex; width:100%; align-items:center; justify-content:flex-start; gap:1rem; }
    .transfer-show-heading::before { height:100%; }
    .transfer-show-heading .card-title { margin:0; }
    .transfer-show-title { display:flex; flex:1 1 auto; align-items:center; gap:.7rem; min-width:0; }
    .transfer-show-title-icon { color:#fff; font-size:1.35rem; }
    .transfer-show-title h2 { margin:0; color:#fff; font-size:1.45rem; font-weight:700; }
    .transfer-show-title p { margin:.15rem 0 0; color:rgba(244,247,250,.66); font-size:.78rem; }
    .transfer-show-title p span { margin:0 .35rem; color:#f20b18; }
    .transfer-detail-actions { display:flex; align-items:center; flex:0 0 auto; gap:.45rem; margin-left:auto; }
    .transfer-detail-button { display:inline-flex; align-items:center; gap:.4rem; padding:.45rem .75rem; border:1px solid rgba(255,255,255,.18); border-radius:4px; color:#fff !important; font-size:.82rem; }
    .transfer-detail-edit { border-color:rgba(255,193,7,.7); background:#e5a900; }
    .transfer-detail-back { border-color:rgba(174,185,196,.45); background:#586674; }
    .transfer-show-panel { min-width:0; overflow:hidden; border:1px solid rgba(105,143,176,.32); border-radius:5px; background:rgba(3,11,18,.78); }
    .transfer-panel-heading { display:flex; min-height:42px; align-items:center; gap:.55rem; padding:.65rem .85rem; border-bottom:1px solid rgba(255,255,255,.1); }
    .transfer-panel-heading > i, .transfer-panel-heading > div > i { color:#d1dce6; font-size:.9rem; }
    .transfer-panel-heading h3 { margin:0; color:#f4f7fa; font-size:.88rem; font-weight:700; }
    .transfer-route-content { display:grid; grid-template-columns:minmax(0,1fr) 40px minmax(0,1fr) minmax(160px,.75fr); align-items:center; gap:.6rem; padding:1rem; }
    .transfer-route-stop { display:flex; min-width:0; align-items:center; gap:.7rem; }
    .transfer-route-icon { display:flex; width:44px; height:44px; flex:0 0 44px; align-items:center; justify-content:center; border-radius:50%; background:rgba(105,143,176,.14); color:#c7d5e1; font-size:1rem; }
    .transfer-route-stop > div { display:grid; min-width:0; gap:.18rem; }
    .transfer-route-stop small, .transfer-route-creator small { color:rgba(244,247,250,.56); font-size:.68rem; }
    .transfer-route-stop strong, .transfer-route-creator strong { overflow:hidden; color:#fff; font-size:.86rem; text-overflow:ellipsis; white-space:nowrap; }
    .transfer-route-stop > div > span { justify-self:start; padding:.15rem .4rem; border:1px solid rgba(105,143,176,.25); border-radius:3px; color:rgba(244,247,250,.66); font-size:.62rem; }
    .transfer-route-connector { color:#c7d5e1; text-align:center; }
    .transfer-route-creator { display:flex; min-height:66px; align-items:flex-start; flex-direction:column; justify-content:center; gap:.65rem; padding-left:1rem; border-left:1px solid rgba(255,255,255,.1); }
    .transfer-route-creator-person, .transfer-route-creator-status { display:flex; align-items:center; gap:.55rem; min-width:0; }
    .transfer-route-creator-person > i { color:#c7d5e1; }
    .transfer-route-creator-person > div, .transfer-route-creator-status > div { display:grid; min-width:0; gap:.18rem; }
    .transfer-route-creator-status small { color:rgba(244,247,250,.56); font-size:.66rem; }
    .transfer-route-creator-status strong { color:#ffca3a; font-size:.78rem; }
    .transfer-route-status-point { width:10px; height:10px; flex:0 0 10px; border-radius:50%; background:#ffbf24; box-shadow:0 0 0 4px rgba(255,191,36,.12); }
    .transfer-route-status-diterima .transfer-route-status-point { background:#36cc84; box-shadow:0 0 0 4px rgba(54,204,132,.12); }
    .transfer-route-status-diterima strong { color:#70e19a; }
    .transfer-route-status-batal .transfer-route-status-point { background:#ee5364; box-shadow:0 0 0 4px rgba(238,83,100,.12); }
    .transfer-route-status-batal strong { color:#ff8793; }
    .transfer-products-heading { justify-content:space-between; }
    .transfer-products-heading > div { display:flex; align-items:center; gap:.55rem; }
    .transfer-products-heading > span { color:rgba(244,247,250,.66); font-size:.72rem; white-space:nowrap; }
    .transfer-products-heading > span i { margin-right:.3rem; }
    .transfer-products-heading b { margin:0 .25rem; color:#fff; }
    .transfer-detail-table { min-width:600px; }
    .transfer-detail-table thead th { padding:.62rem .8rem; color:rgba(244,247,250,.75); font-size:.7rem; }
    .transfer-detail-table tbody td { padding:.55rem .8rem; vertical-align:middle; }
    .transfer-detail-table th { white-space:nowrap; }
    .transfer-product-cell { display:flex; align-items:center; gap:.6rem; min-width:180px; }
    .transfer-product-photo { display:flex; width:40px; height:40px; flex:0 0 40px; align-items:center; justify-content:center; overflow:hidden; border:1px solid rgba(105,143,176,.25); border-radius:4px; background:rgba(0,0,0,.25); color:rgba(244,247,250,.55); }
    .transfer-product-photo img { width:100%; height:100%; object-fit:contain; }
    .transfer-product-cell strong { color:#f4f7fa; font-size:.78rem; }
    .transfer-detail-note { display:block; max-width:240px; margin-top:.18rem; color:#ffcf70; font-size:.68rem; white-space:normal; }
    .transfer-show-bottom-grid { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(280px,1fr); gap:.8rem; align-items:stretch; }
    .transfer-proof-gallery { display:grid; grid-template-columns:repeat(auto-fill,minmax(126px,1fr)); gap:.55rem; padding:.75rem; }
    .transfer-proof-gallery a { position:relative; display:block; height:105px; overflow:hidden; border:1px solid rgba(105,143,176,.3); border-radius:4px; background:rgba(0,0,0,.25); }
    .transfer-proof-gallery img { width:100%; height:100%; object-fit:cover; transition:transform .18s ease; }
    .transfer-proof-gallery a:hover img { transform:scale(1.04); }
    .transfer-proof-zoom { position:absolute; top:.35rem; right:.35rem; display:flex; width:25px; height:25px; align-items:center; justify-content:center; border-radius:4px; background:rgba(0,0,0,.68); color:#fff; font-size:.68rem; }
    .transfer-proof-caption { display:flex; align-items:center; gap:.35rem; padding:0 .75rem .7rem; color:rgba(244,247,250,.62); font-size:.65rem; }
    .transfer-proof-caption span { margin-left:auto; color:rgba(244,247,250,.48); }
    .transfer-proof-empty { display:flex; min-height:116px; align-items:center; justify-content:center; flex-direction:column; gap:.45rem; color:rgba(244,247,250,.5); font-size:.73rem; }
    .transfer-proof-empty i { font-size:1.4rem; }
    .transfer-transaction-info { display:grid; grid-template-columns:minmax(105px,.85fr) minmax(0,1.2fr); gap:.75rem .9rem; margin:0; padding:.85rem; }
    .transfer-transaction-info dt { color:rgba(244,247,250,.58); font-size:.71rem; font-weight:400; }
    .transfer-transaction-info dd { min-width:0; margin:0; color:#f4f7fa; font-size:.73rem; overflow-wrap:anywhere; }
    .transfer-transaction-info dd i { margin-right:.25rem; color:#c7d5e1; }
    @media(max-width:900px) {
        .transfer-route-content { grid-template-columns:minmax(0,1fr) 32px minmax(0,1fr); }
        .transfer-route-creator { grid-column:1/-1; min-height:0; margin-top:.2rem; padding:.7rem 0 0; border-top:1px solid rgba(255,255,255,.1); border-left:0; }
    }
    @media(max-width:767.98px) {
        .transfer-show-bottom-grid { grid-template-columns:1fr; }
    }
    @media(max-width:575.98px) {
        .transfer-show-heading { align-items:flex-start; flex-direction:column; }
        .transfer-show-title h2 { font-size:1.2rem; }
        .transfer-detail-actions { width:100%; justify-content:flex-end; margin-left:0; }
        .transfer-route-content { grid-template-columns:minmax(0,1fr) 24px minmax(0,1fr); gap:.35rem; padding:.7rem; }
        .transfer-route-stop { align-items:flex-start; flex-direction:column; gap:.35rem; }
        .transfer-route-stop strong { font-size:.72rem; white-space:normal; }
        .transfer-route-icon { width:34px; height:34px; flex-basis:34px; font-size:.8rem; }
        .transfer-products-heading { align-items:flex-start; flex-direction:column; }
        .transfer-transaction-info { grid-template-columns:minmax(95px,.8fr) minmax(0,1.2fr); gap:.65rem; }
    }
</style>
@endpush