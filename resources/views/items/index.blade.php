@extends('layouts.app')
@section('title', 'Data Items')

@section('content')
<div class="card">
    <div class="card-header items-toolbar">
        <form class="form-inline items-filter-form pt-2" method="GET">
            <input type="text" name="search" class="form-control mr-2" placeholder="Cari nama/kode item" value="{{ request('search') }}">
            @if(auth()->user()->canViewAllData())
                <select name="cabang_id" class="form-control mr-2">
                    <option value="">Semua Cabang</option>
                    @foreach($cabangs as $c)
                        <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama_cabang }}</option>
                    @endforeach
                </select>
            @endif
            <button class="btn btn-secondary items-glossy"><i class="fas fa-search"></i></button>
        </form>
        <div class="items-toolbar-actions">
            <a href="{{ route('items.index') }}" class="btn btn-secondary items-glossy" title="Reset filter" aria-label="Reset filter">
                <i class="fas fa-undo-alt"></i>
            </a>
            @if(auth()->user()->canViewAllData())
                <button type="button" class="btn btn-success items-glossy" id="itemsExportButton" title="Export item" aria-label="Pilih format ekspor" aria-haspopup="dialog" aria-controls="itemsExportModal">
                    <i class="fas fa-file-pdf"></i>
                </button>
            @else
                <a href="{{ route('items.export') }}" class="btn btn-success items-glossy" title="Export item" aria-label="Export item">
                    <i class="fas fa-file-pdf"></i>
                </a>
            @endif
            @if(auth()->user()->isAdminHo())
                <a href="{{ route('items.import.form') }}" class="btn btn-info items-glossy" title="Import item" aria-label="Import item">
                    <i class="fas fa-file-import"></i>
                </a>
                <a href="{{ route('items.create') }}" class="btn btn-primary items-glossy" title="Tambah item" aria-label="Tambah item">
                    <i class="fas fa-plus"></i>
                </a>
            @endif
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0 items-table {{ auth()->user()->canViewAllData() ? 'items-table-admin' : 'items-table-branch' }}">
            <thead>
                <tr>
                    <th>Kode</th><th>Nama Item</th><th>Kategori</th><th>Stok</th>
                    @if(auth()->user()->canViewAllData())
                        <th>Cabang</th>
                    @endif
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->kode_items }}</td>
                        <td>{{ $item->nama_items }}</td>
                        <td>{{ $item->kategori ?? '-' }}</td>
                        <td>{{ number_format((int) $item->stok_items, 0, ',', '.') }}</td>
                        @if(auth()->user()->canViewAllData())
                            <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
                        @endif
                        <td class="items-actions-cell">
                            <a href="{{ route('items.show', $item) }}" class="btn btn-sm btn-info items-glossy" title="Lihat detail" aria-label="Lihat detail item">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(auth()->user()->isAdminHo())
                                <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-warning items-glossy" title="Edit item" aria-label="Edit {{ $item->nama_items }}"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline items-delete-form" data-item="{{ $item->nama_items }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger items-glossy" title="Hapus item" aria-label="Hapus {{ $item->nama_items }}"><i class="fas fa-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->canViewAllData() ? 7 : 6 }}" class="text-center text-muted">Belum ada data item.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer items-pagination">
        <span class="items-pagination-summary">
            Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} results
        </span>
        @if($items->hasPages())
            <nav class="items-pagination-controls" role="navigation" aria-label="Navigasi halaman items">
                @if($items->onFirstPage())
                    <span class="items-pagination-arrow is-disabled" aria-disabled="true" aria-label="Halaman sebelumnya">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </span>
                @else
                    <a href="{{ $items->previousPageUrl() }}" class="items-pagination-arrow" rel="prev" aria-label="Halaman sebelumnya">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </a>
                @endif
                <span class="items-pagination-counter" aria-current="page">
                    <span class="items-pagination-current">{{ str_pad((string) $items->currentPage(), 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="items-pagination-divider">/</span>
                    <span class="items-pagination-total">{{ str_pad((string) $items->lastPage(), 2, '0', STR_PAD_LEFT) }}</span>
                </span>
                @if($items->hasMorePages())
                    <a href="{{ $items->nextPageUrl() }}" class="items-pagination-arrow" rel="next" aria-label="Halaman berikutnya">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </a>
                @else
                    <span class="items-pagination-arrow is-disabled" aria-disabled="true" aria-label="Halaman berikutnya">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </span>
                @endif
            </nav>
        @endif
    </div>
</div>
@endsection

@if(auth()->user()->canViewAllData())
    @include('components.export-format-modal', [
        'modalId' => 'itemsExportModal',
        'label' => 'Data Items',
        'pdfUrl' => route('items.export'),
        'excelUrl' => route('items.export.excel'),
    ])
@endif

<div class="items-confirm-modal" id="itemsConfirmModal" aria-hidden="true">
    <div class="items-confirm-backdrop" data-items-confirm-cancel></div>
    <section class="items-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="itemsConfirmTitle">
        <div class="items-confirm-icon"><i class="fas fa-trash-alt"></i></div>
        <span class="items-confirm-kicker">Data Items</span>
        <h3 id="itemsConfirmTitle">Hapus item?</h3>
        <p id="itemsConfirmMessage"></p>
        <div class="items-confirm-actions">
            <button type="button" class="items-confirm-cancel" data-items-confirm-cancel>Batal</button>
            <button type="button" class="items-confirm-submit" id="itemsConfirmSubmit"><i class="fas fa-trash-alt"></i> Ya, Hapus</button>
        </div>
    </section>
</div>

@push('styles')
<style>
    .items-toolbar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: nowrap;
    }

    .items-filter-form,
    .items-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        height: 38px;
    }

    .items-toolbar-actions {
        order: 2;
        flex: 0 0 auto;
        margin-left: auto;
    }

    .items-filter-form {
        order: 1;
        min-width: 0;
        flex: 1 1 auto;
    }

    .items-filter-form input[name="search"] {
        min-width: 180px;
        flex: 1 1 auto;
    }

    .items-filter-form select[name="cabang_id"] {
        width: 220px;
        flex: 0 0 220px;
    }

    .items-filter-form .form-control,
    .items-filter-form .custom-select {
        margin-right: 0 !important;
    }

    .items-filter-form .items-glossy {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        margin: 0;
    }

    .items-toolbar-actions .btn {
        width: 38px;
        min-width: 38px;
        height: 38px;
        flex: 0 0 38px;
        margin: 0;
    }

    .items-glossy {
        position: relative;
        display: inline-flex;
        overflow: hidden;
        width: 31px;
        height: 31px;
        align-items: center;
        justify-content: center;
        margin: 0 0.1rem;
        padding: 0;
        border-color: rgba(255, 255, 255, 0.24) !important;
        border: 1px solid rgba(255, 255, 255, 0.24) !important;
        color: #ffffff !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.42), 0 4px 10px rgba(0, 0, 0, 0.25);
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.28);
        transition: filter 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
        line-height: 1;
        vertical-align: middle;
    }

    .items-glossy::before {
        position: absolute;
        top: 0;
        right: 8%;
        left: 8%;
        height: 44%;
        border-radius: 0 0 50% 50%;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.38), rgba(255, 255, 255, 0));
        content: '';
        pointer-events: none;
    }

    .items-glossy:hover,
    .items-glossy:focus {
        filter: brightness(1.12) saturate(1.08);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55), 0 6px 14px rgba(0, 0, 0, 0.32);
        transform: translateY(-1px);
    }

    .items-glossy.btn-secondary {
        background: linear-gradient(145deg, #aeb9c4 0%, #687887 48%, #465563 100%);
    }

    .items-glossy.btn-success {
        background: linear-gradient(145deg, #55d889 0%, #18a957 48%, #087c3b 100%);
    }

    .items-glossy.btn-info {
        background: linear-gradient(145deg, #5fdff2 0%, #0fa5bd 48%, #08788f 100%);
    }

    .items-glossy.btn-primary {
        border-color: #f20b18 !important;
        background: linear-gradient(90deg, #f20b18 0%, #b00813 42%, #5b050b 100%) !important;
    }

    .items-glossy.btn-warning {
        background: linear-gradient(145deg, #ffe87a 0%, #f8c400 48%, #d18d00 100%);
        color: #17212b;
        text-shadow: 0 1px 1px rgba(255, 255, 255, 0.35);
    }

    .items-glossy.btn-danger {
        background: linear-gradient(145deg, #ff7180 0%, #ed3348 48%, #b6122b 100%);
    }

    .items-table {
        width: 100%;
        table-layout: fixed;
    }

    .items-table th,
    .items-table td {
        padding: 0.55rem 0.6rem;
        font-size: 0.82rem;
        line-height: 1.3;
        vertical-align: middle;
    }

    .items-table th {
        white-space: nowrap;
    }

    .items-table-admin th:nth-child(1),
    .items-table-admin td:nth-child(1) {
        width: 15%;
    }

    .items-table-admin th:nth-child(2),
    .items-table-admin td:nth-child(2) {
        width: 26%;
    }

    .items-table-admin th:nth-child(3),
    .items-table-admin td:nth-child(3) {
        width: 10%;
    }

    .items-table-admin th:nth-child(4),
    .items-table-admin td:nth-child(4) {
        width: 13%;
    }

    .items-table-admin th:nth-child(5),
    .items-table-admin td:nth-child(5) {
        width: 22%;
    }

    .items-table-admin th:nth-child(6),
    .items-table-admin td:nth-child(6) {
        width: 14%;
    }

    .items-table-branch th:nth-child(1),
    .items-table-branch td:nth-child(1) {
        width: 20%;
    }

    .items-table-branch th:nth-child(2),
    .items-table-branch td:nth-child(2) {
        width: 35%;
    }

    .items-table-branch th:nth-child(3),
    .items-table-branch td:nth-child(3) {
        width: 15%;
    }

    .items-table-branch th:nth-child(4),
    .items-table-branch td:nth-child(4) {
        width: 15%;
    }

    .items-table-branch th:nth-child(5),
    .items-table-branch td:nth-child(5) {
        width: 15%;
    }

    .items-table td:nth-child(1),
    .items-table td:nth-child(3),
    .items-table td:nth-child(4) {
        white-space: nowrap;
    }

    .items-table .items-actions-cell {
        white-space: nowrap;
    }

    .items-table .items-actions-cell .items-glossy {
        width: 27px;
        height: 27px;
        margin: 0 1px;
        font-size: 0.72rem;
        vertical-align: middle;
    }

    .items-table .items-delete-form {
        display: inline-block !important;
        margin: 0;
        vertical-align: middle;
    }

    .items-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border-top: 1px solid rgba(105, 143, 176, 0.16) !important;
        background: rgba(3, 10, 17, 0.88) !important;
    }

    .items-pagination-summary {
        margin: 0;
        color: rgba(244, 247, 250, 0.58);
        font-size: 0.75rem;
    }

    .items-pagination-controls {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin-left: auto;
    }

    .items-pagination-arrow {
        display: inline-flex;
        width: 1.8rem;
        height: 1.8rem;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(58, 78, 95, 0.58);
        border-radius: 50%;
        background: transparent;
        color: rgba(244, 247, 250, 0.78);
        font-size: 0.62rem;
        text-decoration: none;
        transition: border-color 0.18s ease, background 0.18s ease, color 0.18s ease;
    }

    .items-pagination-arrow:hover,
    .items-pagination-arrow:focus {
        border-color: rgba(247, 0, 8, 0.68);
        background: rgba(247, 0, 8, 0.12);
        color: #ffffff;
        outline: none;
        text-decoration: none;
    }

    .items-pagination-arrow.is-disabled {
        border-color: rgba(45, 61, 75, 0.4);
        color: rgba(244, 247, 250, 0.28);
        cursor: not-allowed;
    }

    .items-pagination-counter {
        display: inline-flex;
        min-width: 5.6rem;
        height: 1.8rem;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0 0.75rem;
        border: 1px solid rgba(58, 78, 95, 0.48);
        border-radius: 999px;
        background: linear-gradient(145deg, rgba(16, 28, 39, 0.94), rgba(5, 12, 20, 0.96));
        color: rgba(244, 247, 250, 0.58);
        font-size: 0.72rem;
        font-weight: 600;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    .items-pagination-current {
        color: #ff343e;
        font-weight: 700;
    }

    .items-pagination-divider {
        color: rgba(120, 148, 171, 0.55);
    }

    .items-pagination-total {
        color: rgba(244, 247, 250, 0.72);
    }

    @media (max-width: 575.98px) {
        .items-pagination {
            align-items: flex-start;
            flex-direction: column;
        }

        .items-pagination-controls {
            align-self: flex-end;
        }
    }

    .items-confirm-modal {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: grid;
        visibility: hidden;
        place-items: center;
        opacity: 0;
        transition: opacity 0.22s ease, visibility 0.22s ease;
    }

    .items-confirm-modal.is-visible {
        visibility: visible;
        opacity: 1;
    }

    .items-confirm-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 4, 9, 0.8);
        backdrop-filter: blur(4px);
    }

    .items-confirm-dialog {
        position: relative;
        width: min(430px, calc(100% - 2rem));
        padding: 2.2rem 1.5rem 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-top: 3px solid #ed3348;
        border-radius: 8px;
        background: linear-gradient(145deg, #172532, #050c14 72%);
        box-shadow: 0 20px 55px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.12);
        text-align: center;
        transform: translateY(18px) scale(0.94);
        transition: transform 0.28s cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    .items-confirm-modal.is-visible .items-confirm-dialog {
        transform: translateY(0) scale(1);
    }

    .items-confirm-icon {
        display: grid;
        width: 60px;
        height: 60px;
        margin: -3.95rem auto 0.8rem;
        place-items: center;
        border: 3px solid #172532;
        border-radius: 50%;
        background: linear-gradient(145deg, #ff7180, #b6122b);
        box-shadow: 0 5px 16px rgba(182, 18, 43, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.48);
        color: #ffffff;
        font-size: 1.35rem;
        animation: items-confirm-pulse 1.8s ease-in-out infinite;
    }

    .items-confirm-kicker {
        display: block;
        color: #ff6570;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .items-confirm-dialog h3 {
        margin: 0.35rem 0 0.55rem;
        color: #ffffff;
        font-size: 1.3rem;
    }

    .items-confirm-dialog p {
        min-height: 2.8rem;
        margin-bottom: 1.35rem;
        color: rgba(244, 247, 250, 0.72);
    }

    .items-confirm-actions {
        display: flex;
        justify-content: center;
        gap: 0.6rem;
    }

    .items-confirm-actions button {
        min-width: 112px;
        padding: 0.58rem 0.85rem;
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 4px;
        font-weight: 700;
        transition: filter 0.18s ease, transform 0.18s ease;
    }

    .items-confirm-actions button:hover,
    .items-confirm-actions button:focus {
        filter: brightness(1.12);
        transform: translateY(-1px);
    }

    .items-confirm-cancel {
        background: linear-gradient(145deg, #aeb9c4, #465563);
        color: #ffffff;
    }

    .items-confirm-submit {
        background: linear-gradient(145deg, #ff6570, #8d0d1d);
        color: #ffffff;
    }

    @keyframes items-confirm-pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.06); }
    }

    @media (max-width: 767.98px) {
        .items-toolbar {
            flex-wrap: wrap;
        }

        .items-filter-form,
        .items-toolbar-actions {
            width: 100%;
            flex-wrap: wrap;
        }

        .items-filter-form input,
        .items-filter-form select {
            min-width: 0;
            flex: 1 1 180px;
        }

        .items-toolbar-actions {
            order: 2;
            margin-left: 0;
        }

        .items-filter-form {
            order: 1;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('itemsConfirmModal');
        const message = document.getElementById('itemsConfirmMessage');
        const submitButton = document.getElementById('itemsConfirmSubmit');
        let activeForm = null;

        function closeModal() {
            modal.classList.remove('is-visible');
            modal.setAttribute('aria-hidden', 'true');
            activeForm = null;
        }

        document.querySelectorAll('.items-delete-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                activeForm = form;
                message.textContent = 'Hapus item "' + form.dataset.item + '"?';
                modal.classList.add('is-visible');
                modal.setAttribute('aria-hidden', 'false');
                submitButton.focus();
            });
        });

        submitButton.addEventListener('click', function () {
            if (activeForm) {
                activeForm.submit();
            }
        });

        modal.querySelectorAll('[data-items-confirm-cancel]').forEach(function (element) {
            element.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-visible')) {
                closeModal();
            }
        });
    });
</script>
@endpush
