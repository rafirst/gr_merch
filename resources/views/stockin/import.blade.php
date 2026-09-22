@extends('layouts.app')
@section('title', 'Import Barang Masuk')

@section('content')
<div class="card import-card">
    <div class="card-header import-card-header">
        <h3 class="card-title">Import Barang Masuk</h3>
    </div>
    <div class="card-body import-card-body">
        <section class="import-step">
            <div class="import-step-number">01</div>
            <div class="import-step-content">
                <h4>Siapkan File</h4>
                <p>Gunakan format kolom berikut pada baris pertama (header):</p>
                <div class="import-code-line">kode_items <span>|</span> jumlah <span>|</span> kode_cabang <span>|</span> sumber</div>
                <a href="{{ route('stockin.import.template') }}" class="btn btn-success import-template-button"><i class="fas fa-file-excel"></i> Download Template</a>
            </div>
        </section>

        <section class="import-step">
            <div class="import-step-number">02</div>
            <div class="import-step-content">
                <h4>Perhatikan Kode Cabang</h4>
                <p>Gunakan kode cabang sesuai dengan tujuan barang masuk.</p>
                <div class="branch-code-list">
                    <div class="branch-code"><strong>1</strong><span>THO</span></div>
                    <div class="branch-code"><strong>2</strong><span>PLG</span></div>
                    <div class="branch-code"><strong>3</strong><span>LLG</span></div>
                    <div class="branch-code"><strong>4</strong><span>TME</span></div>
                    <div class="branch-code"><strong>5</strong><span>PRB</span></div>
                    <div class="branch-code"><strong>6</strong><span>POL</span></div>
                </div>
                <div class="import-note"><i class="fas fa-info-circle"></i> Kode item harus sudah terdaftar pada cabang tujuan. Tanggal barang masuk akan diisi otomatis hari ini.</div>
            </div>
        </section>

        <section class="import-step import-upload-step">
            <div class="import-step-number">03</div>
            <div class="import-step-content">
                <h4>Upload File</h4>
                <p>Pilih file Excel atau CSV yang ingin diimport.</p>
                <form action="{{ route('stockin.import') }}" method="POST" enctype="multipart/form-data" class="import-form">
                    @csrf
                    <div class="import-upload-layout">
                        <label class="import-dropzone" for="stockin-import-file">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <strong>Drag &amp; drop file di sini</strong>
                            <span>atau</span>
                            <span class="import-browse-label">Pilih File</span>
                            <small>Format: .xlsx / .xls / .csv</small>
                        </label>
                        <input type="file" name="file" id="stockin-import-file" class="import-file-input" accept=".xlsx,.xls,.csv" required>
                        <div class="import-selected-file">
                            <i class="fas fa-file-excel"></i>
                            <div>
                                <strong id="stockin-file-name">Belum ada file dipilih</strong>
                                <span id="stockin-file-status">File siap diupload</span>
                            </div>
                            <i class="fas fa-check import-file-check"></i>
                        </div>
                    </div>
                    <div class="import-actions">
                        <a href="{{ route('stockin.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Import Data</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
    .import-card {
        max-width: 980px;
        margin: 0 auto;
    }

    .import-card-header {
        min-height: 48px !important;
        padding: 0.85rem 1rem 0.85rem 1.35rem !important;
    }

    .import-card-header .card-title {
        margin: 0;
        font-size: 1.08rem !important;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .import-card-body {
        padding: 0.4rem 1.2rem 1rem;
    }

    .import-step {
        display: flex;
        gap: 0.85rem;
        padding: 1rem 0;
        border-bottom: 1px solid rgba(105, 143, 176, 0.2);
    }

    .import-step:last-child {
        border-bottom: 0;
        padding-bottom: 0.3rem;
    }

    .import-step-number {
        display: flex;
        flex: 0 0 27px;
        align-items: center;
        justify-content: center;
        width: 27px;
        height: 27px;
        border-radius: 50%;
        background: #f70008;
        color: #ffffff;
        font-size: 0.72rem;
        font-weight: 700;
        box-shadow: 0 0 0 3px rgba(247, 0, 8, 0.11);
    }

    .import-step-content {
        min-width: 0;
        flex: 1;
    }

    .import-step h4 {
        margin: 0 0 0.2rem;
        color: #ffffff;
        font-size: 0.92rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .import-step p {
        margin: 0 0 0.55rem;
        color: rgba(244, 247, 250, 0.72);
        font-size: 0.78rem;
    }

    .import-code-line {
        overflow-x: auto;
        padding: 0.48rem 0.7rem;
        border: 1px solid rgba(105, 143, 176, 0.2);
        border-radius: 4px;
        background: rgba(10, 29, 45, 0.68);
        color: #d4e6f5;
        font-family: Consolas, monospace;
        font-size: 0.68rem;
        white-space: nowrap;
    }

    .import-code-line span {
        color: #f70008;
    }

    .import-template-button {
        margin-top: 0.65rem;
        padding: 0.32rem 0.65rem;
        font-size: 0.75rem;
    }

    .branch-code-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .branch-code {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 58px;
        min-height: 45px;
        border: 1px solid rgba(105, 143, 176, 0.25);
        border-radius: 4px;
        background: rgba(10, 29, 45, 0.75);
    }

    .branch-code strong {
        color: #ffffff;
        font-size: 0.76rem;
    }

    .branch-code span {
        color: rgba(244, 247, 250, 0.62);
        font-size: 0.63rem;
    }

    .import-note {
        margin-top: 0.55rem;
        color: rgba(244, 247, 250, 0.62);
        font-size: 0.72rem;
    }

    .import-note i {
        margin-right: 0.25rem;
        color: #63b8f2;
    }

    .import-upload-layout {
        display: grid;
        grid-template-columns: minmax(230px, 1fr) minmax(230px, 1fr);
        gap: 0.8rem;
    }

    .import-dropzone {
        display: flex;
        min-height: 116px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.2rem;
        margin: 0;
        padding: 0.8rem;
        border: 1px dashed rgba(117, 161, 194, 0.65);
        border-radius: 4px;
        color: rgba(244, 247, 250, 0.65);
        cursor: pointer;
        transition: border-color 0.18s ease, background 0.18s ease;
    }

    .import-dropzone:hover,
    .import-dropzone:focus-within {
        border-color: #63b8f2;
        background: rgba(33, 84, 120, 0.18);
    }

    .import-dropzone > i {
        color: #b9d8ee;
        font-size: 1.2rem;
    }

    .import-dropzone strong {
        color: #c5d4df;
        font-size: 0.7rem;
    }

    .import-dropzone span,
    .import-dropzone small {
        font-size: 0.66rem;
    }

    .import-browse-label {
        padding: 0.18rem 0.5rem;
        border-radius: 3px;
        background: #087fe5;
        color: #ffffff;
        font-weight: 700;
    }

    .import-file-input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .import-selected-file {
        display: flex;
        min-height: 116px;
        align-items: center;
        gap: 0.65rem;
        padding: 0.8rem;
        border: 1px solid rgba(105, 143, 176, 0.25);
        border-radius: 4px;
        background: rgba(10, 29, 45, 0.68);
    }

    .import-selected-file > i:first-child {
        color: #51d47c;
        font-size: 1.35rem;
    }

    .import-selected-file strong,
    .import-selected-file span {
        display: block;
        overflow: hidden;
        max-width: 250px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .import-selected-file strong {
        color: #dce8f1;
        font-size: 0.76rem;
    }

    .import-selected-file span {
        margin-top: 0.18rem;
        color: rgba(244, 247, 250, 0.48);
        font-size: 0.65rem;
    }

    .import-file-check {
        margin-left: auto;
        color: rgba(244, 247, 250, 0.35);
    }

    .import-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.45rem;
        margin-top: 0.7rem;
    }

    .import-actions .btn,
    .import-template-button {
        font-size: 0.76rem;
    }

    @media (max-width: 767.98px) {
        .import-card-body {
            padding-right: 0.85rem;
            padding-left: 0.85rem;
        }

        .import-upload-layout {
            grid-template-columns: 1fr;
        }

        .import-selected-file strong,
        .import-selected-file span {
            max-width: calc(100vw - 150px);
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('stockin-import-file')?.addEventListener('change', function () {
        const fileName = document.getElementById('stockin-file-name');
        const fileStatus = document.getElementById('stockin-file-status');
        const fileCheck = document.querySelector('.import-file-check');

        if (this.files[0]) {
            fileName.textContent = this.files[0].name;
            fileStatus.textContent = 'File siap diupload';
            fileCheck.style.color = '#51d47c';
        }
    });
</script>
@endpush