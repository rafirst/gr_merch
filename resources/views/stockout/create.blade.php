@extends('layouts.app')
@section('title', 'Input Barang Keluar')

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
    $isRetailNonKtpSale = old('jenis', 'penjualan') === 'penjualan' && old('discount') === 'retail_non_ktp';
@endphp

<div class="card stockout-form-card">
    <div class="card-body">
        <form action="{{ route('stockout.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <section class="stockout-form-section" aria-labelledby="transactionDataTitle">
                <h3 class="stockout-form-section-title" id="transactionDataTitle">Data Transaksi</h3>
                <div class="stockout-transaction-layout" id="stockoutTransactionLayout">
                <div class="form-row stockout-transaction-row">
                    <div class="form-group">
                        <label for="jenis">Jenis Keluar</label>
                        <select name="jenis" id="jenis" class="form-control" required>
                            <option value="penjualan" @selected(old('jenis', 'penjualan') === 'penjualan')>Penjualan</option>
                            <option value="DO" @selected(old('jenis') === 'DO')>DO</option>
                            <option value="request" @selected(old('jenis') === 'request')>Request</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="picPenjualan">PIC</label>
                        <input type="text" name="pic_penjualan" id="picPenjualan" class="form-control" value="{{ old('pic_penjualan') }}" maxlength="255">
                    </div>
                </div>

                <div class="form-row stockout-transaction-reference-row">
                    <div class="form-group col-md-3" id="nomorSpkWrap" hidden>
                        <label for="nomorSpk">Nomor SPK</label>
                        <input type="text" name="nomor_spk" id="nomorSpk" class="form-control" value="{{ old('nomor_spk') }}" maxlength="100">
                    </div>
                    <div class="form-group col-md-3" id="nomorImWrap" hidden>
                        <label for="nomorIm">Nomor IM</label>
                        <input type="text" name="nomor_im" id="nomorIm" class="form-control" value="{{ old('nomor_im') }}" maxlength="100">
                    </div>
                </div>

            <div id="itemRows">
                <div class="form-row item-row">
                    <div class="form-group col-md-5 item-select-group">
                        <label>Item</label>
                        <div class="stockout-item-search-wrapper">
                            <input type="hidden" name="item_id[]" class="item-id-input">
                            <input type="text" class="form-control item-search-input" placeholder="Ketik kode atau nama item..." autocomplete="off" required>
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
                        <input type="number" name="jumlah[]" class="form-control quantity-input" min="1" required>
                    </div>
                    <div class="form-group col-md-2 harga-jual-wrap price-group">
                        <label>Harga Jual (Rp)</label>
                       <input type="text" class="form-control price-display" readonly>
                        <input type="hidden" name="harga_jual[]" class="price-input">
                    </div>
                    <div class="form-group col-md-2 total-wrap total-group">
                        <label>Total Harga (Rp)</label>
                        <input type="text" class="form-control total-display" readonly>
                        <input type="hidden" class="total-input">
                    </div>
                    <div class="form-group col-md-1 d-flex align-items-end remove-item-group">
                        <button type="button" class="btn btn-danger remove-item" title="Hapus item" disabled><i class="fas fa-minus"></i></button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary mb-3" id="addItem"><i class="fas fa-plus"></i> Tambah Barang</button>
                </div>
            <div class="form-row stockout-meta-row">
                <div class="form-group col-md-3" id="discountWrap">
                    <label>Discount</label>
                    <select name="discount" id="discount" class="form-control" required>
                        <option value="">- Pilih Discount -</option>
                        <option value="member" @selected(old('discount') === 'member')>TAG Member</option>
                        <option value="retail" @selected(old('discount') === 'retail')>Retail - KTP</option>
                        <option value="retail_non_ktp" @selected(old('discount') === 'retail_non_ktp')>Retail - Non KTP</option>
                    </select>
                </div>
                <div class="form-group col-md-3" id="jenisPembayaranWrap">
                    <label for="jenisPembayaran">Jenis Pembayaran</label>
                    <select name="jenis_pembayaran" id="jenisPembayaran" class="form-control" required>
                        <option value="">- Pilih Jenis Pembayaran -</option>
                        <option value="qris" @selected(old('jenis_pembayaran') === 'qris')>QRIS</option>
                        <option value="transfer" @selected(old('jenis_pembayaran') === 'transfer')>Transfer{{ $transferAccount ? ' — '.$transferAccount : '' }}</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" readonly required>
                </div>
                <div class="form-group col-md-3" id="paketBundlingWrap" hidden>
                    <label for="paketBundling">Paket Bundling</label>
                    <select name="paket_bundling" id="paketBundling" class="form-control">
                        <option value="">- Pilih Paket Bundling -</option>
                        <option value="paket_a" @selected(old('paket_bundling') === 'paket_a')>Paket A [ 1x Racing Cap + 1x Umbrella ]</option>
                        <option value="paket_b" @selected(old('paket_bundling') === 'paket_b')>Paket B [ 1x T-Shirt + 1x Tumbler ]</option>
                        <option value="paket_c" @selected(old('paket_bundling') === 'paket_c')>Paket C [ 1x Softshell Jacket + 1x Mechanic ]</option>
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
            </section>

            <section class="stockout-form-section" id="customerSection" aria-labelledby="customerDataTitle">
                <h3 class="stockout-form-section-title" id="customerDataTitle">Data Customer</h3>
                <div class="form-row stockout-customer-row">
                    <div class="form-group">
                        <label for="namaCustomer">Nama Customer <span class="required-indicator">*</span></label>
                        <input type="text" name="nama_customer" id="namaCustomer" class="form-control" value="{{ old('nama_customer') }}" maxlength="255" required>
                    </div>
                    <div class="form-group" id="nikKtpWrap">
                        <label for="nikKtp" id="nikKtpLabel">NIK KTP <span class="required-indicator" id="nikKtpRequiredIndicator" @if($isRetailNonKtpSale) hidden @endif>*</span></label>
                        <input type="text" name="nik_ktp" id="nikKtp" class="form-control" value="{{ $isRetailNonKtpSale ? '' : old('nik_ktp') }}" minlength="16" maxlength="16" inputmode="numeric" pattern="[0-9]{16}" @readonly($isRetailNonKtpSale) @required(! $isRetailNonKtpSale)>
                    </div>
                    <div class="form-group" id="jabatanWrap" hidden>
                        <label for="jabatan">Jabatan <span class="required-indicator">*</span></label>
                        <input type="text" name="jabatan" id="jabatan" class="form-control" value="{{ old('jabatan') }}" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label for="nomorTelepon">No. Telepon <span class="required-indicator" id="nomorTeleponRequiredIndicator" @if($isRetailNonKtpSale) hidden @endif>*</span></label>
                        <input type="tel" name="nomor_telepon" id="nomorTelepon" class="form-control" value="{{ $isRetailNonKtpSale ? '' : old('nomor_telepon') }}" maxlength="30" inputmode="numeric" pattern="[0-9]*" @readonly($isRetailNonKtpSale) @required(! $isRetailNonKtpSale)>
                    </div>
                    <div class="form-group stockout-customer-address">
                        <label for="alamatCustomer">Alamat <span class="required-indicator" id="alamatCustomerRequiredIndicator" @if($isRetailNonKtpSale) hidden @endif>*</span></label>
                        <textarea name="alamat_customer" id="alamatCustomer" class="form-control" rows="2" maxlength="2000" @readonly($isRetailNonKtpSale) @required(! $isRetailNonKtpSale)>{{ $isRetailNonKtpSale ? '' : old('alamat_customer') }}</textarea>
                    </div>
                </div>
            </section>

            <div class="stockout-form-actions">
                <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('stockout.index') }}" class="btn btn-secondary">Batal</a>
            </div>
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
@endpush

@push('scripts')
@include('stockout.partial.form-scripts')
@endpush
