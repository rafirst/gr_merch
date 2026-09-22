@extends('layouts.app')
@section('title', 'Input Barang Keluar')

@section('content')
<div class="card stockout-form-card">
    <div class="card-body">
        <form action="{{ route('stockout.store') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Jenis Keluar</label>
                    <select name="jenis" id="jenis" class="form-control" required>
                        <option value="penjualan">Penjualan</option>
                        <option value="DO">DO</option>
                        <option value="request">Request</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Nama Customer</label>
                    <input type="text" name="nama_customer" class="form-control" value="{{ old('nama_customer') }}" maxlength="255">
                </div>
                <div class="form-group col-md-3">
                    <label>PIC</label>
                    <input type="text" name="pic_penjualan" class="form-control" value="{{ old('pic_penjualan') }}" maxlength="255">
                </div>
                <div class="form-group col-md-3" id="nomorTeleponWrap" hidden>
                    <label for="nomorTelepon">Nomor Telpon</label>
                    <input type="tel" name="nomor_telepon" id="nomorTelepon" class="form-control" value="{{ old('nomor_telepon') }}" maxlength="30" inputmode="numeric" pattern="[0-9]*">
                </div>
                <div class="form-group col-md-3" id="nomorSpkWrap" hidden>
                    <label for="nomorSpk">Nomor SPK</label>
                    <input type="text" name="nomor_spk" id="nomorSpk" class="form-control" value="{{ old('nomor_spk') }}" maxlength="100">
                </div>
            </div>

            <div id="itemRows">
                <div class="form-row item-row">
                    <div class="form-group col-md-5">
                        <label>Item</label>
                        <div class="stockout-item-search-wrapper">
                            <input type="hidden" name="item_id[]" class="item-id-input">
                            <input type="text" class="form-control item-search-input" placeholder="Ketik kode atau nama item..." autocomplete="off" required>
                            <div class="item-options" role="listbox">
                                @foreach($items as $i)
                                    <button type="button" class="item-option" data-item-id="{{ $i->id }}" data-item-name="{{ $i->nama_items }}" data-harga="{{ $i->harga_items }}" data-harga-jual="{{ $i->harga_jual }}" data-search="{{ strtolower($i->kode_items.' '.$i->nama_items.' '.($i->cabang->nama_cabang ?? '')) }}" role="option">
                                        <span class="item-option-main">
                                            <strong>{{ $i->nama_items }}</strong>
                                            <small>{{ $i->kode_items }} · {{ $i->cabang->nama_cabang ?? '-' }}</small>
                                        </span>
                                        <span class="item-stock">Stok {{ $i->stok_items }}</span>
                                    </button>
                                @endforeach
                                <p class="item-no-results" hidden>Item tidak ditemukan.</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Jumlah</label>
                        <input type="number" name="jumlah[]" class="form-control quantity-input" min="1" required>
                    </div>
                    <div class="form-group col-md-2 harga-jual-wrap">
                        <label>Harga Jual (Rp)</label>
                        <input type="text" class="form-control price-display" readonly>
                        <input type="hidden" name="harga_jual[]" class="price-input">
                    </div>
                    <div class="form-group col-md-2 total-wrap">
                        <label>Total Harga (Rp)</label>
                        <input type="text" class="form-control total-display" readonly>
                        <input type="hidden" class="total-input">
                    </div>
                    <div class="form-group col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-item" title="Hapus item" disabled><i class="fas fa-minus"></i></button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary mb-3" id="addItem"><i class="fas fa-plus"></i> Tambah Barang</button>
            <div class="form-row stockout-meta-row">
                <div class="form-group col-md-3" id="discountWrap">
                    <label>Discount</label>
                    <select name="discount" id="discount" class="form-control" required>
                        <option value="">- Pilih Discount -</option>
                        <option value="member">TAG Member</option>
                        <option value="retail">Retail</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" readonly required>
                </div>
                <div class="form-group col-md-3" id="voucherWrap" hidden>
                    <label for="voucher">Voucher</label>
                    <select name="voucher" id="voucher" class="form-control">
                        <option value="">- Pilih Voucher -</option>
                        <option value="500k">Voucher 500K</option>
                        <option value="1jt">Voucher 1JT</option>
                    </select>
                </div>
            </div>
            <div class="alert alert-info" id="infoApproval">
                <i class="fas fa-info-circle"></i> Transaksi <strong>Penjualan</strong> akan langsung tercatat & mengurangi stok.
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2" placeholder="misal: nama customer, tujuan hadiah, dll"></textarea>
            </div>
            <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            <a href="{{ route('stockout.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<section class="card stockout-preview-card" aria-labelledby="transactionPreviewTitle">
    <div class="card-header stockout-preview-header">
        <div>
            <span class="stockout-preview-kicker">Preview Sebelum Simpan</span>
            <h3 id="transactionPreviewTitle" class="card-title">Detail Transaksi</h3>
        </div>
        <span class="stockout-preview-status"><i class="fas fa-eye"></i> Draft</span>
    </div>
    <div class="card-body">
        <div class="stockout-preview-meta">
            <div><span>Jenis Keluar</span><strong id="previewJenis">Penjualan</strong></div>
            <div><span>Customer</span><strong id="previewCustomer">-</strong></div>
            <div><span>PIC Penjualan</span><strong id="previewPic">-</strong></div>
            <div id="previewNomorTeleponWrap"><span>Nomor Telpon</span><strong id="previewNomorTelepon">-</strong></div>
            <div id="previewNomorSpkWrap" hidden><span>Nomor SPK</span><strong id="previewNomorSpk">-</strong></div>
        </div>

        <div class="stockout-preview-table-wrap">
            <table class="stockout-preview-table">
                <thead>
                    <tr>
                        <th>Detail Barang</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-right">Harga Jual</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody id="previewItems">
                    <tr><td colspan="4" class="preview-empty"><i class="fas fa-box-open"></i><span>Pilih item untuk melihat detail transaksi.</span></td></tr>
                </tbody>
            </table>
        </div>

        <div class="stockout-preview-footer">
            <div class="stockout-preview-totals">
                <div><span>Subtotal</span><strong id="previewSubtotal">Rp 0</strong></div>
                <div><span>Potongan</span><strong id="previewDiscountAmount">Rp 0</strong></div>
                <div id="previewChangeWrap" hidden><span>Kembalian</span><strong id="previewChange">Rp 0</strong></div>
                <div class="preview-grand-total"><span>Total Keseluruhan</span><strong id="previewTotal">Rp 0</strong></div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .stockout-form-card {
        margin-bottom: 1.25rem;
    }

    .stockout-form-card .card-body {
        padding: 1.05rem 1.2rem 1.15rem;
    }

    .stockout-form-card form > .form-row:first-of-type {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.85rem;
        margin-right: 0;
        margin-left: 0;
    }

    .stockout-form-card form > .form-row:first-of-type > .form-group,
    .stockout-form-card #itemRows .form-group,
    .stockout-form-card form > .form-row:last-of-type > .form-group,
    .stockout-form-card .stockout-meta-row > .form-group {
        width: auto;
        max-width: none;
        margin-right: 0;
        margin-left: 0;
        padding-right: 0;
        padding-left: 0;
    }

    .stockout-form-card .form-group {
        margin-bottom: 0.75rem;
    }

    .stockout-form-card label {
        margin-bottom: 0.32rem;
        color: rgba(244, 247, 250, 0.82);
        font-size: 0.72rem;
        font-weight: 700;
    }

    .stockout-form-card .form-control {
        min-height: 34px;
        padding: 0.4rem 0.65rem;
        border-color: rgba(105, 143, 176, 0.38);
        background-color: rgba(2, 10, 17, 0.78);
        color: #f4f7fa;
        font-size: 0.78rem;
    }

    .stockout-form-card .form-control:focus {
        border-color: rgba(247, 0, 8, 0.85);
        box-shadow: 0 0 0 0.12rem rgba(247, 0, 8, 0.12);
    }

    .stockout-form-card #itemRows {
        margin-top: 0.15rem;
    }

    .stockout-item-search-wrapper {
        position: relative;
    }

    .stockout-item-search-wrapper .item-search-input {
        padding-right: 2.5rem;
        background-image: linear-gradient(45deg, transparent 50%, #f70008 50%), linear-gradient(135deg, #f70008 50%, transparent 50%);
        background-position: calc(100% - 1.1rem) 52%, calc(100% - 0.75rem) 52%;
        background-size: 0.4rem 0.4rem, 0.4rem 0.4rem;
        background-repeat: no-repeat;
    }

    .stockout-item-search-wrapper .item-options {
        position: absolute;
        z-index: 1050;
        top: calc(100% + 0.35rem);
        right: 0;
        left: 0;
        display: none;
        max-height: 290px;
        overflow-y: auto;
        padding: 0.35rem;
        border: 1px solid rgba(247, 0, 8, 0.65);
        border-radius: 0.35rem;
        background: rgba(3, 10, 17, 0.98);
        box-shadow: 0 0.8rem 1.8rem rgba(0, 0, 0, 0.45);
    }

    .stockout-item-search-wrapper .item-options.is-open {
        display: block;
    }

    .stockout-item-search-wrapper .item-option {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        gap: 1rem;
        padding: 0.7rem 0.8rem;
        border: 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        background: transparent;
        color: #f4f7fa;
        text-align: left;
        cursor: pointer;
    }

    .stockout-item-search-wrapper .item-option:hover,
    .stockout-item-search-wrapper .item-option:focus {
        outline: 0;
        border-radius: 0.25rem;
        background: linear-gradient(90deg, rgba(247, 0, 8, 0.22), rgba(247, 0, 8, 0.06));
    }

    .stockout-item-search-wrapper .item-option-main {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 0.2rem;
    }

    .stockout-item-search-wrapper .item-option-main strong {
        overflow: hidden;
        color: #ffffff;
        font-size: 0.92rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .stockout-item-search-wrapper .item-option-main small {
        color: rgba(244, 247, 250, 0.62);
        font-size: 0.75rem;
    }

    .stockout-item-search-wrapper .item-stock {
        flex: 0 0 auto;
        padding: 0.25rem 0.45rem;
        border: 1px solid rgba(247, 0, 8, 0.5);
        border-radius: 0.2rem;
        color: #ff6268;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .stockout-item-search-wrapper .item-no-results {
        margin: 0;
        padding: 0.8rem;
        color: rgba(244, 247, 250, 0.62);
        font-size: 0.85rem;
        text-align: center;
    }

    .stockout-form-card #itemRows .item-row {
        display: grid;
        grid-template-columns: minmax(0, 5fr) minmax(88px, 2fr) minmax(105px, 2fr) minmax(105px, 2fr) 38px;
        gap: 0.7rem;
        align-items: end;
        margin-right: 0;
        margin-left: 0;
    }

    .stockout-form-card #itemRows.is-non-sales .item-row {
        grid-template-columns: minmax(0, 5fr) minmax(88px, 2fr) 38px;
    }

    .stockout-form-card #itemRows .item-row > .form-group:last-child {
        display: flex;
        justify-content: flex-end;
        padding-bottom: 0.75rem;
    }

    .stockout-form-card .remove-item {
        width: 34px;
        height: 34px;
        padding: 0;
    }

    .stockout-form-card #addItem {
        margin-top: 0.05rem;
        margin-bottom: 0.85rem !important;
        padding: 0.42rem 0.7rem;
        font-size: 0.75rem;
    }

    .stockout-form-card .stockout-meta-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
        margin-right: 0;
        margin-left: 0;
    }

    .stockout-form-card #infoApproval {
        margin: 0 0 0.75rem;
        padding: 0.55rem 0.75rem;
        font-size: 0.76rem;
    }

    .stockout-form-card textarea.form-control {
        min-height: 62px;
        resize: vertical;
    }

    .stockout-form-card form > .btn {
        min-height: 34px;
        padding: 0.42rem 0.8rem;
        font-size: 0.76rem;
    }

    @media (max-width: 767.98px) {
        .stockout-form-card .card-body {
            padding: 0.9rem;
        }

        .stockout-form-card form > .form-row:first-of-type,
        .stockout-form-card .stockout-meta-row {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .stockout-form-card #itemRows .item-row {
            grid-template-columns: minmax(0, 1fr) minmax(84px, 0.55fr);
            gap: 0.65rem;
        }

        .stockout-form-card #itemRows.is-non-sales .item-row {
            grid-template-columns: minmax(0, 1fr) minmax(84px, 0.55fr) 34px;
        }

        .stockout-form-card #itemRows .item-row > .form-group:last-child {
            grid-column: 2;
            grid-row: 3;
            padding-bottom: 0.75rem;
        }

        .stockout-form-card #itemRows.is-non-sales .item-row > .form-group:last-child {
            grid-column: 3;
            grid-row: 1;
            padding-bottom: 0.75rem;
        }
    }

    .stockout-preview-card {
        margin-top: 1.5rem;
        border: 1px solid rgba(105, 143, 176, 0.42);
        background: rgba(3, 12, 20, 0.86);
    }

    .stockout-preview-header {
        min-height: 43px;
        padding-top: 0.55rem;
        padding-bottom: 0.55rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(105, 143, 176, 0.25);
    }

    .stockout-preview-kicker {
        display: block;
        margin-bottom: 0.25rem;
        color: #ef1d2f;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .stockout-preview-status {
        position: absolute;
        right: 0.85rem;
        bottom: 0.55rem;
        padding: 0.3rem 0.55rem;
        border: 1px solid rgba(105, 143, 176, 0.45);
        border-radius: 4px;
        color: rgba(244, 247, 250, 0.7);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .stockout-preview-status i {
        margin-right: 0.25rem;
        color: #ef1d2f;
    }

    .stockout-preview-meta {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.7rem;
        margin-bottom: 1.25rem;
    }

    .stockout-preview-meta > div {
        min-width: 0;
        padding: 0.7rem 0.75rem;
        border-left: 2px solid #ef1d2f;
        background: rgba(105, 143, 176, 0.08);
    }

    .stockout-preview-meta span,
    .stockout-preview-note span,
    .stockout-preview-totals span {
        display: block;
        color: rgba(244, 247, 250, 0.55);
        font-size: 0.7rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .stockout-preview-meta strong {
        display: block;
        overflow: hidden;
        margin-top: 0.25rem;
        color: #ffffff;
        font-size: 0.82rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .stockout-preview-table-wrap {
        overflow-x: auto;
    }

    .stockout-preview-table {
        width: 100%;
        min-width: 620px;
        border-collapse: collapse;
        color: #f4f7fa;
    }

    .stockout-preview-table th {
        padding: 0.65rem 0.75rem;
        border-bottom: 1px solid rgba(105, 143, 176, 0.35);
        color: rgba(244, 247, 250, 0.58);
        font-size: 0.7rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .stockout-preview-table th:first-child,
    .stockout-preview-table td:first-child {
        width: 52%;
        text-align: left;
    }

    .stockout-preview-table th:not(:first-child),
    .stockout-preview-table td:not(:first-child) {
        width: 16%;
    }

    .stockout-preview-table td {
        padding: 0.75rem;
        border-bottom: 1px solid rgba(105, 143, 176, 0.16);
        vertical-align: middle;
    }

    .preview-item-name,
    .preview-item-code {
        display: block;
    }

    .preview-item-name {
        color: #ffffff;
        font-weight: 700;
    }

    .preview-item-code {
        margin-top: 0.2rem;
        color: rgba(244, 247, 250, 0.52);
        font-size: 0.72rem;
    }

    .preview-empty {
        height: 142px;
        padding: 1.5rem !important;
        color: rgba(244, 247, 250, 0.55);
        text-align: center !important;
    }

    .preview-empty i {
        display: block;
        margin-bottom: 0.4rem;
        color: #ef1d2f;
        font-size: 1.25rem;
    }

    .stockout-preview-footer {
        display: block;
        margin-top: 1.25rem;
    }

    .stockout-preview-totals {
        width: 100%;
    }

    .stockout-preview-note {
        min-width: 0;
        padding: 0.85rem 1rem;
        border-left: 2px solid rgba(105, 143, 176, 0.45);
        background: rgba(105, 143, 176, 0.08);
    }

    .stockout-preview-note strong {
        display: block;
        margin-top: 0.4rem;
        color: rgba(244, 247, 250, 0.82);
        font-size: 0.85rem;
        font-weight: 400;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .stockout-preview-totals > div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.45rem 0;
        color: rgba(244, 247, 250, 0.75);
    }

    .stockout-preview-totals strong {
        color: #ffffff;
        font-size: 0.9rem;
    }

    .stockout-preview-totals .preview-grand-total {
        margin-top: 0.45rem;
        padding: 0.75rem 0.9rem;
        background: linear-gradient(105deg, rgba(105, 143, 176, 0.2) 0 46%, #c9142a 46%);
        color: #ffffff;
    }

    .stockout-preview-totals .preview-grand-total span {
        color: #ffffff;
        font-weight: 700;
    }

    @media (max-width: 991.98px) {
        .stockout-preview-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .stockout-preview-header,
        .stockout-preview-footer {
            align-items: flex-start;
            grid-template-columns: 1fr;
            flex-direction: column;
        }

        .stockout-preview-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>
@endpush

@push('scripts')
<script>
    const jenisSelect = document.getElementById('jenis');
    const discountWrap = document.getElementById('discountWrap');
    const discountInput = document.getElementById('discount');
    const voucherWrap = document.getElementById('voucherWrap');
    const voucherInput = document.getElementById('voucher');
    const previewChangeWrap = document.getElementById('previewChangeWrap');
    const infoBox = document.getElementById('infoApproval');
    const nomorTeleponWrap = document.getElementById('nomorTeleponWrap');
    const nomorTeleponInput = document.getElementById('nomorTelepon');
    const nomorSpkWrap = document.getElementById('nomorSpkWrap');
    const nomorSpkInput = document.getElementById('nomorSpk');
    const previewNomorTeleponWrap = document.getElementById('previewNomorTeleponWrap');
    const previewNomorSpkWrap = document.getElementById('previewNomorSpkWrap');
    const itemRows = document.getElementById('itemRows');
    const addItemButton = document.getElementById('addItem');
    const previewItems = document.getElementById('previewItems');
    const previewElements = {
        jenis: document.getElementById('previewJenis'),
        customer: document.getElementById('previewCustomer'),
        pic: document.getElementById('previewPic'),
        nomorTelepon: document.getElementById('previewNomorTelepon'),
        nomorSpk: document.getElementById('previewNomorSpk'),
        tanggal: document.getElementById('previewTanggal'),
        subtotal: document.getElementById('previewSubtotal'),
        discountAmount: document.getElementById('previewDiscountAmount'),
        change: document.getElementById('previewChange'),
        total: document.getElementById('previewTotal'),
    };

    const discountRates = {
        member: 20,
        retail: 15,
    };

    function formatCurrency(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0,
        }).format(value);
    }

    function formatInputCurrency(value) {
        return value ? new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0,
        }).format(value) : '';
    }

    function setPreviewText(element, value) {
        if (element) {
            element.textContent = value || '-';
        }
    }

    function toggleJenis() {
        const salesOnlyFields = document.querySelectorAll('.harga-jual-wrap, .total-wrap');
        const isPenjualan = jenisSelect.value === 'penjualan';
        const requiresSpk = jenisSelect.value === 'DO' || jenisSelect.value === 'request';
        const isDo = jenisSelect.value === 'DO';
        const isRequest = jenisSelect.value === 'request';

        itemRows.classList.toggle('is-non-sales', !isPenjualan);
        nomorTeleponWrap.hidden = !isPenjualan;
        nomorSpkWrap.hidden = !requiresSpk;
        previewNomorTeleponWrap.hidden = !isPenjualan;
        previewNomorSpkWrap.hidden = !requiresSpk;
        nomorSpkInput.required = requiresSpk;
        voucherWrap.hidden = !isDo;
        voucherInput.disabled = !isDo;
        voucherInput.required = isDo;

        if (isPenjualan) {
            salesOnlyFields.forEach((field) => field.style.display = '');
            discountWrap.style.display = '';
            discountInput.disabled = false;
            discountInput.required = true;
            infoBox.innerHTML = '<i class="fas fa-info-circle"></i> Transaksi <strong>Penjualan</strong> akan langsung tercatat & mengurangi stok.';
            infoBox.className = 'alert alert-info';
        } else if (isDo) {
            salesOnlyFields.forEach((field) => field.style.display = 'none');
            discountWrap.style.display = 'none';
            discountInput.disabled = true;
            discountInput.required = false;
            infoBox.innerHTML = '<i class="fas fa-check-circle"></i> Transaksi <strong>DO</strong> akan langsung tercatat & mengurangi stok tanpa approval.';
            infoBox.className = 'alert alert-info';
        } else if (isRequest) {
            salesOnlyFields.forEach((field) => field.style.display = 'none');
            discountWrap.style.display = 'none';
            discountInput.disabled = true;
            discountInput.required = false;
            infoBox.innerHTML = '<i class="fas fa-clock"></i> Transaksi <strong>' + jenisSelect.options[jenisSelect.selectedIndex].text + '</strong> memerlukan approval Admin Pusat sebelum stok dipotong.';
            infoBox.className = 'alert alert-warning';
        }
        calculateTotals();
    }

    function calculateRowTotal(row) {
        const jumlah = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const harga = parseFloat(row.querySelector('.price-input').value) || 0;
        const discount = discountRates[discountInput.value] || 0;
        const total = harga * jumlah * (1 - discount / 100);
        row.querySelector('.total-input').value = total.toFixed(2);
        row.querySelector('.total-display').value = formatInputCurrency(total);
    }

    function calculateTotals() {
        document.querySelectorAll('.item-row').forEach(calculateRowTotal);
        renderPreview();
    }

    function renderPreview() {
        const discount = discountRates[discountInput.value] || 0;
        const voucherAmounts = {
            '500k': 500000,
            '1jt': 1000000,
        };
        const voucherAmount = jenisSelect.value === 'DO' ? (voucherAmounts[voucherInput.value] || 0) : 0;
        let subtotal = 0;
        let total = 0;
        let itemCount = 0;

        setPreviewText(previewElements.jenis, jenisSelect.options[jenisSelect.selectedIndex]?.text);
        setPreviewText(previewElements.customer, document.querySelector('[name="nama_customer"]').value.trim());
        setPreviewText(previewElements.pic, document.querySelector('[name="pic_penjualan"]').value.trim());
        setPreviewText(previewElements.nomorTelepon, nomorTeleponInput.value.trim());
        setPreviewText(previewElements.nomorSpk, nomorSpkInput.value.trim());
        setPreviewText(previewElements.tanggal, document.querySelector('[name="tanggal"]').value);

        previewItems.innerHTML = '';
        document.querySelectorAll('.item-row').forEach((row) => {
            const itemSearch = row.querySelector('.item-search-input');
            const itemId = row.querySelector('.item-id-input');
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const rowSubtotal = price * quantity;
            const rowTotal = rowSubtotal * (1 - discount / 100);

            if (!itemId.value) {
                return;
            }

            const itemCell = document.createElement('td');
            const itemName = document.createElement('strong');
            const itemCode = document.createElement('small');
            itemName.className = 'preview-item-name';
            itemCode.className = 'preview-item-code';
            itemName.textContent = itemSearch.value;
            itemCode.textContent = itemId.dataset.itemCode || '';
            itemCell.append(itemName, itemCode);

            const quantityCell = document.createElement('td');
            quantityCell.className = 'text-right';
            quantityCell.textContent = quantity || '-';

            const priceCell = document.createElement('td');
            priceCell.className = 'text-right';
            priceCell.textContent = price ? formatCurrency(price) : '-';

            const totalCell = document.createElement('td');
            totalCell.className = 'text-right';
            totalCell.textContent = rowTotal ? formatCurrency(rowTotal) : '-';

            const tableRow = document.createElement('tr');
            tableRow.append(itemCell, quantityCell, priceCell, totalCell);
            previewItems.appendChild(tableRow);
            subtotal += rowSubtotal;
            total += rowTotal;
            itemCount++;
        });

        if (!itemCount) {
            previewItems.innerHTML = '<tr><td colspan="4" class="preview-empty"><i class="fas fa-box-open"></i><span>Pilih item untuk melihat detail transaksi.</span></td></tr>';
        }

        const changeAmount = jenisSelect.value === 'DO' ? Math.max(0, voucherAmount - subtotal) : 0;
        const totalAfterVoucher = jenisSelect.value === 'DO'
            ? Math.max(0, subtotal - voucherAmount)
            : total;
        previewChangeWrap.hidden = jenisSelect.value !== 'DO';

        setPreviewText(previewElements.subtotal, formatCurrency(subtotal));
        setPreviewText(previewElements.discountAmount, formatCurrency(jenisSelect.value === 'DO' ? voucherAmount : subtotal - total));
        setPreviewText(previewElements.change, formatCurrency(changeAmount));
        setPreviewText(previewElements.total, formatCurrency(totalAfterVoucher));
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach((row) => {
            row.querySelector('.remove-item').disabled = rows.length === 1;
        });
    }

    function filterItems(row) {
        const searchInput = row.querySelector('.item-search-input');
        const options = Array.from(row.querySelectorAll('.item-option'));
        const noResults = row.querySelector('.item-no-results');
        const query = searchInput.value.trim().toLowerCase();
        let visibleItems = 0;

        options.forEach((option) => {
            const isVisible = !query || option.dataset.search.includes(query);
            option.hidden = !isVisible;
            visibleItems += isVisible ? 1 : 0;
        });

        noResults.hidden = visibleItems > 0;
        row.querySelector('.item-options').classList.add('is-open');
    }

    function selectItem(option) {
        const row = option.closest('.item-row');
        const searchInput = row.querySelector('.item-search-input');
        const itemId = row.querySelector('.item-id-input');
        const priceInput = row.querySelector('.price-input');

        searchInput.value = option.dataset.itemName;
        itemId.value = option.dataset.itemId;
        itemId.dataset.itemCode = option.querySelector('small').textContent.split(' · ')[0];
        searchInput.setCustomValidity('');
        priceInput.value = option.dataset.harga || '';
        row.querySelector('.price-display').value = formatInputCurrency(priceInput.value);
        row.querySelector('.item-options').classList.remove('is-open');
        calculateRowTotal(row);
        renderPreview();
    }

    itemRows.addEventListener('input', function (event) {
        if (event.target.classList.contains('item-search-input')) {
            const row = event.target.closest('.item-row');
            row.querySelector('.item-id-input').value = '';
            row.querySelector('.item-id-input').dataset.itemCode = '';
            event.target.setCustomValidity('Pilih item dari daftar yang tersedia.');
            filterItems(row);
        }

        if (event.target.classList.contains('quantity-input') || event.target.classList.contains('price-input')) {
            calculateRowTotal(event.target.closest('.item-row'));
            renderPreview();
        }
    });

    itemRows.addEventListener('focusin', function (event) {
        if (event.target.classList.contains('item-search-input')) {
            filterItems(event.target.closest('.item-row'));
        }
    });

    itemRows.addEventListener('click', function (event) {
        const itemOption = event.target.closest('.item-option');
        if (itemOption) {
            selectItem(itemOption);
            return;
        }

        const removeButton = event.target.closest('.remove-item');
        if (!removeButton) {
            return;
        }

        removeButton.closest('.item-row').remove();
        updateRemoveButtons();
        renderPreview();
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.stockout-item-search-wrapper')) {
            document.querySelectorAll('.stockout-item-search-wrapper .item-options').forEach((options) => options.classList.remove('is-open'));
        }
    });

    addItemButton.addEventListener('click', function () {
        const newRow = itemRows.querySelector('.item-row').cloneNode(true);
        newRow.querySelector('.item-search-input').value = '';
        newRow.querySelector('.item-id-input').value = '';
        newRow.querySelector('.item-id-input').dataset.itemCode = '';
        newRow.querySelector('.quantity-input').value = '';
        newRow.querySelector('.price-input').value = '';
        newRow.querySelector('.price-display').value = '';
        newRow.querySelector('.total-input').value = '0.00';
        newRow.querySelector('.total-display').value = '';
        newRow.querySelectorAll('.item-option').forEach((option) => {
            option.hidden = false;
        });
        newRow.querySelector('.item-no-results').hidden = true;
        newRow.querySelector('.item-options').classList.remove('is-open');
        itemRows.appendChild(newRow);
        updateRemoveButtons();
        renderPreview();
    });

    discountInput.addEventListener('change', calculateTotals);
    voucherInput.addEventListener('change', renderPreview);
    jenisSelect.addEventListener('change', toggleJenis);
    nomorTeleponInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
        renderPreview();
    });
    document.querySelectorAll('[name="nama_customer"], [name="pic_penjualan"], [name="nomor_telepon"], [name="nomor_spk"], [name="keterangan"]').forEach((input) => {
        input.addEventListener('input', renderPreview);
    });
    document.querySelector('[name="tanggal"]').addEventListener('change', renderPreview);
    updateRemoveButtons();
    toggleJenis();
</script>
@endpush

