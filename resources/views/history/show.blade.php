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
    $formatRupiah = fn ($value) => $value === null || $value === '' ? '-' : 'Rp '.number_format((float) $value, 0, ',', '.');
    $formatTanggal = fn ($value) => $value ? date('d-m-Y', strtotime((string) $value)) : '-';
    $formatTanggalWaktu = fn ($value) => $value ? date('d-m-Y H:i', strtotime((string) $value)) : '-';
    $discountLabel = $isStockOut && ! $isStockInApproval && $record->jenis === 'penjualan'
        ? $record->discountLabel()
        : '-';
    $paketLabel = $isStockOut ? match ($record->paket_bundling ?? null) {
        'paket_a' => 'Paket A [ Racing Cap + Umbrella ]',
        'paket_b' => 'Paket B [ T-Shirt + Tumbler ]',
        'paket_c' => 'Paket C [ Softshell Jacket + Mechanic ]',
        default => '-',
    } : '-';
    $jenisBayarLabel = $isStockOut ? match ($record->jenis_pembayaran ?? null) {
        'qris' => 'QRIS',
        'transfer' => 'Transfer',
        default => '-',
    } : '-';
@endphp

<div class="card history-detail-card">
    <div class="card-header history-detail-header">
        <div>
            <span class="history-detail-kicker">History Record #{{ $record->id }}</span>
            <h3 class="card-title mb-0">Detail Histori</h3>
        </div>
        <a href="{{ route('history.index', $isApproval ? ['tipe' => 'approval'] : ['tipe' => $isStockIn ? 'in' : 'out']) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="history-detail-type history-detail-type-{{ $isApproval ? 'approval' : ($isStockIn ? 'in' : 'out') }}">
            <i class="fas {{ $isApproval ? 'fa-check-circle' : ($isStockIn ? 'fa-arrow-down' : 'fa-arrow-up') }}"></i>
            <span>{{ $isApproval ? ($isStockInApproval ? 'Edit Stok' : 'Approval Transaksi — '.ucfirst($record->jenis ?? '-')) : ($isStockIn ? 'Barang Masuk' : 'Barang Keluar — '.($record->jenis === 'DO' ? 'DO' : ucfirst($record->jenis ?? '-'))) }}</span>
        </div>

        @if($item?->foto)
            <section class="history-item-photo-panel" aria-labelledby="historyItemPhotoTitle">
                <div class="history-item-photo-copy">
                    <span class="history-detail-kicker">Item Image</span>
                    <h4 id="historyItemPhotoTitle">Foto Item</h4>
                    <strong>{{ $item?->nama_items ?? '-' }}</strong>
                </div>
                <img src="{{ url('storage/'.ltrim($item?->foto ?? '', '/')) }}" alt="Foto {{ $item?->nama_items ?? '-' }}" class="history-item-photo">
            </section>
        @endif

        <section class="history-information-panel" aria-labelledby="historySummaryTitle">
            <div class="history-information-header">
                <i class="fas fa-file-alt"></i>
                <h4 id="historySummaryTitle">Ringkasan Transaksi</h4>
            </div>
            <div class="history-detail-grid">
                <div class="history-detail-row"><span>ID Transaksi</span><strong>#{{ str_pad((string) $record->id, 6, '0', STR_PAD_LEFT) }}</strong></div>
                <div class="history-detail-row"><span>Tanggal</span><strong>{{ $formatTanggal($isStockInApproval ? $record->new_tanggal : $record->tanggal) }}</strong></div>
                <div class="history-detail-row"><span>Item</span><strong>{{ $item?->nama_items ?? '-' }}</strong></div>
                <div class="history-detail-row"><span>Cabang</span><strong>{{ $cabang?->nama_cabang ?? '-' }}</strong></div>
                <div class="history-detail-row"><span>User Pengaju</span><strong>{{ $requester?->name ?? '-' }}</strong></div>
                @if($isStockOut)
                    <div class="history-detail-row"><span>Jenis Keluar</span><strong>{{ ($record->jenis ?? null) === 'DO' ? 'DO' : ucfirst($record->jenis ?? '-') }}</strong></div>
                    <div class="history-detail-row"><span>Status</span><strong>{{ ucfirst($record->status ?? '-') }}</strong></div>
                    <div class="history-detail-row"><span>Jumlah</span><strong>{{ $record->jumlah }}</strong></div>
                    @if(($record->jenis ?? null) === 'request')
                        <div class="history-detail-row"><span>Nomor IM</span><strong>{{ $record->nomor_im ?: '-' }}</strong></div>
                    @elseif(($record->jenis ?? null) === 'DO')
                        <div class="history-detail-row"><span>Nomor SPK</span><strong>{{ $record->nomor_spk ?: '-' }}</strong></div>
                        <div class="history-detail-row history-detail-row-wide"><span>Paket Bundling</span><strong>{{ $paketLabel }}</strong></div>
                    @endif
                    <div class="history-detail-row"><span>PIC Penjualan</span><strong>{{ $record->pic_penjualan ?: '-' }}</strong></div>
                    <div class="history-detail-row history-detail-row-wide"><span>Keterangan</span><strong>{{ $record->keterangan ?: '-' }}</strong></div>
                @endif
                @if($isStockInApproval)
                    <div class="history-detail-row"><span>Jumlah Perubahan</span><strong>{{ $record->old_jumlah }} &rarr; {{ $record->new_jumlah }}</strong></div>
                    <div class="history-detail-row"><span>Tipe Request</span><strong>Edit Stok (StockIn #{{ $record->stock_in_id }})</strong></div>
                    <div class="history-detail-row"><span>Sumber</span><strong>{{ $record->old_sumber ?: '-' }} &rarr; {{ $record->new_sumber ?: '-' }}</strong></div>
                    <div class="history-detail-row"><span>Item</span><strong>{{ $record->oldItem?->nama_items ?? '-' }} &rarr; {{ $record->newItem?->nama_items ?? '-' }}</strong></div>
                    <div class="history-detail-row history-detail-row-wide"><span>Keterangan Baru</span><strong>{{ $record->new_keterangan ?: '-' }}</strong></div>
                @elseif($isStockIn)
                    <div class="history-detail-row"><span>Jumlah Masuk</span><strong>+{{ $record->jumlah }}</strong></div>
                    <div class="history-detail-row"><span>Sumber</span><strong>{{ $record->sumber ?: '-' }}</strong></div>
                    <div class="history-detail-row history-detail-row-wide"><span>Keterangan</span><strong>{{ $record->keterangan ?: '-' }}</strong></div>
                    <div class="history-detail-row"><span>Dibuat Pada</span><strong>{{ $formatTanggalWaktu($record->created_at) }}</strong></div>
                @endif
                @if($isStockOut)
                    <div class="history-detail-row"><span>Discount</span><strong>{{ $discountLabel }}</strong></div>
                    <div class="history-detail-row"><span>Total</span><strong>{{ $formatRupiah($record->total) }}</strong></div>
                    <div class="history-detail-row"><span>Jenis Pembayaran</span><strong>{{ $jenisBayarLabel }}</strong></div>
                    <div class="history-detail-row"><span>Kode Pembayaran</span><strong class="history-mono">{{ $record->kode_pembayaran ?: '-' }}</strong></div>
                    <div class="history-detail-row"><span>Bukti Pembayaran</span><strong>@if($record->bukti_pembayaran)<a class="history-payment-link" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($record->bukti_pembayaran) }}" target="_blank" rel="noopener"><i class="fas fa-file-pdf"></i> {{ basename($record->bukti_pembayaran) }}</a>@else - @endif</strong></div>
                @endif
                @if(! $isStockIn)
                    <div class="history-detail-row"><span>Status</span><strong>{{ ucfirst($record->status ?? '-') }}</strong></div>
                    <div class="history-detail-row"><span>Approved By</span><strong>{{ $record->approver?->name ?? '-' }}</strong></div>
                    <div class="history-detail-row history-detail-row-wide"><span>Catatan Approval</span><strong>{{ $record->catatan_approval ?: '-' }}</strong></div>
                @endif
            </div>
        </section>

        @if($isStockOut && ($record->jenis ?? null) === 'penjualan')
            <section class="history-information-panel" aria-labelledby="historyCustomerTitle">
                <div class="history-information-header">
                    <i class="fas fa-user"></i>
                    <h4 id="historyCustomerTitle">Data Customer</h4>
                </div>
                <div class="history-detail-grid">
                    <div class="history-detail-row"><span>Nama Customer</span><strong>{{ $record->nama_customer ?: '-' }}</strong></div>
                    @php($isMemberSale = $record->isTagMemberSale())
                    @if($isMemberSale)
                        <div class="history-detail-row"><span>Jabatan</span><strong>{{ $record->jabatan ?: '-' }}</strong></div>
                    @else
                        <div class="history-detail-row"><span>NIK KTP</span><strong class="history-mono">{{ $record->nik_ktp ?: '-' }}</strong></div>
                    @endif
                    <div class="history-detail-row"><span>No. Telepon</span><strong>{{ $record->nomor_telepon ?: '-' }}</strong></div>
                    <div class="history-detail-row"><span>Jenis Pembayaran</span><strong>{{ $jenisBayarLabel }}</strong></div>
                    <div class="history-detail-row history-detail-row-wide"><span>Alamat</span><strong>{{ $record->alamat_customer ?: '-' }}</strong></div>
                </div>
            </section>
        @endif

        <section class="history-information-panel" aria-labelledby="historyItemTitle">
            <div class="history-information-header">
                <i class="fas fa-box"></i>
                <h4 id="historyItemTitle">Item &amp; Cabang</h4>
            </div>
            <div class="history-detail-grid">
                <div class="history-detail-row"><span>Kode Item</span><strong class="history-mono">{{ $item?->kode_items ?? '-' }}</strong></div>
                <div class="history-detail-row"><span>Nama Item</span><strong>{{ $item?->nama_items ?? '-' }}</strong></div>
                <div class="history-detail-row"><span>Kategori</span><strong>{{ $item?->kategori ?? '-' }}</strong></div>
                <div class="history-detail-row"><span>Nama Cabang</span><strong>{{ $cabang?->nama_cabang ?? '-' }}</strong></div>
                <div class="history-detail-row"><span>Harga Modal</span><strong>{{ $formatRupiah($item?->harga_items ?? null) }}</strong></div>
                <div class="history-detail-row"><span>Harga Jual </span><strong>{{ $formatRupiah($item?->harga_jual ?? null) }}</strong></div>
            </div>
        </section>
        <section class="history-information-panel" aria-labelledby="historyAuditTitle">
            <div class="history-information-header">
                <i class="fas fa-user-check"></i>
                <h4 id="historyAuditTitle">Approval &amp; Audit</h4>
            </div>
            <div class="history-detail-grid">
                @if($isStockInApproval)
                    <div class="history-detail-row"><span>Status Approval</span><strong>{{ ucfirst($record->status ?? '-') }}</strong></div>
                    <div class="history-detail-row"><span>Approved By</span><strong>{{ $record->approver?->name ?? '-' }}</strong></div>
                    <div class="history-detail-row"><span>Approved At</span><strong>{{ $formatTanggalWaktu($record->approved_at) }}</strong></div>
                    <div class="history-detail-row"><span>Diminta Oleh</span><strong>{{ $record->requester?->name ?? '-' }}</strong></div>
                    <div class="history-detail-row history-detail-row-wide"><span>Catatan Approval</span><strong>{{ $record->catatan_approval ?: '-' }}</strong></div>
                @elseif($isStockOut)
                    <div class="history-detail-row"><span>Diinput Oleh</span><strong>{{ $record->user?->name ?? '-' }} ({{ $record->user?->role ?? '-' }})</strong></div>
                    <div class="history-detail-row"><span>Cabang Input</span><strong>{{ $record->user?->cabang?->nama_cabang ?? $cabang?->nama_cabang ?? '-' }}</strong></div>
                @else
                    <div class="history-detail-row"><span>Diinput Oleh</span><strong>{{ $record->user?->name ?? '-' }} ({{ $record->user?->role ?? '-' }})</strong></div>
                    <div class="history-detail-row"><span>Cabang Input</span><strong>{{ $record->user?->cabang?->nama_cabang ?? $cabang?->nama_cabang ?? '-' }}</strong></div>
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
    .history-information-panel + .history-information-panel { margin-top: 1rem; }
    .history-mono { font-family: SFMono-Regular, Menlo, Consolas, monospace; font-size: 0.8rem; letter-spacing: 0.02em; }
    .history-payment-link { color: #7ef0aa; font-weight: 700; text-decoration: none; }
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
