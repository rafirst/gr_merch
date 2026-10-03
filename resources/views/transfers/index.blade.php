@extends('layouts.app')
@section('title', auth()->user()->isAdminHo() ? 'Transfer Barang' : 'Transfer Masuk')

@section('content')
@if(auth()->user()->isAdminHo())
    <div class="card transfer-card">
        <div class="card-header transfer-toolbar">
            <form method="GET" class="transfer-filters">
                <select name="status" class="form-control" aria-label="Filter status">
                    <option value="">Semua Status</option>
                    <option value="proses" {{ request('status') === 'proses' ? 'selected' : '' }}>Proses</option>
                    <option value="diterima" {{ request('status') === 'diterima' ? 'selected' : '' }}>Diterima</option>
                    <option value="batal" {{ request('status') === 'batal' ? 'selected' : '' }}>Batal</option>
                </select>
                <div class="transfer-search-field">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari nama atau kode barang" aria-label="Cari nama atau kode barang">
                </div>
                <button class="btn transfer-filter-button" type="submit" aria-label="Cari transfer" title="Cari transfer"><i class="fas fa-search"></i></button>
                <a class="btn transfer-reset-button" href="{{ route('transfers.index') }}" aria-label="Reset filter" title="Reset filter"><i class="fas fa-undo-alt"></i></a>
            </form>
            <a href="{{ route('transfers.create') }}" class="btn transfer-create-button" title="Buat Transfer"><i class="fas fa-plus"></i><span>Transfer</span></a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 transfer-table">
                    <thead>
                        <tr><th>Tanggal</th><th>Cabang Asal</th><th>Cabang Tujuan</th><th>Barang</th><th>Status</th><th class="text-center">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $transfer)
                            <tr>
                                <td>{{ $transfer->sent_at?->format('d/m/Y') ?? $transfer->created_at->format('d/m/Y') }}</td>
                                <td>{{ $transfer->fromCabang->nama_cabang }}</td>
                                <td>{{ $transfer->toCabang->nama_cabang }}</td>
                                <td>
                                    <div class="transfer-lines">
                                        @foreach($transfer->items as $line)
                                            <span>{{ $line->sourceItem->nama_items }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td><span class="transfer-status transfer-status-{{ $transfer->status }}">{{ ucfirst($transfer->status) }}</span></td>
                                <td class="text-center">
                                    <div class="transfer-actions">
                                        <a href="{{ route('transfers.show', $transfer) }}" class="btn transfer-action-button transfer-view-button" title="Lihat detail" aria-label="Lihat detail transfer"><i class="fas fa-eye"></i></a>
                                        @if($transfer->status === 'proses')
                                            <a href="{{ route('transfers.edit', $transfer) }}" class="btn transfer-action-button transfer-edit-button" title="Edit transfer" aria-label="Edit transfer"><i class="fas fa-pen"></i></a>
                                        @endif
                                        @if($transfer->status !== 'batal')
                                            <form action="{{ route('transfers.destroy', $transfer) }}" method="POST" class="d-inline" onsubmit="return confirm('Batalkan transfer ini? Stok akan disesuaikan dan status menjadi batal.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn transfer-action-button transfer-delete-button" title="Batalkan transfer" aria-label="Batalkan transfer"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4">Belum ada transfer barang.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">{{ $transfers->links() }}</div>
    </div>
@else
    <div class="transfer-staff-page">
        <p class="transfer-staff-subtitle">Kelola transfer barang yang masuk ke cabang.</p>
        <div class="card transfer-card">
            <form method="GET" class="card-header transfer-staff-toolbar">
                <label class="transfer-staff-search">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari transfer..." aria-label="Cari transfer">
                </label>
                <label class="transfer-staff-status">
                    <select name="status" aria-label="Filter status">
                        <option value="" {{ $status === null ? 'selected' : '' }}>Semua Status</option>
                        <option value="proses" {{ $status === 'proses' ? 'selected' : '' }}>Waiting</option>
                        <option value="diterima" {{ $status === 'diterima' ? 'selected' : '' }}>Approved</option>
                    </select>
                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </label>
                <button class="btn transfer-staff-filter-button" type="submit" title="Cari transfer" aria-label="Cari transfer">
                    <i class="fas fa-search" aria-hidden="true"></i>
                </button>
                <a class="btn transfer-staff-reset-button" href="{{ route('transfers.index') }}" title="Reset filter" aria-label="Reset filter">
                    <i class="fas fa-undo-alt" aria-hidden="true"></i>
                </a>
            </form>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 transfer-table transfer-staff-table">
                        <thead><tr><th>No. Transfer</th><th>Tanggal</th><th>Cabang Asal</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
                        <tbody>
                            @forelse($transfers as $transfer)
                                <tr>
                                    <td><strong>TRF-{{ str_pad((string) $transfer->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
                                    <td>{{ $transfer->sent_at?->format('d/m/Y') ?? $transfer->created_at->format('d/m/Y') }}</td>
                                    <td>{{ $transfer->fromCabang->nama_cabang }}</td>
                                    <td>
                                        <span class="transfer-staff-status-label {{ $transfer->status === 'proses' ? 'is-pending' : 'is-received' }}">
                                            {{ $transfer->status === 'proses' ? 'Waiting' : 'Approved' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="transfer-staff-actions">
                                        @if($transfer->status === 'proses')
                                            <button type="button" class="btn transfer-staff-action transfer-staff-receive" data-toggle="modal" data-target="#receiveTransfer{{ $transfer->id }}" title="Terima transfer" aria-label="Terima transfer TRF-{{ str_pad((string) $transfer->id, 5, '0', STR_PAD_LEFT) }}">
                                                <i class="fas fa-check" aria-hidden="true"></i>
                                            </button>
                                        @endif
                                            <a href="{{ route('transfers.show', $transfer) }}" class="btn transfer-staff-action transfer-staff-detail" title="Lihat detail transfer" aria-label="Lihat detail transfer TRF-{{ str_pad((string) $transfer->id, 5, '0', STR_PAD_LEFT) }}">
                                                <i class="far fa-eye" aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4">Tidak ada transfer masuk untuk filter ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">{{ $transfers->links() }}</div>
        </div>
    </div>

    @foreach($transfers->where('status', 'proses') as $transfer)
            <div class="modal fade transfer-receive-modal" id="receiveTransfer{{ $transfer->id }}" tabindex="-1" role="dialog" aria-labelledby="receiveTransferTitle{{ $transfer->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <form action="{{ route('transfers.receive', $transfer) }}" method="POST" data-receive-form>
                            @csrf
                            <div class="modal-header">
                                <div><h2 class="modal-title" id="receiveTransferTitle{{ $transfer->id }}">Terima TRF-{{ str_pad((string) $transfer->id, 5, '0', STR_PAD_LEFT) }}</h2><small>Dari {{ $transfer->fromCabang->nama_cabang }}</small></div>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead><tr><th>Barang</th><th class="text-center">Qty Dikirim</th><th>Qty Diterima</th><th>Catatan Selisih</th></tr></thead>
                                        <tbody>
                                            @foreach($transfer->items as $line)
                                                <tr>
                                                    <td><strong>{{ $line->sourceItem->nama_items }}</strong><small class="d-block text-muted">{{ $line->sourceItem->kode_items }}</small></td>
                                                    <td class="text-center">{{ $line->jumlah_dikirim }}</td>
                                                    <td><input type="number" name="items[{{ $line->id }}][jumlah_diterima]" class="form-control" min="0" max="{{ $line->jumlah_dikirim }}" value="{{ $line->jumlah_dikirim }}" data-received-quantity data-sent="{{ $line->jumlah_dikirim }}" required></td>
                                                    <td><textarea name="items[{{ $line->id }}][catatan_selisih]" class="form-control transfer-discrepancy-note" rows="2" placeholder="Wajib jika jumlah berbeda" data-discrepancy-note></textarea></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <small class="transfer-modal-hint">Jika qty diterima berbeda dari qty dikirim, isi catatan selisih.</small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Konfirmasi Terima</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    @endforeach
@endif
@endsection

@push('styles')
<style>
    .transfer-toolbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; }
    .transfer-filters { display:flex; align-items:center; flex:1 1 auto; gap:.5rem; min-width:0; margin:0; }
    .transfer-filters select { flex:0 0 190px; }
    .transfer-search-field { position:relative; flex:1 1 240px; min-width:200px; }
    .transfer-search-field i { position:absolute; top:50%; left:.7rem; z-index:1; color:rgba(244,247,250,.55); transform:translateY(-50%); }
    .transfer-search-field input { padding-left:2rem; }
    .transfer-filter-button,
    .transfer-reset-button,
    .transfer-create-button { position:relative; display:inline-flex; align-items:center; justify-content:center; overflow:hidden; height:38px; border:1px solid rgba(255,255,255,.3) !important; border-radius:4px; color:#fff !important; box-shadow:inset 0 1px 0 rgba(255,255,255,.58),0 4px 9px rgba(0,0,0,.3); text-shadow:0 1px 1px rgba(0,0,0,.28); transition:filter .18s ease,transform .18s ease,box-shadow .18s ease; }
    .transfer-filter-button,
    .transfer-reset-button { flex:0 0 38px; width:38px; padding:0; background:linear-gradient(145deg,#aeb9c4 0%,#687887 48%,#465563 100%); }
    .transfer-filter-button::before,
    .transfer-reset-button::before,
    .transfer-create-button::before { position:absolute; top:0; right:8%; left:8%; height:48%; border-radius:0 0 50% 50%; background:linear-gradient(180deg,rgba(255,255,255,.58),rgba(255,255,255,0)); content:''; pointer-events:none; }
    .transfer-filter-button i,
    .transfer-reset-button i,
    .transfer-create-button i,
    .transfer-create-button span { position:relative; z-index:1; }
    .transfer-filter-button:hover,
    .transfer-filter-button:focus,
    .transfer-reset-button:hover,
    .transfer-reset-button:focus,
    .transfer-create-button:hover,
    .transfer-create-button:focus { filter:brightness(1.12) saturate(1.08); box-shadow:inset 0 1px 0 rgba(255,255,255,.72),0 6px 13px rgba(0,0,0,.4); transform:translateY(-1px); }
    .transfer-create-button { flex:0 0 auto; gap:.45rem; padding:0 .8rem; border-color:#f20b18 !important; background:linear-gradient(90deg,#f20b18 0%,#b00813 42%,#5b050b 100%) !important; white-space:nowrap; }
    .transfer-table th { white-space:nowrap; }
    .transfer-lines { display:grid; gap:.25rem; min-width:170px; }
    .transfer-lines small { margin-left:.25rem; color:rgba(244,247,250,.62); }
    .transfer-status { display:inline-block; padding:.3rem .58rem; border:1px solid; border-radius:4px; white-space:nowrap; font-size:.75rem; font-weight:700; }
    .transfer-status-pending { border-color:rgba(255,193,7,.55); background:rgba(255,193,7,.12); color:#ffdf76; }
    .transfer-status-received { border-color:rgba(40,167,69,.55); background:rgba(40,167,69,.12); color:#78e69a; }
    .transfer-status-proses { border-color:rgba(255,193,7,.6); background:rgba(255,193,7,.17); color:#ffdf76; }
    .transfer-status-diterima { border-color:rgba(40,167,69,.6); background:rgba(40,167,69,.17); color:#78e69a; }
    .transfer-status-batal { border-color:rgba(220,53,69,.6); background:rgba(220,53,69,.17); color:#ff8793; }
    .transfer-actions { display:inline-flex; align-items:center; justify-content:center; gap:.35rem; }
    .transfer-action-button { position:relative; display:inline-flex; width:34px; height:34px; align-items:center; justify-content:center; overflow:hidden; padding:0; border:1px solid rgba(255,255,255,.32); border-radius:4px; color:#fff !important; box-shadow:inset 0 1px 0 rgba(255,255,255,.58),0 3px 7px rgba(0,0,0,.3); transition:filter .18s ease,transform .18s ease; }
    .transfer-action-button::before { position:absolute; top:0; right:8%; left:8%; height:48%; border-radius:0 0 50% 50%; background:linear-gradient(180deg,rgba(255,255,255,.58),rgba(255,255,255,0)); content:''; pointer-events:none; }
    .transfer-action-button i { position:relative; z-index:1; }
    .transfer-action-button:hover,.transfer-action-button:focus { filter:brightness(1.12); transform:translateY(-1px); }
    .transfer-view-button { background:linear-gradient(145deg,#62dceb 0%,#0fa5bd 48%,#08788f 100%); }
    .transfer-edit-button { background:linear-gradient(145deg,#ffe17a 0%,#e4a800 48%,#a96c00 100%); }
    .transfer-delete-button { background:linear-gradient(145deg,#ff6570 0%,#d3182b 48%,#8d0d1d 100%); }
    .transfer-tabs { display:flex; gap:.35rem; padding:.5rem .75rem 0; }
    .transfer-tab { display:inline-flex; align-items:center; gap:.45rem; padding:.7rem .85rem; border-bottom:2px solid transparent; color:rgba(244,247,250,.68); text-decoration:none !important; }
    .transfer-tab.active { border-bottom-color:#f70008; color:#fff; }
    .transfer-discrepancy { color:#ffcf70 !important; }
    .transfer-receive-modal .modal-content { border:1px solid rgba(105,143,176,.65); border-radius:5px; background:#07111b; color:#f4f7fa; }
    .transfer-receive-modal .modal-header, .transfer-receive-modal .modal-footer { border-color:rgba(255,255,255,.12); }
    .transfer-receive-modal .modal-title { font-size:1.05rem; font-weight:700; }
    .transfer-receive-modal .modal-header small, .transfer-modal-hint { color:rgba(244,247,250,.62); }
    .transfer-discrepancy-note { min-width:150px; }
    .transfer-staff-page { display:grid; gap:.65rem; }
    .transfer-staff-subtitle { margin:0; color:rgba(244,247,250,.7); font-size:.82rem; }
    .transfer-staff-toolbar { display:flex; align-items:center; gap:.55rem; padding:.9rem 1rem; }
    .transfer-staff-search,
    .transfer-staff-status { position:relative; display:flex; height:46px; align-items:center; margin:0; border:1px solid rgba(105,143,176,.48); border-radius:6px; background:rgba(4,14,23,.76); color:#f4f7fa; }
    .transfer-staff-search { flex:1 1 auto; min-width:0; }
    .transfer-staff-search > i { flex:0 0 auto; margin:0 .8rem 0 1rem; color:rgba(244,247,250,.68); }
    .transfer-staff-search input { width:100%; height:100%; min-width:0; padding:0 .8rem 0 0; border:0; outline:0; background:transparent; color:#f4f7fa; }
    .transfer-staff-search input::placeholder { color:rgba(244,247,250,.64); }
    .transfer-staff-status { flex:0 0 242px; gap:.65rem; padding:0 .85rem; }
    .transfer-staff-status > i:first-child { color:rgba(244,247,250,.8); }
    .transfer-staff-status select { width:100%; height:100%; appearance:none; border:0; outline:0; background:transparent; color:#f4f7fa; }
    .transfer-staff-status select option { background:#07111b; color:#f4f7fa; }
    .transfer-staff-status > i:last-child { color:rgba(244,247,250,.65); font-size:.7rem; pointer-events:none; }
    .transfer-staff-table { min-width:620px; }
    .transfer-staff-table thead th { padding:.9rem 1rem; border-bottom:1px solid rgba(105,143,176,.32); background:rgba(12,27,39,.78); color:rgba(244,247,250,.86); font-size:.76rem; }
    .transfer-staff-table tbody td { padding:.85rem 1rem; border-top:1px solid rgba(105,143,176,.2); vertical-align:middle; }
    .transfer-staff-table tbody tr:hover { background:rgba(28,49,64,.42); }
    .transfer-staff-status-label { display:inline-flex; align-items:center; padding:.3rem .58rem; border:1px solid; border-radius:4px; font-size:.75rem; font-weight:700; white-space:nowrap; }
    .transfer-staff-status-label.is-pending { border-color:rgba(255,193,7,.6); background:rgba(255,193,7,.17); color:#ffdf76; }
    .transfer-staff-status-label.is-received { border-color:rgba(40,167,69,.6); background:rgba(40,167,69,.17); color:#78e69a; }
    .transfer-staff-filter-button,
    .transfer-staff-reset-button { position:relative; display:inline-flex; width:40px; height:40px; flex:0 0 40px; align-items:center; justify-content:center; overflow:hidden; padding:0; border:1px solid rgba(255,255,255,.3) !important; border-radius:4px; background:linear-gradient(145deg,#aeb9c4 0%,#687887 48%,#465563 100%); color:#fff !important; box-shadow:inset 0 1px 0 rgba(255,255,255,.58),0 4px 9px rgba(0,0,0,.3); text-shadow:0 1px 1px rgba(0,0,0,.28); transition:filter .18s ease,transform .18s ease,box-shadow .18s ease; }
    .transfer-staff-filter-button::before,
    .transfer-staff-reset-button::before,
    .transfer-staff-action::before { position:absolute; top:0; right:8%; left:8%; height:48%; border-radius:0 0 50% 50%; background:linear-gradient(180deg,rgba(255,255,255,.58),rgba(255,255,255,0)); content:''; pointer-events:none; }
    .transfer-staff-filter-button i,
    .transfer-staff-reset-button i,
    .transfer-staff-action i { position:relative; z-index:1; }
    .transfer-staff-filter-button:hover,
    .transfer-staff-filter-button:focus,
    .transfer-staff-reset-button:hover,
    .transfer-staff-reset-button:focus { filter:brightness(1.12) saturate(1.08); box-shadow:inset 0 1px 0 rgba(255,255,255,.72),0 6px 13px rgba(0,0,0,.4); transform:translateY(-1px); }
    .transfer-staff-actions { display:inline-flex; align-items:center; justify-content:center; gap:.45rem; }
    .transfer-staff-action { position:relative; display:inline-flex; width:39px; min-width:39px; height:39px; align-items:center; justify-content:center; overflow:hidden; padding:0; border:1px solid rgba(255,255,255,.24); border-radius:5px; color:#fff !important; font-size:.9rem; box-shadow:inset 0 1px 0 rgba(255,255,255,.42),0 4px 9px rgba(0,0,0,.28); transition:filter .18s ease,transform .18s ease; }
    .transfer-staff-action:hover,.transfer-staff-action:focus { filter:brightness(1.12); color:#fff; transform:translateY(-1px); }
    .transfer-staff-receive { border-color:rgba(86,224,142,.55); background:linear-gradient(145deg,#55d889 0%,#18a957 48%,#087c3b 100%); box-shadow:0 4px 12px rgba(24,169,87,.24); }
    .transfer-staff-detail { border-color:rgba(95,223,242,.48); background:linear-gradient(145deg,#62dceb 0%,#0fa5bd 48%,#08788f 100%); box-shadow:0 4px 12px rgba(15,165,189,.2); }
    @media (max-width:767.98px) {
        .transfer-toolbar { flex-wrap:wrap; }
        .transfer-filters { width:100%; flex-wrap:wrap; }
        .transfer-filters select, .transfer-search-field { min-width:0; flex:1 1 160px; }
        .transfer-create-button { margin-left:auto; }
        .transfer-staff-toolbar { flex-wrap:wrap; }
        .transfer-staff-search { flex:1 1 100%; height:44px; }
        .transfer-staff-status { flex:1 1 0; min-width:0; height:40px; }
        .transfer-staff-table { min-width:570px; }
    }
    @media(max-width:575.98px) {
        .transfer-tabs { flex-wrap:wrap; }
        .transfer-tab { flex:1 1 100%; }
        .transfer-receive-modal .modal-body { padding:.75rem; }
    }
</style>
@endpush

@push('scripts')
@if(!auth()->user()->isAdminHo())
    <script>
        document.querySelectorAll('[data-receive-form]').forEach((form) => {
            const quantities = form.querySelectorAll('[data-received-quantity]');

            function syncDiscrepancyRequirement(quantity) {
                const note = quantity.closest('tr').querySelector('[data-discrepancy-note]');
                note.required = Number(quantity.value) !== Number(quantity.dataset.sent);
            }

            quantities.forEach((quantity) => {
                syncDiscrepancyRequirement(quantity);
                quantity.addEventListener('input', () => syncDiscrepancyRequirement(quantity));
            });
        });
    </script>
@endif
@endpush