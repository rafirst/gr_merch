@extends('layouts.app')
@section('title', 'Barang Keluar')

@section('content')
<div class="card">
    <div class="card-header stockout-card-header">
        <form class="form-inline stockout-filter-form" method="GET">
            <input type="text" name="search" class="form-control" placeholder="Cari customer/item" value="{{ request('search') }}">
            <select name="jenis" class="form-control mr-2" onchange="this.form.submit()">
                <option value="">Semua Jenis</option>
                <option value="penjualan" {{ request('jenis') == 'penjualan' ? 'selected' : '' }}>Penjualan</option>
                <option value="hadiah" {{ request('jenis') == 'hadiah' ? 'selected' : '' }}>Hadiah</option>
                <option value="request" {{ request('jenis') == 'request' ? 'selected' : '' }}>Request</option>
            </select>
            <select name="status" class="form-control mr-2" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="btn stockout-filter-button" title="Cari transaksi" aria-label="Cari transaksi"><i class="fas fa-search"></i></button>
            <a href="{{ route('stockout.index') }}" class="btn stockout-reset-button" title="Reset filter" aria-label="Reset filter"><i class="fas fa-undo-alt"></i></a>
        </form>
        <a href="{{ route('stockout.create') }}" class="btn stockout-create-button"><i class="fas fa-plus"></i> Items</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Item</th><th>Nama Customer</th><th>Cabang</th><th>Jenis</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($stockOuts as $row)
                    <tr>
                        <td>
                            {{ $row->item->nama_items ?? '-' }}
                            @if(($row->item_count ?? 1) > 1)
                                <span class="stockout-more-items">+{{ $row->item_count - 1 }}</span>
                            @endif
                        </td>
                        <td>{{ $row->nama_customer ?: '-' }}</td>
                        <td>{{ $row->cabang->nama_cabang ?? '-' }}</td>
                        <td><span class="badge stockout-badge stockout-type-badge">{{ ucfirst($row->jenis) }}</span></td>
                        <td>
                            @if($row->status == 'pending')
                                <span class="badge stockout-badge stockout-status-pending">Pending</span>
                            @elseif($row->status == 'approved')
                                <span class="badge stockout-badge stockout-status-approved">Approved</span>
                            @else
                                <span class="badge stockout-badge stockout-status-rejected">Rejected</span>
                            @endif
                        </td>
                        <td>
                            <div class="stockout-action-group">
                                <a href="{{ route('stockout.show', $row) }}" class="btn btn-sm stockout-action-button" title="Lihat detail" aria-label="Lihat detail transaksi">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($row->status !== 'rejected')
                                    <a href="{{ route('stockout.edit', $row) }}" class="btn btn-sm stockout-edit-button" title="Edit transaksi" aria-label="Edit transaksi">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                @endif
                                @if($row->status === 'approved')
                                    <button type="button" class="btn btn-sm stockout-payment-button{{ $row->bukti_pembayaran ? ' is-complete' : '' }}"
                                        title="Bukti pembayaran"
                                        aria-label="Input bukti pembayaran"
                                        data-toggle="modal" data-target="#paymentModal-{{ $row->id }}">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada data barang keluar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $stockOuts->links() }}</div>
</div>

@foreach($stockOuts as $row)
    @if($row->status === 'approved')
        <div class="modal fade stockout-payment-modal" id="paymentModal-{{ $row->id }}" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel-{{ $row->id }}" aria-hidden="true" data-backdrop="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                @php($isPaymentComplete = (bool) ($row->kode_pembayaran && $row->bukti_pembayaran))
                <div class="modal-content{{ $isPaymentComplete ? ' is-readonly' : '' }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel-{{ $row->id }}">
                            <i class="fas fa-money-bill-wave mr-2"></i>Bukti Pembayaran #{{ str_pad((string) $row->id, 6, '0', STR_PAD_LEFT) }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @if($isPaymentComplete)
                        <div class="modal-body">
                            <p class="stockout-payment-customer mb-3">
                                <strong>{{ $row->nama_customer ?: '-' }}</strong>
                                <span>{{ $row->item->nama_items ?? '-' }}</span>
                            </p>
                            <div class="stockout-payment-status">
                                <i class="fas fa-circle-check"></i>
                                <div>
                                    <strong>Kode pembayaran tersimpan</strong>
                                    <span>{{ $row->kode_pembayaran ?: '-' }}</span>
                                </div>
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($row->bukti_pembayaran) }}" target="_blank" rel="noopener" class="stockout-payment-status-link">
                                    <i class="fas fa-file-pdf"></i> Lihat Bukti
                                </a>
                            </div>
                            <div class="form-group">
                                <label>Kode Pembayaran</label>
                                <input type="text" class="form-control is-readonly" value="{{ $row->kode_pembayaran }}" readonly disabled tabindex="-1">
                            </div>
                            <div class="form-group mb-1">
                                <label>Bukti Pembayaran</label>
                                <div class="stockout-dropzone is-disabled" aria-disabled="true">
                                    <span class="stockout-dropzone-icon"><i class="fas fa-file-check"></i></span>
                                    <span class="stockout-dropzone-text">
                                        <strong>{{ basename($row->bukti_pembayaran) }}</strong>
                                        <small>File sudah tersimpan. Klik Edit untuk mengganti.</small>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <a href="{{ route('stockout.edit', $row) }}" class="btn stockout-modal-edit-button">
                                <i class="fas fa-pen mr-1"></i> Edit
                            </a>
                        </div>
                    @else
                    <form action="{{ route('stockout.bukti-pembayaran.update', $row) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_redirect_to" value="index">
                        <div class="modal-body">
                            <p class="stockout-payment-customer mb-3">
                                <strong>{{ $row->nama_customer ?: '-' }}</strong>
                                <span>{{ $row->item->nama_items ?? '-' }}</span>
                            </p>
                            @if($row->kode_pembayaran || $row->bukti_pembayaran)
                                <div class="stockout-payment-status">
                                    <i class="fas fa-circle-check"></i>
                                    <div>
                                        <strong>Kode pembayaran tersimpan</strong>
                                        <span>{{ $row->kode_pembayaran ?: '-' }}</span>
                                    </div>
                                    @if($row->bukti_pembayaran)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($row->bukti_pembayaran) }}" target="_blank" rel="noopener" class="stockout-payment-status-link">
                                            <i class="fas fa-file-pdf"></i> Lihat Bukti
                                        </a>
                                    @endif
                                </div>
                            @endif
                            <div class="form-group">
                                <label for="kode-{{ $row->id }}">Kode Pembayaran</label>
                                <input type="text" name="kode_pembayaran" id="kode-{{ $row->id }}" class="form-control" maxlength="100"
                                    placeholder="Contoh: BAY-2026-000123" value="{{ old('kode_pembayaran', $row->kode_pembayaran) }}" required>
                            </div>
                            <div class="form-group mb-1">
                                <label for="bukti-{{ $row->id }}">Upload Bukti Pembayaran</label>
                                <label class="stockout-dropzone" for="bukti-{{ $row->id }}">
                                    <input type="file" name="bukti_pembayaran" id="bukti-{{ $row->id }}" class="stockout-dropzone-input"
                                        accept="application/pdf,.pdf" data-name-target="filename-{{ $row->id }}" hidden>
                                    <span class="stockout-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                    <span class="stockout-dropzone-text">
                                        <strong id="filename-{{ $row->id }}">Klik untuk upload atau seret file ke sini</strong>
                                        <small>Format Hanya PDF. Maksimal 5 MB.</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn stockout-modal-save-button">
                                <i class="fas fa-save mr-1"></i> Simpan Bukti
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endforeach

<style>
    .stockout-card-header {
        display: flex !important;
        align-items: center;
        justify-content: flex-start !important;
        gap: 1rem;
    }

    .stockout-card-header form {
        margin-bottom: 0;
    }

    .stockout-filter-form {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        min-width: 0;
    }

    .stockout-filter-form input {
        width: 340px;
        min-width: 330px;
    }

    .stockout-filter-form select {
        width: 203px;
        min-width: 203px;
        flex: 0 0 203px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
    }

    .stockout-filter-form .form-control {
        margin-right: 0 !important;
    }

    .stockout-filter-button,
    .stockout-reset-button {
        display: inline-flex;
        width: 38px;
        height: 38px;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid rgba(255, 255, 255, 0.28) !important;
        border-radius: 4px;
        color: #ffffff !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.58), 0 4px 9px rgba(0, 0, 0, 0.28);
    }

    .stockout-filter-button,
    .stockout-reset-button {
        background: linear-gradient(145deg, #aeb9c4 0%, #687887 48%, #465563 100%);
    }

    .stockout-card-header .stockout-create-button {
        flex: 0 0 auto;
        margin-left: auto;
    }

    .stockout-create-button {
        border-color: #f20b18 !important;
        background: linear-gradient(90deg, #f20b18 0%, #b00813 42%, #5b050b 100%) !important;
        color: #ffffff !important;
    }

    .stockout-badge,
    .stockout-action-button {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.28) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.38), 0 2px 5px rgba(0, 0, 0, 0.28);
        color: #ffffff !important;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.42);
    }

    .stockout-badge::before,
    .stockout-action-button::before {
        position: absolute;
        top: 0;
        right: 12%;
        left: 12%;
        height: 42%;
        border-radius: inherit;
        background: rgba(255, 255, 255, 0.28);
        content: '';
        filter: blur(1px);
        pointer-events: none;
    }

    .stockout-type-badge {
        background: linear-gradient(180deg, #8795a5 0%, #596a7c 48%, #344554 100%) !important;
    }

    .stockout-status-pending {
        background: linear-gradient(180deg, #ffd45a 0%, #e49a08 48%, #a85b00 100%) !important;
    }

    .stockout-status-approved {
        background: linear-gradient(180deg, #54e47a 0%, #16a34a 48%, #08752f 100%) !important;
    }

    .stockout-status-rejected {
        background: linear-gradient(180deg, #ff6570 0%, #dc2638 48%, #8d0d1d 100%) !important;
    }

    .stockout-action-button {
        background: linear-gradient(180deg, #69d8ed 0%, #159fbe 48%, #08728c 100%) !important;
    }

    .stockout-edit-button {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.28) !important;
        background: linear-gradient(180deg, #ffd97a 0%, #e8a723 48%, #a76a00 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.42), 0 2px 5px rgba(0, 0, 0, 0.28);
        color: #271900 !important;
    }

    .stockout-edit-button::before {
        position: absolute;
        top: 0;
        right: 12%;
        left: 12%;
        height: 42%;
        border-radius: inherit;
        background: rgba(255, 255, 255, 0.3);
        content: '';
        filter: blur(1px);
        pointer-events: none;
    }

    .stockout-edit-button i {
        position: relative;
        z-index: 1;
    }

    .stockout-edit-button:hover,
    .stockout-edit-button:focus {
        filter: brightness(1.1);
        color: #271900 !important;
    }

    .stockout-payment-button {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.28) !important;
        background: linear-gradient(180deg, #7df0a8 0%, #1fa85c 48%, #0a6636 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.42), 0 2px 5px rgba(0, 0, 0, 0.28);
        color: #ffffff !important;
    }

    .stockout-payment-button::before {
        position: absolute;
        top: 0;
        right: 12%;
        left: 12%;
        height: 42%;
        border-radius: inherit;
        background: rgba(255, 255, 255, 0.32);
        content: '';
        filter: blur(1px);
        pointer-events: none;
    }

    .stockout-payment-button i {
        position: relative;
        z-index: 1;
    }

    .stockout-payment-button:hover,
    .stockout-payment-button:focus {
        filter: brightness(1.12) saturate(1.08);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6), 0 4px 9px rgba(0, 0, 0, 0.34);
        color: #ffffff !important;
    }

    .stockout-payment-button.is-complete {
        background: linear-gradient(180deg, #2fd383 0%, #0e8a4d 48%, #04432a 100%) !important;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.5), 0 0 0 2px rgba(47, 211, 131, 0.5), 0 2px 6px rgba(0, 0, 0, 0.3);
    }

    .stockout-more-items {
        display: inline-block;
        margin-left: 0.35rem;
        padding: 0.1rem 0.35rem;
        border-radius: 4px;
        background: #e30613;
        color: #ffffff;
        font-size: 0.72rem;
        font-weight: 700;
    }

    /* Tombol aksi tetap sejajar satu baris (lihat, edit, bukti bayar). */
    .stockout-action-group {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        white-space: nowrap;
    }

    .stockout-action-group .btn-sm {
        display: inline-flex;
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    /* ===== Payment popup: blur + centered, dark theme ===== */
    .stockout-payment-modal .modal-dialog {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100% - 2rem);
        margin: 1rem auto;
    }

    .modal-backdrop.show {
        opacity: 1 !important;
        background: rgba(2, 6, 12, 0.55);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .stockout-payment-modal .modal-content {
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 12px;
        background: linear-gradient(160deg, #142434 0%, #0a141f 55%, #060d15 100%);
        color: #f4f7fa;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.55);
    }

    .stockout-payment-modal .modal-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        align-items: center;
    }

    .stockout-payment-modal .modal-title {
        color: #ffffff;
        font-size: 1rem;
        font-weight: 700;
    }

    .stockout-payment-modal .modal-title i {
        color: #6fe8a0;
    }

    .stockout-payment-modal .close {
        color: #ffffff;
        opacity: 0.8;
        text-shadow: none;
    }

    .stockout-payment-modal .close:hover {
        opacity: 1;
        color: #ffffff;
    }

    .stockout-payment-customer {
        display: grid;
        gap: 0.15rem;
        margin-bottom: 1rem;
        padding: 0.65rem 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.04);
    }

    .stockout-payment-customer strong {
        color: #ffffff;
        font-size: 0.95rem;
    }

    .stockout-payment-customer span {
        color: rgba(244, 247, 250, 0.72);
        font-size: 0.82rem;
    }

    .stockout-payment-modal label {
        color: #ffffff;
        font-weight: 600;
    }

    .stockout-payment-modal .form-control {
        border-color: rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.06);
        color: #ffffff;
    }

    .stockout-payment-modal .form-control::placeholder {
        color: rgba(244, 247, 250, 0.5);
    }
    .stockout-payment-modal .form-control.is-readonly {
        border-color: rgba(255, 77, 79, 0.55);
        background: rgba(255, 255, 255, 0.03);
        color: #ffffff;
        box-shadow: 0 0 0 1px rgba(255, 77, 79, 0.25) inset;
        cursor: not-allowed;
        opacity: 1;
    }
    /* legacy card/header replaced by .stockout-pay-* above */
    /* Dropzone gelap sesuai referensi: dashed, center, simpel */
    .stockout-dropzone {
        display: grid;
        justify-items: center;
        gap: 0.3rem;
        width: 100%;
        margin: 0;
        padding: 1.1rem 1rem;
        border: 1px dashed rgba(148, 178, 205, 0.55);
        border-radius: 8px;
        background: rgba(3, 10, 17, 0.55);
        cursor: pointer;
        text-align: center;
        transition: border-color 0.2s ease, background 0.2s ease;
    }
    .stockout-dropzone:hover,
    .stockout-dropzone.is-dragover,
    .stockout-dropzone.has-file {
        border-color: rgba(111, 232, 160, 0.7);
        background: rgba(10, 26, 20, 0.65);
    }

    .stockout-dropzone.is-disabled {
        border-style: solid;
        border-color: rgba(111, 232, 160, 0.4);
        background: rgba(10, 26, 20, 0.45);
        cursor: not-allowed;
    }

    .stockout-dropzone-input { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }

    .stockout-dropzone-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: rgba(148, 178, 205, 0.16);
        color: #c9d8e6;
        font-size: 0.95rem;
    }

    .stockout-dropzone-text { display: grid; gap: 0.2rem; min-width: 0; max-width: 100%; }

    .stockout-dropzone-text strong {
        overflow: hidden;
        color: #ffffff;
        font-size: 0.86rem;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .stockout-dropzone-text small { color: rgba(244, 247, 250, 0.6); font-size: 0.76rem; }

    .stockout-payment-status {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        margin-bottom: 1rem;
        padding: 0.6rem 0.75rem;
        border: 1px solid rgba(111, 232, 160, 0.34);
        border-radius: 8px;
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
        color: #271900 !important;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .stockout-payment-modal .modal-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.12);
    }

    .stockout-modal-save-button {
        border: 1px solid rgba(125, 240, 168, 0.4) !important;
        background: linear-gradient(180deg, #2fd383 0%, #1fa85c 48%, #0a6636 100%) !important;
        color: #ffffff !important;
        font-weight: 700;
    }

    .stockout-modal-save-button:hover {
        filter: brightness(1.12);
        color: #ffffff !important;
    }

    .stockout-modal-edit-button {
        border: 1px solid rgba(255, 255, 255, 0.28) !important;
        background: linear-gradient(180deg, #ffd97a 0%, #e8a723 48%, #a76a00 100%) !important;
        color: #271900 !important;
        font-weight: 700;
    }

    .stockout-modal-edit-button:hover {
        filter: brightness(1.1);
        color: #271900 !important;
    }

    @media (max-width: 767.98px) {
        .stockout-card-header {
            align-items: stretch;
            flex-wrap: wrap;
        }

        .stockout-filter-form {
            width: 100%;
            flex-wrap: wrap;
        }

        .stockout-filter-form input {
            flex: 1 1 180px;
        }

        .stockout-card-header .stockout-create-button {
            margin-left: 0;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.stockout-dropzone-input').forEach(function (input) {
            var zone = input.closest('.stockout-dropzone');
            var target = input.getAttribute('data-name-target');
            var label = target ? document.getElementById(target) : null;
            var fallback = 'Klik untuk upload atau seret file ke sini';

            input.addEventListener('change', function () {
                var name = input.files && input.files.length ? input.files[0].name : fallback;
                if (label) {
                    label.textContent = name;
                }
                if (zone) {
                    zone.classList.toggle('has-file', !!(input.files && input.files.length));
                }
            });

            if (zone) {
                ['dragenter', 'dragover'].forEach(function (evt) {
                    zone.addEventListener(evt, function (e) {
                        e.preventDefault();
                        zone.classList.add('is-dragover');
                    });
                });
                ['dragleave', 'drop'].forEach(function (evt) {
                    zone.addEventListener(evt, function (e) {
                        e.preventDefault();
                        zone.classList.remove('is-dragover');
                    });
                });
            }
        });
    });
</script>
@endsection
