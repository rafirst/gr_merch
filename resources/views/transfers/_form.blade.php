@php
    $formItems = old('items', $isEditing
        ? $transfer->items->map(fn ($line): array => [
            'item_id' => $line->source_item_id,
            'jumlah_dikirim' => $line->jumlah_dikirim,
        ])->all()
        : [['item_id' => '', 'jumlah_dikirim' => '']]);
    $sourceBranchId = old('from_cabang_id', $isEditing ? $transfer->from_cabang_id : '');
    $destinationBranchId = old('to_cabang_id', $isEditing ? $transfer->to_cabang_id : '');
@endphp

<form action="{{ $isEditing ? route('transfers.update', $transfer) : route('transfers.store') }}" method="POST" enctype="multipart/form-data" id="transferForm" class="transfer-create-layout">
    @csrf
    @if($isEditing)
        @method('PUT')
    @endif
    <main class="transfer-create-main">
        <section class="transfer-create-section transfer-route-section">
            <div class="transfer-section-heading">
                <span class="transfer-section-number">01</span>
                <div><h2>Rute Transfer</h2><p>{{ $isEditing ? 'Perbarui cabang asal dan tujuan transfer.' : 'Tentukan cabang mana yang mengirim dan menerima.' }}</p></div>
            </div>
            <div class="transfer-route-fields">
                <div class="form-group">
                    <label for="fromCabang"><i class="fas fa-map-marker-alt"></i> Cabang Asal</label>
                    <select id="fromCabang" name="from_cabang_id" class="form-control" required>
                        <option value="">Pilih cabang asal</option>
                        @foreach($cabangs as $cabang)
                            <option value="{{ $cabang->id }}" {{ (string) $sourceBranchId === (string) $cabang->id ? 'selected' : '' }}>{{ $cabang->nama_cabang }}</option>
                        @endforeach
                    </select>
                </div>
                <span class="transfer-route-arrow" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                <div class="form-group">
                    <label for="toCabang"><i class="fas fa-map-marker-alt"></i> Cabang Tujuan</label>
                    <select id="toCabang" name="to_cabang_id" class="form-control" required>
                        <option value="">Pilih cabang tujuan</option>
                        @foreach($cabangs as $cabang)
                            <option value="{{ $cabang->id }}" {{ (string) $destinationBranchId === (string) $cabang->id ? 'selected' : '' }}>{{ $cabang->nama_cabang }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <small class="transfer-route-hint" id="transferRouteHint"><i class="fas fa-info-circle"></i> Cabang asal dan tujuan harus berbeda.</small>
        </section>

        <section class="transfer-create-section transfer-items-section">
            <div class="transfer-section-heading transfer-items-heading">
                <span class="transfer-section-number">02</span>
                <div><h2>Barang yang Dikirim</h2><p>{{ $isEditing ? 'Perubahan stok dihitung ulang dari daftar barang ini.' : 'Pilih item dan jumlah yang akan dipindahkan.' }}</p></div>
                <button type="button" class="btn transfer-add-button" id="addTransferItem" title="Tambah barang"><i class="fas fa-plus"></i><span>Tambah Barang</span></button>
            </div>

            <div class="transfer-item-columns" aria-hidden="true"><span>Item</span><span>Stok Tersedia</span><span>Qty Transfer</span><span></span></div>
            <div id="transferItems" class="transfer-item-list">
                @foreach($formItems as $index => $formItem)
                    <div class="transfer-item-row" data-transfer-item-row>
                        <div class="transfer-item-identity">
                            <div class="transfer-item-photo" data-item-photo><i class="fas fa-box"></i></div>
                            <div class="transfer-item-select-wrap">
                                <select name="items[{{ $index }}][item_id]" class="form-control transfer-item-select" required aria-label="Pilih item">
                                    <option value="">Pilih item</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" data-cabang="{{ $item->cabang_id }}" data-nama="{{ $item->nama_items }}" data-stok="{{ (int) $item->stok_items + (int) $stockAdjustments->get($item->id, 0) }}" data-foto="{{ $item->foto ? url('storage/'.ltrim($item->foto, '/')) : '' }}" {{ (string) ($formItem['item_id'] ?? '') === (string) $item->id ? 'selected' : '' }}>{{ $item->kode_items }} - {{ $item->nama_items }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="transfer-stock-cell"><strong data-item-stock>-</strong><small>pcs</small></div>
                        <div class="transfer-quantity-cell"><input type="number" name="items[{{ $index }}][jumlah_dikirim]" class="form-control transfer-quantity-input" min="1" step="1" value="{{ $formItem['jumlah_dikirim'] ?? '' }}" required aria-label="Qty transfer"></div>
                        <button type="button" class="btn transfer-remove-button" aria-label="Hapus item" title="Hapus item"><i class="fas fa-trash"></i></button>
                    </div>
                @endforeach
            </div>

            @if($errors->any())
                <div class="alert alert-danger mt-3"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <div class="transfer-items-totals">
                <div><i class="fas fa-boxes"></i><span>Total jenis barang</span><strong data-total-items>0 item</strong></div>
                <div><i class="fas fa-dolly"></i><span>Total kuantitas</span><strong data-total-quantity>0 pcs</strong></div>
            </div>
        </section>

        <section class="transfer-create-section transfer-proof-section">
            <div class="transfer-section-heading">
                <span class="transfer-section-number">03</span>
                <div><h2>Upload Foto Bukti Transfer</h2><p>Tambahkan foto barang sebelum dikirim sebagai dokumentasi.</p></div>
            </div>
            <label for="transferProofInput" class="transfer-proof-dropzone" data-proof-dropzone>
                <i class="fas fa-cloud-upload-alt" aria-hidden="true"></i>
                <strong>Klik untuk upload atau seret foto ke sini</strong>
                <span>PNG, JPG, atau WEBP. Maksimal 5 foto, 5 MB per foto.</span>
                <input type="file" id="transferProofInput" name="bukti_foto[]" accept="image/jpeg,image/png,image/webp" multiple class="sr-only">
            </label>
            <div class="transfer-proof-toolbar"><strong>Preview Foto</strong><span data-proof-count>0/5 foto</span></div>
            <div class="transfer-proof-previews" id="transferProofPreviews">
                @if($isEditing)
                    @foreach($transfer->bukti_foto ?? [] as $proofPath)
                        <figure class="transfer-proof-preview transfer-proof-existing">
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($proofPath) }}" target="_blank" rel="noopener">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($proofPath) }}" alt="Bukti transfer {{ $loop->iteration }}">
                            </a>
                            <figcaption><label><input type="checkbox" name="hapus_bukti_foto[]" value="{{ $proofPath }}" data-proof-remove {{ in_array($proofPath, old('hapus_bukti_foto', []), true) ? 'checked' : '' }}> Hapus foto</label></figcaption>
                        </figure>
                    @endforeach
                @endif
            </div>
            <small class="transfer-proof-feedback" data-proof-feedback aria-live="polite"></small>
        </section>
    </main>

    <aside class="transfer-create-summary">
        <div class="transfer-summary-heading"><span class="transfer-section-number">04</span><div><h2>Ringkasan</h2><p>Periksa informasi transfer.</p></div></div>
        <div class="transfer-summary-route">
            <div class="transfer-summary-branch"><span class="transfer-summary-icon"><i class="fas fa-warehouse"></i></span><div><small>Dari</small><strong>Cabang Asal</strong><span data-summary-from>-</span></div></div>
            <i class="fas fa-arrow-down transfer-summary-direction" aria-hidden="true"></i>
            <div class="transfer-summary-branch"><span class="transfer-summary-icon"><i class="fas fa-warehouse"></i></span><div><small>Ke</small><strong>Cabang Tujuan</strong><span data-summary-to>-</span></div></div>
        </div>
        <div class="transfer-summary-stat"><i class="fas fa-boxes"></i><div><small>Total Item</small><strong data-summary-items>0 item</strong></div></div>
        <div class="transfer-summary-stat"><i class="fas fa-dolly"></i><div><small>Total Qty Transfer</small><strong data-summary-quantity>0 pcs</strong></div></div>
        {{-- Temporarily hidden; keep this block for later reactivation.
        <div class="transfer-summary-inventory">
            <strong>Stok Semua Cabang</strong>
            <div data-all-branch-stock><small>Pilih barang untuk melihat stok cabang.</small></div>
        </div>
        --}}
        <div class="transfer-summary-status"><span class="transfer-summary-status-icon"><i class="fas fa-check"></i></span><div><small>Status</small><strong data-summary-status>Lengkapi transfer</strong></div></div>
        <div class="transfer-create-actions">
            <a href="{{ $isEditing ? route('transfers.index', $transfer) : route('transfers.index') }}" class="btn transfer-cancel-button">Batal</a>
            <button type="submit" class="btn transfer-submit-button"><i class="fas {{ $isEditing ? 'fa-save' : 'fa-exchange-alt' }}"></i> {{ $isEditing ? 'Simpan Perubahan' : 'Buat Transfer' }}</button>
        </div>
    </aside>
</form>

<template id="transferItemRowTemplate">
    <div class="transfer-item-row" data-transfer-item-row>
        <div class="transfer-item-identity">
            <div class="transfer-item-photo" data-item-photo><i class="fas fa-box"></i></div>
            <div class="transfer-item-select-wrap">
                <select name="items[__INDEX__][item_id]" class="form-control transfer-item-select" required aria-label="Pilih item">
                    <option value="">Pilih item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" data-cabang="{{ $item->cabang_id }}" data-nama="{{ $item->nama_items }}" data-stok="{{ (int) $item->stok_items + (int) $stockAdjustments->get($item->id, 0) }}" data-foto="{{ $item->foto ? url('storage/'.ltrim($item->foto, '/')) : '' }}">{{ $item->kode_items }} - {{ $item->nama_items }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="transfer-stock-cell"><strong data-item-stock>-</strong><small>pcs</small></div>
        <div class="transfer-quantity-cell"><input type="number" name="items[__INDEX__][jumlah_dikirim]" class="form-control transfer-quantity-input" min="1" step="1" required aria-label="Qty transfer"></div>
        <button type="button" class="btn transfer-remove-button" aria-label="Hapus item" title="Hapus item"><i class="fas fa-trash"></i></button>
    </div>
</template>

@push('styles')
<style>
    .transfer-create-layout { display:grid; grid-template-columns:minmax(0,1.8fr) minmax(245px,.95fr); gap:.75rem; align-items:start; }
    .transfer-create-main, .transfer-create-summary { min-width:0; overflow:hidden; border:1px solid rgba(105,143,176,.38); border-radius:5px; background:rgba(3,11,18,.84); }
    .transfer-create-section { padding:.9rem; }
    .transfer-create-section + .transfer-create-section { border-top:1px solid rgba(255,255,255,.1); }
    .transfer-section-heading, .transfer-summary-heading { display:flex; align-items:center; gap:.65rem; min-width:0; }
    .transfer-section-number { display:inline-flex; width:27px; height:27px; flex:0 0 27px; align-items:center; justify-content:center; border-radius:50%; background:#f20b18; color:#fff; font-size:.78rem; font-weight:700; }
    .transfer-section-heading h2, .transfer-summary-heading h2 { margin:0; color:#fff; font-size:.95rem; font-weight:700; }
    .transfer-section-heading p, .transfer-summary-heading p { margin:.16rem 0 0; color:rgba(244,247,250,.58); font-size:.72rem; }
    .transfer-route-fields { display:grid; grid-template-columns:minmax(0,1fr) 28px minmax(0,1fr); align-items:end; gap:.5rem; margin-top:.85rem; }
    .transfer-route-fields .form-group { min-width:0; margin:0; }
    .transfer-route-fields label { display:block; margin-bottom:.3rem; color:rgba(244,247,250,.76); font-size:.74rem; }
    .transfer-route-fields label i { margin-right:.25rem; color:#b7c3ce; }
    .transfer-route-fields .form-control { height:34px; padding:.35rem .5rem; font-size:.75rem; }
    .transfer-route-arrow { display:flex; height:34px; align-items:center; justify-content:center; color:#c8d2dc; }
    .transfer-route-hint { display:block; margin-top:.55rem; color:rgba(244,247,250,.52); font-size:.68rem; }
    .transfer-route-hint i { margin-right:.2rem; }
    .transfer-route-hint.is-invalid { color:#ff8793; }
    .transfer-items-heading { margin-bottom:.85rem; }
    .transfer-add-button { display:inline-flex; align-items:center; gap:.35rem; margin-left:auto; padding:.35rem .55rem; border:1px solid rgba(180,196,210,.45); border-radius:4px; color:#fff; font-size:.72rem; white-space:nowrap; }
    .transfer-add-button:hover { border-color:#f70008; background:rgba(247,0,8,.13); color:#fff; }
    .transfer-item-columns, .transfer-item-row { display:grid; grid-template-columns:minmax(0,1fr) 74px 84px 28px; align-items:center; gap:.45rem; }
    .transfer-item-columns { padding:0 .35rem .35rem; color:rgba(244,247,250,.48); font-size:.58rem; text-transform:uppercase; }
    .transfer-item-columns span:nth-child(2), .transfer-item-columns span:nth-child(3) { text-align:center; }
    .transfer-item-list { display:grid; gap:.35rem; }
    .transfer-item-row { min-height:49px; padding:.35rem; border:1px solid rgba(105,143,176,.2); border-radius:4px; background:rgba(255,255,255,.025); }
    .transfer-item-identity { display:flex; align-items:center; gap:.5rem; min-width:0; }
    .transfer-item-photo { display:flex; width:36px; height:36px; flex:0 0 36px; align-items:center; justify-content:center; overflow:hidden; border:1px solid rgba(105,143,176,.28); border-radius:4px; background:rgba(0,0,0,.3); color:rgba(244,247,250,.48); }
    .transfer-item-photo img { width:100%; height:100%; object-fit:contain; }
    .transfer-item-select-wrap { min-width:0; flex:1 1 auto; }
    .transfer-item-select { height:27px; padding:.15rem 1.2rem .15rem .35rem; border:0; background-color:transparent !important; color:#f4f7fa; font-size:.72rem; font-weight:700; }
    .transfer-item-select option { background:#07111b; color:#f4f7fa; }
    .transfer-stock-cell { display:flex; align-items:baseline; justify-content:center; gap:.18rem; color:rgba(244,247,250,.78); font-size:.7rem; }
    .transfer-stock-cell small { color:rgba(244,247,250,.45); font-size:.58rem; }
    .transfer-quantity-input { height:29px; padding:.2rem .3rem; text-align:center; font-size:.72rem; }
    .transfer-remove-button { display:inline-flex; width:27px; height:27px; align-items:center; justify-content:center; padding:0; border:1px solid rgba(247,0,8,.45); border-radius:4px; background:rgba(247,0,8,.16); color:#ff777d; font-size:.68rem; }
    .transfer-remove-button:hover { background:#c51220; color:#fff; }
    .transfer-items-totals { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; margin-top:.65rem; padding:.6rem .5rem 0; border-top:1px solid rgba(255,255,255,.08); }
    .transfer-items-totals > div { display:grid; grid-template-columns:24px 1fr; align-items:center; column-gap:.35rem; }
    .transfer-items-totals i { grid-row:span 2; color:#a9bac9; font-size:.82rem; }
    .transfer-items-totals span, .transfer-items-totals strong { font-size:.66rem; }
    .transfer-items-totals span { color:rgba(244,247,250,.52); }
    .transfer-items-totals strong { color:#fff; }
    .transfer-proof-section { border-top:1px solid rgba(255,255,255,.1); }
    .transfer-proof-dropzone { position:relative; display:flex; min-height:118px; align-items:center; justify-content:center; flex-direction:column; gap:.35rem; margin:.85rem 0 .65rem; padding:1rem; border:1px dashed rgba(105,143,176,.55); border-radius:5px; background:rgba(0,0,0,.16); color:#fff; text-align:center; cursor:pointer; transition:border-color .18s ease,background-color .18s ease; }
    .transfer-proof-dropzone:hover, .transfer-proof-dropzone.is-dragging { border-color:#f20b18; background:rgba(247,0,8,.08); }
    .transfer-proof-dropzone > i { color:#b8c9d8; font-size:1.35rem; }
    .transfer-proof-dropzone > strong { font-size:.78rem; }
    .transfer-proof-dropzone > span { color:rgba(244,247,250,.52); font-size:.66rem; }
    .transfer-proof-toolbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:.4rem; }
    .transfer-proof-toolbar strong { color:rgba(244,247,250,.75); font-size:.7rem; }
    .transfer-proof-toolbar span, .transfer-proof-feedback { color:rgba(244,247,250,.52); font-size:.64rem; }
    .transfer-proof-previews { display:grid; grid-template-columns:repeat(auto-fill,minmax(105px,1fr)); gap:.5rem; }
    .transfer-proof-preview { min-width:0; margin:0; overflow:hidden; border:1px solid rgba(105,143,176,.3); border-radius:4px; background:rgba(0,0,0,.22); }
    .transfer-proof-preview a { display:block; height:82px; }
    .transfer-proof-preview img { display:block; width:100%; height:100%; object-fit:cover; }
    .transfer-proof-preview > img { height:82px; }
    .transfer-proof-preview figcaption { padding:.35rem .4rem; color:rgba(244,247,250,.65); font-size:.62rem; }
    .transfer-proof-preview figcaption label { display:flex; align-items:center; gap:.3rem; margin:0; cursor:pointer; }
    .transfer-proof-preview figcaption input { accent-color:#f20b18; }
    .transfer-proof-caption { display:flex; align-items:center; justify-content:space-between; gap:.25rem; }
    .transfer-proof-caption > span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .transfer-proof-remove-selected { display:inline-flex; width:20px; height:20px; flex:0 0 20px; align-items:center; justify-content:center; padding:0; border:0; border-radius:3px; background:rgba(247,0,8,.18); color:#ff8793; font-size:.65rem; }
    .transfer-proof-remove-selected:hover { background:#c51220; color:#fff; }
    .transfer-proof-feedback:not(:empty) { display:block; margin-top:.35rem; color:#ffcf70; }
    .transfer-create-summary { position:sticky; top:70px; padding:.9rem; }
    .transfer-summary-route { margin:.8rem 0; padding:.65rem; border:1px solid rgba(105,143,176,.24); border-radius:4px; background:rgba(0,0,0,.16); }
    .transfer-summary-branch { display:flex; align-items:center; gap:.55rem; min-width:0; }
    .transfer-summary-icon { display:flex; width:30px; height:30px; flex:0 0 30px; align-items:center; justify-content:center; border:1px solid rgba(105,143,176,.28); border-radius:50%; color:#b9c9d7; font-size:.75rem; }
    .transfer-summary-branch > div { display:grid; min-width:0; gap:.1rem; }
    .transfer-summary-branch small, .transfer-summary-stat small, .transfer-summary-status small { color:rgba(244,247,250,.5); font-size:.61rem; }
    .transfer-summary-branch strong { color:rgba(244,247,250,.75); font-size:.7rem; }
    .transfer-summary-branch span:last-child { overflow:hidden; color:#fff; font-size:.69rem; text-overflow:ellipsis; white-space:nowrap; }
    .transfer-summary-direction { display:block; margin:.35rem 0 .35rem 10px; color:#c5d0da; font-size:.7rem; }
    .transfer-summary-stat { display:flex; align-items:center; gap:.6rem; padding:.55rem .65rem; border:1px solid rgba(105,143,176,.2); border-radius:4px; }
    .transfer-summary-stat + .transfer-summary-stat { margin-top:.4rem; }
    .transfer-summary-stat > i { width:16px; color:#b9c9d7; font-size:.75rem; text-align:center; }
    .transfer-summary-stat div, .transfer-summary-status div { display:grid; gap:.1rem; }
    .transfer-summary-stat strong { color:#fff; font-size:.72rem; }
    .transfer-summary-inventory { margin-top:.4rem; padding:.55rem .65rem; border:1px solid rgba(105,143,176,.2); border-radius:4px; }
    .transfer-summary-inventory > strong { display:block; margin-bottom:.35rem; color:rgba(244,247,250,.7); font-size:.65rem; }
    .transfer-summary-inventory > div { display:flex; flex-wrap:wrap; gap:.25rem; }
    .transfer-summary-inventory small { color:rgba(244,247,250,.48); font-size:.61rem; }
    .transfer-item-stock-group { display:grid; flex:1 1 100%; gap:.25rem; }
    .transfer-item-stock-group > strong { overflow:hidden; color:#fff; font-size:.62rem; text-overflow:ellipsis; white-space:nowrap; }
    .transfer-item-stock-group > div { display:flex; flex-wrap:wrap; gap:.25rem; }
    .transfer-branch-stock { padding:.16rem .3rem; border:1px solid rgba(105,143,176,.18); border-radius:3px; color:rgba(244,247,250,.66); font-size:.58rem; }
    .transfer-branch-stock strong { color:#fff; }
    .transfer-summary-status { display:flex; align-items:center; gap:.55rem; margin-top:.45rem; padding:.55rem .65rem; border:1px solid rgba(40,167,69,.25); border-radius:4px; }
    .transfer-summary-status-icon { display:flex; width:22px; height:22px; flex:0 0 22px; align-items:center; justify-content:center; border-radius:50%; background:#22b879; color:#fff; font-size:.65rem; }
    .transfer-summary-status strong { color:#61e3a7; font-size:.69rem; }
    .transfer-summary-status.is-incomplete { border-color:rgba(255,193,7,.28); }
    .transfer-summary-status.is-incomplete .transfer-summary-status-icon { background:#b98313; }
    .transfer-summary-status.is-incomplete strong { color:#ffcf70; }
    .transfer-create-actions { display:flex; justify-content:flex-end; gap:.4rem; margin-top:1rem; padding-top:.7rem; border-top:1px solid rgba(255,255,255,.1); }
    .transfer-cancel-button, .transfer-submit-button { min-height:34px; padding:.4rem .65rem; border-radius:4px; font-size:.7rem; }
    .transfer-cancel-button { border:1px solid rgba(174,185,196,.42); color:#d1d8df; }
    .transfer-submit-button { border:1px solid #f20b18; background:linear-gradient(90deg,#f20b18,#a90813); color:#fff; }
    .transfer-cancel-button:hover { background:rgba(174,185,196,.12); color:#fff; }
    .transfer-submit-button:hover { filter:brightness(1.12); color:#fff; }
    @media(max-width:991.98px) {
        .transfer-create-layout { grid-template-columns:minmax(0,1fr); }
        .transfer-create-summary { position:static; }
    }
    @media(max-width:575.98px) {
        .transfer-create-section, .transfer-create-summary { padding:.7rem; }
        .transfer-route-fields { grid-template-columns:minmax(0,1fr) 22px minmax(0,1fr); gap:.25rem; }
        .transfer-route-fields label { font-size:.66rem; }
        .transfer-route-fields .form-control { font-size:.67rem; }
        .transfer-item-columns, .transfer-item-row { grid-template-columns:minmax(0,1fr) 54px 66px 25px; gap:.25rem; }
        .transfer-item-photo { width:30px; height:30px; flex-basis:30px; }
        .transfer-item-identity { gap:.25rem; }
        .transfer-item-select { font-size:.64rem; }
        .transfer-stock-cell { font-size:.62rem; }
        .transfer-quantity-input { font-size:.64rem; }
        .transfer-items-totals { gap:.25rem; padding-right:0; padding-left:0; }
        .transfer-add-button { padding:.3rem .4rem; font-size:.63rem; }
        .transfer-proof-previews { grid-template-columns:repeat(auto-fill,minmax(76px,1fr)); }
    }
</style>
@endpush

@push('scripts')
<script>
    const isEditingTransfer = @json($isEditing);
    {{-- const stockSummaries = @json($itemStockSummaries); --}}
    const itemRows = document.getElementById('transferItems');
    const fromCabang = document.getElementById('fromCabang');
    const toCabang = document.getElementById('toCabang');
    const rowTemplate = document.getElementById('transferItemRowTemplate');
    const totalItems = document.querySelector('[data-total-items]');
    const totalQuantity = document.querySelector('[data-total-quantity]');
    {{-- const allBranchStock = document.querySelector('[data-all-branch-stock]'); --}}
    const summaryItems = document.querySelector('[data-summary-items]');
    const summaryQuantity = document.querySelector('[data-summary-quantity]');
    const summaryFrom = document.querySelector('[data-summary-from]');
    const summaryTo = document.querySelector('[data-summary-to]');
    const summaryStatus = document.querySelector('.transfer-summary-status');
    const summaryStatusText = document.querySelector('[data-summary-status]');
    const routeHint = document.getElementById('transferRouteHint');
    const proofInput = document.getElementById('transferProofInput');
    const proofDropzone = document.querySelector('[data-proof-dropzone]');
    const proofPreviews = document.getElementById('transferProofPreviews');
    const proofCount = document.querySelector('[data-proof-count]');
    const proofFeedback = document.querySelector('[data-proof-feedback]');
    let selectedProofPhotos = [];
    let proofPreviewUrls = [];
    let nextItemIndex = itemRows.querySelectorAll('[data-transfer-item-row]').length;

    function updateRow(row) {
        const select = row.querySelector('.transfer-item-select');
        const quantityInput = row.querySelector('.transfer-quantity-input');
        const stockLabel = row.querySelector('[data-item-stock]');
        const photoContainer = row.querySelector('[data-item-photo]');
        const sourceId = fromCabang.value;

        Array.from(select.options).forEach((option) => {
            option.hidden = option.value !== '' && sourceId !== '' && option.dataset.cabang !== sourceId;
        });

        const selected = select.selectedOptions[0];
        if (selected && selected.value && selected.dataset.cabang !== sourceId) {
            select.value = '';
        }

        const chosen = select.selectedOptions[0];
        const hasItem = Boolean(chosen?.value);
        const stock = hasItem ? Number(chosen.dataset.stok) : 0;
        stockLabel.textContent = hasItem ? stock : '-';
        quantityInput.max = hasItem ? stock : '';
        quantityInput.disabled = !hasItem;
        quantityInput.setCustomValidity(Number(quantityInput.value) > stock ? 'Qty melebihi stok tersedia.' : '');
        photoContainer.replaceChildren();
        if (hasItem && chosen.dataset.foto) {
            const photo = document.createElement('img');
            photo.src = chosen.dataset.foto;
            photo.alt = chosen.dataset.nama;
            photoContainer.append(photo);
        } else {
            const icon = document.createElement('i');
            icon.className = 'fas fa-box';
            photoContainer.append(icon);
        }
    }

    function updateBranchSelection() {
        const sameBranch = fromCabang.value !== '' && fromCabang.value === toCabang.value;
        fromCabang.setCustomValidity(sameBranch ? 'Cabang asal dan tujuan harus berbeda.' : '');
        toCabang.setCustomValidity(sameBranch ? 'Cabang asal dan tujuan harus berbeda.' : '');
        routeHint.classList.toggle('is-invalid', sameBranch);
        routeHint.innerHTML = sameBranch
            ? '<i class="fas fa-exclamation-circle"></i> Cabang asal dan tujuan harus berbeda.'
            : '<i class="fas fa-info-circle"></i> Cabang asal dan tujuan harus berbeda.';
        itemRows.querySelectorAll('[data-transfer-item-row]').forEach(updateRow);
        updateSummary();
    }

    function updateSummary() {
        const selectedRows = Array.from(itemRows.querySelectorAll('[data-transfer-item-row]'))
            .filter((row) => row.querySelector('.transfer-item-select').value !== '');
        const quantity = selectedRows.reduce((total, row) => total + (Number(row.querySelector('.transfer-quantity-input').value) || 0), 0);
        const hasInvalidQuantity = selectedRows.some((row) => {
            const input = row.querySelector('.transfer-quantity-input');
            const stock = Number(row.querySelector('.transfer-item-select').selectedOptions[0]?.dataset.stok ?? 0);

            return Number(input.value) < 1 || Number(input.value) > stock;
        });
        const sourceName = fromCabang.selectedOptions[0]?.value ? fromCabang.selectedOptions[0].textContent.trim() : '-';
        const destinationName = toCabang.selectedOptions[0]?.value ? toCabang.selectedOptions[0].textContent.trim() : '-';
        const isReady = Boolean(fromCabang.value && toCabang.value && !fromCabang.validity.customError && selectedRows.length && quantity > 0 && !hasInvalidQuantity);

        summaryFrom.textContent = sourceName;
        summaryTo.textContent = destinationName;
        totalItems.textContent = `${selectedRows.length} item`;
        totalQuantity.textContent = `${quantity} pcs`;
        summaryItems.textContent = `${selectedRows.length} item`;
        summaryQuantity.textContent = `${quantity} pcs`;
        summaryStatusText.textContent = isReady ? (isEditingTransfer ? 'Siap diperbarui' : 'Siap transfer') : 'Lengkapi transfer';
        summaryStatus.classList.toggle('is-incomplete', !isReady);

        /* Temporarily disabled with the all-branch stock panel.
        allBranchStock.replaceChildren();
        if (!selectedRows.length) {
            const emptyState = document.createElement('small');
            emptyState.textContent = 'Pilih barang untuk melihat stok cabang.';
            allBranchStock.append(emptyState);
            return;
        }

        selectedRows.forEach((row) => {
            const selectedItem = row.querySelector('.transfer-item-select').selectedOptions[0];
            const itemSummary = stockSummaries.find((entry) => entry.code === selectedItem.dataset.kode);
            if (!itemSummary) {
                return;
            }

            const itemStockGroup = document.createElement('div');
            itemStockGroup.className = 'transfer-item-stock-group';
            const itemName = document.createElement('strong');
            itemName.textContent = itemSummary.name;
            itemStockGroup.append(itemName);

            const branchStocks = document.createElement('div');
            itemSummary.branches.forEach((branch) => {
                const branchStock = document.createElement('span');
                branchStock.className = 'transfer-branch-stock';
                branchStock.textContent = `${branch.name}: `;
                const stockValue = document.createElement('strong');
                stockValue.textContent = branch.stock;
                branchStock.append(stockValue);
                branchStocks.append(branchStock);
            });
            itemStockGroup.append(branchStocks);
            allBranchStock.append(itemStockGroup);
        });
        */
    }

    function getExistingProofCount() {
        return Array.from(proofPreviews.querySelectorAll('[data-proof-remove]'))
            .filter((checkbox) => !checkbox.checked).length;
    }

    function setSelectedProofPhotos(files) {
        const existingCount = getExistingProofCount();
        const availableSlots = Math.max(0, 5 - existingCount);
        const validFiles = Array.from(files).filter((file) => {
            return ['image/jpeg', 'image/png', 'image/webp'].includes(file.type) && file.size <= 5 * 1024 * 1024;
        });
        selectedProofPhotos = validFiles.slice(0, availableSlots);

        const fileTransfer = new DataTransfer();
        selectedProofPhotos.forEach((file) => fileTransfer.items.add(file));
        proofInput.files = fileTransfer.files;

        proofPreviews.querySelectorAll('[data-new-proof-preview]').forEach((preview) => preview.remove());
        proofPreviewUrls.forEach((url) => URL.revokeObjectURL(url));
        proofPreviewUrls = [];

        selectedProofPhotos.forEach((file, index) => {
            const previewUrl = URL.createObjectURL(file);
            proofPreviewUrls.push(previewUrl);

            const preview = document.createElement('figure');
            preview.className = 'transfer-proof-preview';
            preview.dataset.newProofPreview = '';

            const image = document.createElement('img');
            image.src = previewUrl;
            image.alt = file.name;

            const caption = document.createElement('figcaption');
            caption.className = 'transfer-proof-caption';
            const filename = document.createElement('span');
            filename.textContent = file.name;
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'transfer-proof-remove-selected';
            removeButton.dataset.removeSelectedPhoto = String(index);
            removeButton.setAttribute('aria-label', `Hapus ${file.name}`);
            removeButton.innerHTML = '<i class="fas fa-times"></i>';

            preview.append(image, caption);
            caption.append(filename, removeButton);
            proofPreviews.append(preview);
        });

        const totalCount = existingCount + selectedProofPhotos.length;
        proofCount.textContent = `${totalCount}/5 foto`;
        proofInput.disabled = existingCount >= 5;
        if (validFiles.length > selectedProofPhotos.length) {
            proofFeedback.textContent = 'Sebagian file tidak ditambahkan. Maksimal 5 foto, format PNG/JPG/WEBP, 5 MB per foto.';
        } else if (validFiles.length !== Array.from(files).length) {
            proofFeedback.textContent = 'Foto harus berformat PNG, JPG, atau WEBP dan berukuran maksimal 5 MB.';
        } else {
            proofFeedback.textContent = '';
        }
    }

    proofInput.addEventListener('change', () => setSelectedProofPhotos(proofInput.files));
    proofPreviews.addEventListener('change', (event) => {
        if (event.target.matches('[data-proof-remove]')) {
            setSelectedProofPhotos(selectedProofPhotos);
        }
    });
    proofPreviews.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-remove-selected-photo]');
        if (removeButton) {
            selectedProofPhotos.splice(Number(removeButton.dataset.removeSelectedPhoto), 1);
            setSelectedProofPhotos(selectedProofPhotos);
        }
    });
    proofDropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        proofDropzone.classList.add('is-dragging');
    });
    proofDropzone.addEventListener('dragleave', () => proofDropzone.classList.remove('is-dragging'));
    proofDropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        proofDropzone.classList.remove('is-dragging');
        setSelectedProofPhotos(event.dataTransfer.files);
    });
    setSelectedProofPhotos([]);

    fromCabang.addEventListener('change', updateBranchSelection);
    toCabang.addEventListener('change', updateBranchSelection);
    itemRows.addEventListener('change', (event) => {
        if (event.target.matches('.transfer-item-select')) {
            updateRow(event.target.closest('[data-transfer-item-row]'));
            updateSummary();
        }
    });
    itemRows.addEventListener('input', (event) => {
        if (event.target.matches('.transfer-quantity-input')) {
            const row = event.target.closest('[data-transfer-item-row]');
            const stock = Number(row.querySelector('.transfer-item-select').selectedOptions[0]?.dataset.stok ?? 0);
            event.target.setCustomValidity(Number(event.target.value) > stock ? 'Qty melebihi stok tersedia.' : '');
            updateSummary();
        }
    });
    itemRows.addEventListener('click', (event) => {
        const removeButton = event.target.closest('.transfer-remove-button');
        if (removeButton && itemRows.querySelectorAll('[data-transfer-item-row]').length > 1) {
            removeButton.closest('[data-transfer-item-row]').remove();
            updateSummary();
        }
    });
    document.getElementById('addTransferItem').addEventListener('click', () => {
        itemRows.insertAdjacentHTML('beforeend', rowTemplate.innerHTML.replaceAll('__INDEX__', nextItemIndex++));
        updateBranchSelection();
    });
    updateBranchSelection();
</script>
@endpush
