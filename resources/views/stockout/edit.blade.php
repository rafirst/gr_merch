@extends('layouts.app')
@section('title', 'Edit Barang Keluar')

@section('content')
@php
    $transferAccounts = [
        'PLG' => 'BCA 0216198888',
        'POL' => 'BCA 0216198888',
        'THO' => 'BCA 0216198888',
        'PRB' => 'BCA 0218829999',
        'TME' => 'BCA 0218829999',
        'LLG' => 'BCA 0217817777',
    ];
    $transferAccount = $transferAccounts[auth()->user()?->cabang?->kode_cabang ?? ''] ?? null;
    $discountOption = $stockOut->discountOption();
    $isEditRetailNonKtpSale = $stockOut->isRetailNonKtpSale();
@endphp

<div class="card stockout-form-card">
    <div class="card-header stockout-edit-header">
        <div>
            <span class="stockout-edit-kicker">Edit Transaksi</span>
            <h3 class="card-title mb-0">
                Barang Keluar #{{ str_pad((string) $stockOut->id, 6, '0', STR_PAD_LEFT) }}
            </h3>
        </div>
        {{-- <div class="stockout-edit-actions">
            @if($stockOut->status === 'approved')
                <a href="{{ route('stockout.invoice', $stockOut) }}" class="btn btn-sm stockout-invoice-button" title="Cetak invoice" aria-label="Cetak invoice" target="_blank" rel="noopener">
                    <i class="fas fa-print"></i> Invoice
                </a>
            @endif
            <a href="{{ route('stockout.show', $stockOut) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div> --}}
    </div>
    <div class="card-body">
        <div class="stockout-edit-note">
            <i class="fas fa-info-circle"></i>
            <span>
                @if($stockOut->status === 'approved')
                    Stok otomatis disesuaikan mengikuti perubahan jumlah dan item pada transaksi ini.
                @else
                    Transaksi ini masih menunggu approval, jadi stok belum dipotong dan tidak ikut berubah.
                @endif
                Jenis transaksi tidak dapat diubah setelah transaksi dibuat.
            </span>
        </div>

        <form action="{{ route('stockout.update', $stockOut) }}" method="POST">
            @csrf
            @method('PUT')
            <section class="stockout-form-section" aria-labelledby="transactionDataTitle">
                <h3 class="stockout-form-section-title" id="transactionDataTitle">Data Transaksi</h3>
                <div class="stockout-transaction-layout" id="stockoutTransactionLayout">
                <div class="form-row stockout-transaction-row">
                    <div class="form-group">
                        <label for="jenis">Jenis Keluar</label>
                        <select name="jenis_display" id="jenis" class="form-control" disabled>
                            <option value="{{ $stockOut->jenis }}" selected>{{ $stockOut->jenis === 'DO' ? 'DO' : ucfirst($stockOut->jenis) }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="picPenjualan">PIC</label>
                        <input type="text" name="pic_penjualan" id="picPenjualan" class="form-control" value="{{ old('pic_penjualan', $stockOut->pic_penjualan) }}" maxlength="255">
                    </div>
                </div>

                <div class="form-row stockout-transaction-reference-row">
                    <div class="form-group col-md-3" id="nomorSpkWrap" hidden>
                        <label for="nomorSpk">Nomor SPK</label>
                        <input type="text" name="nomor_spk" id="nomorSpk" class="form-control" value="{{ old('nomor_spk', $stockOut->nomor_spk) }}" maxlength="100">
                    </div>
                    <div class="form-group col-md-3" id="nomorImWrap" hidden>
                        <label for="nomorIm">Nomor IM</label>
                        <input type="text" name="nomor_im" id="nomorIm" class="form-control" value="{{ old('nomor_im', $stockOut->nomor_im) }}" maxlength="100">
                    </div>
                </div>
<div id="itemRows">
                @foreach($stockOuts as $stockOutRow)
                    <div class="form-row item-row">
                        <div class="form-group col-md-5 item-select-group">
                            <label>Item</label>
                            <div class="stockout-item-search-wrapper">
                                <input type="hidden" name="item_id[]" class="item-id-input" value="{{ $stockOutRow->item_id }}" data-item-code="{{ $stockOutRow->item->kode_items ?? '' }}">
                                <input type="text" class="form-control item-search-input" placeholder="Ketik kode atau nama item..." autocomplete="off" required
                                    value="{{ $stockOutRow->item->nama_items ?? '' }}">
                                <div class="item-options" role="listbox">
                                    @foreach($items as $i)
                                        <button type="button" class="item-option" data-item-id="{{ $i->id }}" data-item-name="{{ $i->nama_items }}" data-harga-jual="{{ $i->harga_jual }}" data-search="{{ strtolower($i->kode_items.' '.$i->nama_items.' '.($i->cabang->nama_cabang ?? '')) }}" role="option">
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
                        <div class="form-group col-md-2 quantity-group">
                            <label>Jumlah</label>
                            <input type="number" name="jumlah[]" class="form-control quantity-input" min="1" value="{{ $stockOutRow->jumlah }}" required>
                        </div>
                        <div class="form-group col-md-2 harga-jual-wrap price-group">
                            <label>Harga Jual (Rp)</label>
                            <input type="text" class="form-control price-display" readonly value="{{ $stockOut->jenis === 'penjualan' ? number_format((float) $stockOutRow->item->harga_jual, 0, ',', '.') : ($stockOutRow->harga_jual !== null ? number_format((float) $stockOutRow->harga_jual, 0, ',', '.') : '') }}">
                            <input type="hidden" name="harga_jual[]" class="price-input" value="{{ $stockOut->jenis === 'penjualan' ? $stockOutRow->item->harga_jual : $stockOutRow->harga_jual }}">
                        </div>
                        <div class="form-group col-md-2 total-wrap total-group">
                            <label>Total Harga (Rp)</label>
                            <input type="text" class="form-control total-display" readonly>
                            <input type="hidden" class="total-input">
                        </div>
                        <div class="form-group col-md-1 d-flex align-items-end remove-item-group">
                            <button type="button" class="btn btn-danger remove-item" title="Hapus item"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-secondary mb-3" id="addItem"><i class="fas fa-plus"></i> Tambah Barang</button>
                </div>
            <div class="form-row stockout-meta-row">
                <div class="form-group col-md-3" id="discountWrap">
                    <label>Discount</label>
                    <select name="discount_display" id="discount" class="form-control" disabled>
                        <option value="">- Tanpa Discount -</option>
                        <option value="member" @selected($discountOption === 'member')>TAG Member</option>
                        <option value="retail" @selected($discountOption === 'retail')>Retail - KTP</option>
                        <option value="retail_non_ktp" @selected($discountOption === 'retail_non_ktp')>Retail - Non KTP</option>
                    </select>
                </div>
                <div class="form-group col-md-3" id="jenisPembayaranWrap">
                    <label for="jenisPembayaran">Jenis Pembayaran</label>
                    <select name="jenis_pembayaran" id="jenisPembayaran" class="form-control" required>
                        <option value="">- Pilih Jenis Pembayaran -</option>
                        <option value="qris" @selected(old('jenis_pembayaran', $stockOut->jenis_pembayaran) === 'qris')>QRIS</option>
                        <option value="transfer" @selected(old('jenis_pembayaran', $stockOut->jenis_pembayaran) === 'transfer')>Transfer{{ $transferAccount ? ' — '.$transferAccount : '' }}</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ $stockOut->tanggal?->format('Y-m-d') }}" readonly>
                </div>
                <div class="form-group col-md-3" id="paketBundlingWrap" hidden>
                    <label for="paketBundling">Paket Bundling</label>
                    <select name="paket_bundling_display" id="paketBundling" class="form-control" disabled>
                        <option value="">- Pilih Paket Bundling -</option>
                        <option value="paket_a" @selected($stockOut->paket_bundling === 'paket_a')>Paket A [ 1x Racing Cap + 1x Umbrella ]</option>
                        <option value="paket_b" @selected($stockOut->paket_bundling === 'paket_b')>Paket B [ 1x T-Shirt + 1x Tumbler ]</option>
                        <option value="paket_c" @selected($stockOut->paket_bundling === 'paket_c')>Paket C [ 1x Softshell Jacket + 1x Mechanic ]</option>
                    </select>
                </div>
            </div>
            <div class="alert alert-info" id="infoApproval">
                <i class="fas fa-info-circle"></i> Perubahan akan langsung diterapkan ke transaksi ini.
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2" maxlength="255" placeholder="misal: nama customer, tujuan hadiah, dll">{{ old('keterangan', $stockOut->keterangan) }}</textarea>
            </div>
            </section>

            <section class="stockout-form-section" id="customerSection" aria-labelledby="customerDataTitle">
                <h3 class="stockout-form-section-title" id="customerDataTitle">Data Customer</h3>
                <div class="form-row stockout-customer-row">
                    <div class="form-group">
                        <label for="namaCustomer">Nama Customer <span class="required-indicator">*</span></label>
                        <input type="text" name="nama_customer" id="namaCustomer" class="form-control" value="{{ old('nama_customer', $stockOut->nama_customer) }}" maxlength="255" required>
                    </div>
                    @php($isEditMemberSale = $stockOut->isTagMemberSale())
                    @if($isEditMemberSale)
                        <div class="form-group" id="jabatanWrap">
                            <label for="jabatan">Jabatan <span class="required-indicator">*</span></label>
                            <input type="text" name="jabatan" id="jabatan" class="form-control" value="{{ old('jabatan', $stockOut->jabatan) }}" maxlength="100" required>
                        </div>
                    @else
                        <div class="form-group" id="nikKtpWrap">
                            <label for="nikKtp" id="nikKtpLabel">NIK KTP <span class="required-indicator" id="nikKtpRequiredIndicator" @if($isEditRetailNonKtpSale) hidden @endif>*</span></label>
                            <input type="text" name="nik_ktp" id="nikKtp" class="form-control" value="{{ $isEditRetailNonKtpSale ? '' : old('nik_ktp', $stockOut->nik_ktp) }}" minlength="16" maxlength="16" inputmode="numeric" pattern="[0-9]{16}" @readonly($isEditRetailNonKtpSale) @required($stockOut->requiresNikKtp())>
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="nomorTelepon">No. Telepon <span class="required-indicator" id="nomorTeleponRequiredIndicator" @if($isEditRetailNonKtpSale) hidden @endif>*</span></label>
                        <input type="tel" name="nomor_telepon" id="nomorTelepon" class="form-control" value="{{ old('nomor_telepon', $stockOut->nomor_telepon) }}" maxlength="30" inputmode="numeric" pattern="[0-9]*" @readonly($isEditRetailNonKtpSale) @required(! $isEditRetailNonKtpSale)>
                    </div>
                    <div class="form-group stockout-customer-address">
                        <label for="alamatCustomer">Alamat <span class="required-indicator" id="alamatCustomerRequiredIndicator" @if($isEditRetailNonKtpSale) hidden @endif>*</span></label>
                        <textarea name="alamat_customer" id="alamatCustomer" class="form-control" rows="2" maxlength="2000" @readonly($isEditRetailNonKtpSale) @required(! $isEditRetailNonKtpSale)>{{ old('alamat_customer', $stockOut->alamat_customer) }}</textarea>
                    </div>
                </div>
            </section>

            <div class="stockout-form-actions">
                <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan </button>
                <a href="{{ route('stockout.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

@if($stockOut->status === 'approved')
    <section class="card stockout-form-card stockout-payment-card" id="bukti-payment" aria-labelledby="paymentProofTitle">
        <div class="card-header stockout-payment-header">
            <div class="stockout-payment-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div>
                <span class="stockout-payment-kicker">Bukti Payment</span>
                <h3 class="card-title mb-0" id="paymentProofTitle">Input Bukti Pembayaran</h3>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('stockout.bukti-pembayaran.update', $stockOut) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_redirect_to" value="edit">

                @if($stockOut->bukti_pembayaran || $stockOut->kode_pembayaran)
                    <div class="stockout-payment-status">
                        <i class="fas fa-circle-check"></i>
                        <div>
                            <strong>Kode pembayaran tersimpan</strong>
                            <span>{{ $stockOut->kode_pembayaran ?: '-' }}</span>
                        </div>
                        @if($stockOut->bukti_pembayaran)
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($stockOut->bukti_pembayaran) }}" target="_blank" rel="noopener" class="stockout-payment-status-link">
                                <i class="fas fa-file-pdf"></i> Lihat Bukti
                            </a>
                        @endif
                    </div>
                @endif

                <div class="stockout-payment-field">
                    <label for="stockoutPaymentCode"><i class="fas fa-barcode"></i> Kode Pembayaran</label>
                    <input type="text" name="kode_pembayaran" id="stockoutPaymentCode" class="form-control" maxlength="100"
                        placeholder="Contoh: BAY-2026-000123" value="{{ old('kode_pembayaran', $stockOut->kode_pembayaran) }}" required>
                </div>

                <div class="stockout-payment-field">
                    <label for="stockoutPaymentProof"><i class="fas fa-file-pdf"></i> Upload Bukti Pembayaran</label>
                    <label class="stockout-payment-dropzone" for="stockoutPaymentProof" data-payment-dropzone>
                        <i class="fas fa-cloud-arrow-up"></i>
                        <strong data-payment-file-label>Klik untuk pilih file</strong>
                        <span>Format PDF saja. Maksimal 5 MB.</span>
                    </label>
                    <input type="file" name="bukti_pembayaran" id="stockoutPaymentProof" class="stockout-payment-file" accept="application/pdf,.pdf" data-payment-file>
                </div>

                <div class="stockout-payment-actions">
                    <button type="submit" class="stockout-payment-submit" data-payment-submit>
                        <i class="fas fa-save" data-payment-submit-icon></i>
                        <span data-payment-submit-label>{{ $stockOut->bukti_pembayaran || $stockOut->kode_pembayaran ? 'Simpan ' : 'Simpan Bukti' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </section>
@endif

<section class="card stockout-preview-card" aria-labelledby="transactionPreviewTitle">
    <div class="card-header stockout-preview-header">
        <div>
            <span class="stockout-preview-kicker">Preview Sebelum Disimpan</span>
            <h3 id="transactionPreviewTitle" class="card-title">Detail Transaksi</h3>
        </div>
        <span class="stockout-preview-status"><i class="fas fa-eye"></i> Draft</span>
    </div>
    <div class="card-body">
        <div class="stockout-preview-meta">
            <div><span>Jenis Keluar</span><strong id="previewJenis">{{ $stockOut->jenis === 'DO' ? 'DO' : ucfirst($stockOut->jenis) }}</strong></div>
            <div id="previewCustomerWrap"><span>Customer</span><strong id="previewCustomer">-</strong></div>
            <div><span>PIC Penjualan</span><strong id="previewPic">-</strong></div>
            <div id="previewJenisPembayaranWrap"><span>Jenis Pembayaran</span><strong id="previewJenisPembayaran">-</strong></div>
            <div id="previewNomorTeleponWrap"><span>Nomor Telpon</span><strong id="previewNomorTelepon">-</strong></div>
            <div id="previewNomorSpkWrap" hidden><span>Nomor SPK</span><strong id="previewNomorSpk">-</strong></div>
            <div id="previewNomorImWrap" hidden><span>Nomor IM</span><strong id="previewNomorIm">-</strong></div>
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
                <div id="previewDiscountRow"><span id="previewDiscountLabel">Discount 0%</span><strong id="previewDiscountAmount">Rp 0</strong></div>
                <div id="previewPpnRow"><span>PPN 11%</span><strong id="previewPpnAmount">Rp 0</strong></div>
                <div class="preview-grand-total"><span>Total Keseluruhan</span><strong id="previewTotal">Rp 0</strong></div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
@include('stockout.partial.form-styles')
<style>
    .stockout-edit-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .stockout-edit-kicker {
        display: block;
        color: rgba(244, 247, 250, 0.55);
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .stockout-edit-actions {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        gap: 0.5rem;
    }

    .stockout-edit-note {
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        margin-bottom: 1.15rem;
        padding: 0.65rem 0.8rem;
        border-left: 3px solid #159fbe;
        background: rgba(21, 159, 190, 0.12);
        color: rgba(244, 247, 250, 0.78);
        font-size: 0.82rem;
    }

    .stockout-edit-note i {
        margin-top: 0.15rem;
        color: #69d8ed;
    }

    /* ===== Bukti Payment ===== */
    .stockout-payment-card {
        margin-top: 1.25rem;
    }

    .stockout-payment-header {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        border-top: 3px solid #1fa85c;
    }

    .stockout-payment-icon {
        display: grid;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 50%;
        background: linear-gradient(160deg, #a6f7c4 0%, #1fa85c 45%, #0a5c31 100%);
        color: #ffffff;
        font-size: 1.15rem;
    }

    .stockout-payment-kicker {
        display: block;
        color: #7df0a8;
        font-size: 0.66rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .stockout-payment-field {
        margin-bottom: 1rem;
    }

    .stockout-payment-field > label {
        display: block;
        margin-bottom: 0.35rem;
        color: rgba(244, 247, 250, 0.78);
        font-size: 0.76rem;
        font-weight: 600;
    }

    .stockout-payment-field > label i {
        margin-right: 0.3rem;
        color: #7df0a8;
    }

    .stockout-payment-field .form-control {
        height: 40px;
        border-color: rgba(31, 168, 92, 0.42);
        background: rgba(8, 14, 24, 0.72);
        color: #f4f7fa;
    }

    .stockout-payment-field .form-control:focus {
        border-color: #1fa85c;
        background: rgba(8, 14, 24, 0.9);
        color: #ffffff;
        box-shadow: 0 0 0 0.18rem rgba(31, 168, 92, 0.22);
    }

    .stockout-payment-file {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
    }

    .stockout-payment-dropzone {
        display: grid;
        margin: 0;
        padding: 1.1rem 0.75rem;
        place-items: center;
        border: 1px dashed rgba(125, 240, 168, 0.45);
        border-radius: 8px;
        background: linear-gradient(180deg, rgba(31, 168, 92, 0.14), rgba(31, 168, 92, 0.03));
        color: rgba(244, 247, 250, 0.72);
        text-align: center;
        cursor: pointer;
        transition: border-color 0.18s ease, background 0.18s ease, transform 0.18s ease;
    }

    .stockout-payment-dropzone:hover {
        border-color: #7df0a8;
        background: linear-gradient(180deg, rgba(31, 168, 92, 0.24), rgba(31, 168, 92, 0.06));
        transform: translateY(-1px);
    }

    .stockout-payment-dropzone i {
        margin-bottom: 0.35rem;
        color: #7df0a8;
        font-size: 1.4rem;
    }

    .stockout-payment-dropzone strong {
        color: #ffffff;
        font-size: 0.86rem;
    }

    .stockout-payment-dropzone span {
        color: rgba(244, 247, 250, 0.55);
        font-size: 0.72rem;
    }

    .stockout-payment-status {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        margin-bottom: 1rem;
        padding: 0.6rem 0.75rem;
        border: 1px solid rgba(111, 232, 160, 0.34);
        border-radius: 6px;
        background: linear-gradient(145deg, rgba(23, 167, 90, 0.24), rgba(4, 106, 51, 0.16));
    }

    .stockout-payment-status > i {
        color: #6fe8a0;
        font-size: 1.05rem;
    }

    .stockout-payment-status > div {
        display: grid;
        min-width: 0;
        flex: 1 1 auto;
    }

    .stockout-payment-status strong {
        color: #ffffff;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .stockout-payment-status span {
        overflow: hidden;
        color: rgba(244, 247, 250, 0.82);
        font-size: 0.82rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .stockout-payment-status-link {
        flex: 0 0 auto;
        padding: 0.3rem 0.6rem;
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 5px;
        background: linear-gradient(180deg, #ffe477 0%, #e7a900 48%, #a76500 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45), 0 2px 6px rgba(0, 0, 0, 0.3);
        color: #271900 !important;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .stockout-payment-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.6rem;
        margin-top: 0.5rem;
        padding-top: 0.9rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .stockout-payment-submit {
        position: relative;
        display: inline-flex;
        overflow: hidden;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.5rem 1.1rem;
        border: 1px solid rgba(125, 240, 168, 0.4) !important;
        border-radius: 6px;
        background: linear-gradient(180deg, #7df0a8 0%, #1fa85c 48%, #0a6636 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5), 0 4px 12px rgba(10, 102, 54, 0.5);
        color: #ffffff !important;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .stockout-payment-submit:hover {
        filter: brightness(1.12) saturate(1.08);
        color: #ffffff !important;
    }

    @media (max-width: 767.98px) {
        .stockout-edit-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .stockout-payment-actions {
            flex-direction: column-reverse;
        }

        .stockout-payment-submit {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
@include('stockout.partial.form-scripts')
<script>
    (function () {
        const fileInput = document.querySelector('[data-payment-file]');
        const fileLabel = document.querySelector('[data-payment-file-label]');

        if (fileInput && fileLabel) {
            fileInput.addEventListener('change', function () {
                fileLabel.textContent = this.files[0]?.name || 'Klik untuk memilih file';
            });
        }
    })();
</script>
@endpush
