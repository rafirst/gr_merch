@extends('layouts.app')
@section('title', 'Detail Histori')

@section('content')
@php
    $isStockIn = $type === 'in';
    $isStockOut = in_array($type, ['out', 'approval-out'], true);
    $isApproval = str_starts_with($type, 'approval-');
    $isStockInApproval = $type === 'approval-in';
    $item = $isStockInApproval ? $record->oldItem : $record->item;
    $cabang = $isStockInApproval ? $record->oldItem?->cabang : $record->cabang;
    $requester = $isStockInApproval ? $record->requester : $record->user;
@endphp

<div class="card history-detail-card">
    <div class="card-header history-detail-header">
        <div>
            <span class="history-detail-kicker">History Record</span>
            <h3 class="card-title mb-0">Detail Histori</h3>
        </div>
        <a href="{{ route('history.index', $isApproval ? ['tipe' => 'approval'] : ['tipe' => $isStockIn ? 'in' : 'out']) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="history-detail-type history-detail-type-{{ $isApproval ? 'approval' : ($isStockIn ? 'in' : 'out') }}">
            <i class="fas {{ $isApproval ? 'fa-check-circle' : ($isStockIn ? 'fa-arrow-down' : 'fa-arrow-up') }}"></i>
            <span>{{ $isApproval ? ($isStockInApproval ? 'Edit Stok' : 'Approval Transaksi') : ($isStockIn ? 'Barang Masuk' : 'Barang Keluar') }}</span>
        </div>

        @if($item?->foto)
            <section class="history-item-photo-panel" aria-labelledby="historyItemPhotoTitle">
                <div class="history-item-photo-copy">
                    <span class="history-detail-kicker">Item Image</span>
                    <h4 id="historyItemPhotoTitle">Foto Item</h4>
                    <strong>{{ $item->nama_items }}</strong>
                </div>
                <img src="{{ url('storage/'.ltrim($item->foto, '/')) }}" alt="Foto {{ $item->nama_items }}" class="history-item-photo">
            </section>
        @endif

        <section class="history-information-panel" aria-labelledby="historyInformationTitle">
            <div class="history-information-header">
                <i class="fas fa-file-alt"></i>
                <h4 id="historyInformationTitle">Informasi Transaksi</h4>
            </div>
            <div class="history-detail-grid">
                <div class="history-detail-row"><span>Item</span><strong>{{ $item->nama_items ?? '-' }}</strong></div>
                <div class="history-detail-row"><span>Cabang</span><strong>{{ $cabang->nama_cabang ?? '-' }}</strong></div>
                <div class="history-detail-row"><span>User Pengaju</span><strong>{{ $requester->name ?? '-' }}</strong></div>
                <div class="history-detail-row"><span>Tanggal</span><strong>{{ ($isStockInApproval ? $record->new_tanggal : $record->tanggal)?->format('d-m-Y') ?? '-' }}</strong></div>
                @if($isStockInApproval)
                    <div class="history-detail-row"><span>Jumlah Perubahan</span><strong>{{ $record->old_jumlah }} &rarr; {{ $record->new_jumlah }} {{ $record->newItem->harga_jual ? 'Rp ' . number_format($record->newItem->harga_jual, 0, ',', '.') : '-' }}</strong></div>
                    <div class="history-detail-row"><span>Tipe Request</span><strong>Edit Stok</strong></div>
                    <div class="history-detail-row history-detail-row-wide"><span>Keterangan</span><strong>{{ $record->new_keterangan ?: '-' }}</strong></div>
                @elseif($isStockOut)
                    <div class="history-detail-row"><span>Jumlah</span><strong>{{ $record->jumlah }} {{ $item->harga_jual ? 'Rp ' . number_format($item->harga_jual, 0, ',', '.') : '-' }}</strong></div>
                    <div class="history-detail-row"><span>Jenis</span><strong>{{ ucfirst($record->jenis) }}</strong></div>
                    @if($isApproval)
                        <div class="history-detail-row"><span>Status Approval</span><strong>{{ ucfirst($record->status) }}</strong></div>
                        <div class="history-detail-row"><span>Approved By</span><strong>{{ $record->approver->name ?? '-' }}</strong></div>
                    @endif
                    <div class="history-detail-row history-detail-row-wide"><span>Keterangan</span><strong>{{ $record->keterangan ?: '-' }}</strong></div>
                @else
                    <div class="history-detail-row"><span>Jumlah Masuk</span><strong>+{{ $record->jumlah }} {{ $item->harga_jual ? 'Rp ' . number_format($item->harga_jual, 0, ',', '.') : '-' }}</strong></div>
                    <div class="history-detail-row"><span>Sumber</span><strong>{{ $record->sumber ?: '-' }}</strong></div>
                    <div class="history-detail-row history-detail-row-wide"><span>Keterangan</span><strong>{{ $record->keterangan ?: '-' }}</strong></div>
                @endif
                @if($isApproval && $isStockInApproval)
                    <div class="history-detail-row"><span>Status Approval</span><strong>{{ ucfirst($record->status) }}</strong></div>
                    <div class="history-detail-row"><span>Approved By</span><strong>{{ $record->approver->name ?? '-' }}</strong></div>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
    .history-detail-header { display: flex; align-items: center; justify-content: space-between; width: 100%; gap: 1rem; }
    .history-detail-header > div:first-child { margin-right: auto; }
    .history-detail-header > .btn { flex: 0 0 auto; margin-left: auto; }
    .history-detail-kicker { display: block; margin-bottom: 0.2rem; color: #ef1d2f; font-size: 0.68rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; }
    .history-detail-type { display: inline-flex; align-items: center; gap: 0.45rem; margin-bottom: 0.8rem; padding: 0.45rem 0.7rem; border: 1px solid rgba(255, 255, 255, 0.24); font-size: 0.78rem; font-weight: 700; }
    .history-detail-type-in { background: linear-gradient(145deg, #5be58a, #078138); color: #fff; }
    .history-detail-type-out { background: linear-gradient(145deg, #ff7180, #b6122b); color: #fff; }
    .history-detail-type-approval { background: linear-gradient(145deg, #ffe477, #a76500); color: #271900; }
    .history-item-photo-panel { display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; margin-bottom: 1rem; padding: 0.85rem 1rem; border: 1px solid rgba(105, 143, 176, 0.28); border-left: 3px solid #f70008; background: rgba(5, 14, 23, 0.72); }
    .history-item-photo-copy h4 { margin: 0.15rem 0 0.25rem; color: #fff; font-size: 0.9rem; text-transform: uppercase; }
    .history-item-photo-copy strong { color: rgba(244, 247, 250, 0.78); font-weight: 500; }
    .history-item-photo { width: 92px; height: 70px; border: 1px solid rgba(105, 143, 176, 0.45); border-radius: 4px; background: rgba(0, 0, 0, 0.28); object-fit: contain; }
    .history-information-panel { overflow: hidden; border: 1px solid rgba(105, 143, 176, 0.28); background: rgba(2, 10, 17, 0.35); }
    .history-information-header { display: flex; align-items: center; gap: 0.6rem; padding: 0.7rem 0.85rem; border-bottom: 1px solid rgba(105, 143, 176, 0.2); background: rgba(5, 14, 23, 0.7); color: rgba(244, 247, 250, 0.82); }
    .history-information-header h4 { margin: 0; font-size: 0.82rem; letter-spacing: 0.04em; text-transform: uppercase; }
    .history-detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .history-detail-row { display: grid; grid-template-columns: 38% minmax(0, 1fr); align-items: center; min-height: 52px; padding: 0 0.85rem; border-bottom: 1px solid var(--gazoo-border); }
    .history-detail-row span { color: rgba(244, 247, 250, 0.58); font-size: 0.72rem; letter-spacing: 0.04em; text-transform: uppercase; }
    .history-detail-row strong { color: #fff; font-weight: 500; overflow-wrap: anywhere; }
    .history-detail-row-wide { grid-column: 1 / -1; grid-template-columns: 18.3% minmax(0, 1fr); }
    @media (max-width: 767.98px) { .history-detail-header { align-items: flex-start; flex-direction: column; } .history-item-photo-panel { align-items: flex-start; flex-direction: column; } .history-item-photo { width: 100%; height: 150px; } .history-detail-grid { grid-template-columns: 1fr; } .history-detail-row-wide { grid-column: auto; grid-template-columns: 38% minmax(0, 1fr); } }
</style>
@endpush
