@extends('layouts.app')
@section('title', 'Barang Masuk')

@section('content')
<div class="card">
    <div class="card-header stockin-card-header">
        <form class="stockin-filter-form" method="GET">
            <input type="text" name="search" class="form-control" placeholder="Cari nama/kode item" value="{{ request('search') }}">
            @if(auth()->user()->isAdminHo())
                <select name="cabang_id" class="form-control">
                    <option value="">Semua Cabang</option>
                    @foreach($cabangs as $cabang)
                        <option value="{{ $cabang->id }}" {{ request('cabang_id') == $cabang->id ? 'selected' : '' }}>{{ $cabang->nama_cabang }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="btn stockin-filter-button" title="Cari stok" aria-label="Cari stok"><i class="fas fa-search"></i></button>
            <a href="{{ route('stockin.index') }}" class="btn stockin-reset-button" title="Reset filter" aria-label="Reset filter"><i class="fas fa-undo-alt"></i></a>
        </form>
        <div class="stockin-header-actions">
              <a href="{{ route('stockin.export') }}" class="btn btn-success stockin-toolbar-button stockin-export-button" title="Export barang masuk" aria-label="Export barang masuk"><i class="fas fa-file-excel"></i></a>
              <a href="{{ route('stockin.import.form') }}" class="btn btn-info stockin-toolbar-button stockin-import-button" title="Import barang masuk" aria-label="Import barang masuk"><i class="fas fa-file-import"></i></a>
              <a href="{{ route('stockin.create') }}" class="btn btn-primary stockin-toolbar-button stockin-create-button" title="Tambah barang masuk" aria-label="Tambah barang masuk"><i class="fas fa-plus"></i></a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Kode</th><th>Nama Item</th><th>Kategori</th><th>Cabang</th><th>Stok </th><th class="text-center">Aksi</th></tr></thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->kode_items }}</td>
                        <td>{{ $item->nama_items }}</td>
                        <td>{{ $item->kategori ?? '-' }}</td>
                        <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
                        <td><span class="badge {{ $item->isStokMenipis() ? 'badge-warning' : 'badge-success' }} stockin-quantity-badge">{{ $item->stok_items }}</span></td>
                        <td class="text-center stockin-actions-cell">
                            <a href="{{ route('stockin.show', $item->latestStockIn) }}" class="btn btn-sm stockin-action-button stockin-view-button" title="Lihat item" aria-label="Lihat detail item">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('stockin.edit', $item->latestStockIn) }}" class="btn btn-sm stockin-action-button stockin-edit-button" title="Edit barang masuk" aria-label="Edit barang masuk">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form action="{{ route('stockin.destroy', $item->latestStockIn) }}" method="POST" class="d-inline stockin-delete-form" data-item="{{ $item->nama_items }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm stockin-action-button stockin-delete-button" title="Hapus barang masuk" aria-label="Hapus barang masuk">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Belum ada data item.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $items->links() }}</div>
</div>
@endsection

<div class="stockin-confirm-modal" id="stockinConfirmModal" aria-hidden="true">
    <div class="stockin-confirm-backdrop" data-stockin-confirm-cancel></div>
    <section class="stockin-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="stockinConfirmTitle">
        <div class="stockin-confirm-icon"><i class="fas fa-trash-alt"></i></div>
        <span class="stockin-confirm-kicker">Barang Masuk</span>
        <h3 id="stockinConfirmTitle">Hapus transaksi?</h3>
        <p id="stockinConfirmMessage"></p>
        <div class="stockin-confirm-actions">
            <button type="button" class="stockin-confirm-cancel" data-stockin-confirm-cancel>Batal</button>
            <button type="button" class="stockin-confirm-submit" id="stockinConfirmSubmit"><i class="fas fa-trash-alt"></i> Ya, Hapus</button>
        </div>
    </section>
</div>

@push('styles')
<style>
    .stockin-card-header {
        display: flex !important;
        align-items: center;
        justify-content: space-between !important;
        gap: 1rem;
    }

    .stockin-card-header .card-title {
        margin-bottom: 0;
    }

    .stockin-card-header .stockin-input-button {
        flex: 0 0 auto;
    }

    .stockin-filter-form {
        display: flex;
        align-items: center;
        flex: 1 1 auto;
        gap: 0.5rem;
        min-width: 0;
        margin: 0;
    }

    .stockin-filter-form input {
        min-width: 200px;
        flex: 1 1 240px;
    }

    .stockin-filter-form select {
        width: 350px;
        flex: 0 0 240px;
    }

    .stockin-filter-button,
    .stockin-reset-button {
        position: relative;
        display: inline-flex;
        width: 38px;
        height: 38px;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.28) !important;
        border-radius: 4px;
        color: #ffffff !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.58), 0 4px 9px rgba(0, 0, 0, 0.28);
        transition: filter 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
    }

    .stockin-filter-button::before,
    .stockin-reset-button::before {
        position: absolute;
        top: 0;
        right: 8%;
        left: 8%;
        height: 48%;
        border-radius: 0 0 50% 50%;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0));
        content: '';
        pointer-events: none;
    }

    .stockin-filter-button i,
    .stockin-reset-button i {
        position: relative;
        z-index: 1;
    }

    .stockin-filter-button:hover,
    .stockin-filter-button:focus,
    .stockin-reset-button:hover,
    .stockin-reset-button:focus {
        filter: brightness(1.12) saturate(1.08);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7), 0 6px 13px rgba(0, 0, 0, 0.38);
        transform: translateY(-1px);
    }

    .stockin-filter-button {
        background: linear-gradient(145deg, #aeb9c4 0%, #687887 48%, #465563 100%);
    }

    .stockin-reset-button {
        background: linear-gradient(145deg, #aeb9c4 0%, #687887 48%, #465563 100%);
    }

    .stockin-header-actions {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        margin-left: auto;
    }

    .stockin-input-button {
        background: linear-gradient(90deg, #f20b18 0%, #b00813 42%, #5b050b 100%) !important;
        border-color: #f20b18 !important;
    }

        .stockin-toolbar-button {
            position: relative;
            display: inline-flex;
            width: 38px;
            height: 38px;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.32) !important;
            border-radius: 4px;
            color: #ffffff !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.62), 0 4px 9px rgba(0, 0, 0, 0.32);
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.28);
            transition: filter 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
        }

        .stockin-toolbar-button::before {
            position: absolute;
            top: 0;
            right: 8%;
            left: 8%;
            height: 48%;
            border-radius: 0 0 50% 50%;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.58), rgba(255, 255, 255, 0));
            content: '';
            pointer-events: none;
        }

        .stockin-toolbar-button i {
            position: relative;
            z-index: 1;
        }

        .stockin-toolbar-button:hover,
        .stockin-toolbar-button:focus {
            filter: brightness(1.12) saturate(1.08);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72), 0 6px 13px rgba(0, 0, 0, 0.4);
            transform: translateY(-1px);
        }

        .stockin-export-button {
            background: linear-gradient(145deg, #5be58a 0%, #18a957 48%, #087c3b 100%) !important;
        }

        .stockin-import-button {
            background: linear-gradient(145deg, #62dceb 0%, #0fa5bd 48%, #08788f 100%) !important;
        }

        .stockin-create-button {
            border-color: #f20b18 !important;
            background: linear-gradient(90deg, #f20b18 0%, #b00813 42%, #5b050b 100%) !important;
        }

    .stockin-quantity-badge {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.28);
        background: linear-gradient(145deg, #5be58a 0%, #19ae53 48%, #078138 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5), 0 2px 6px rgba(0, 0, 0, 0.28);
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
        display: inline-block;
        min-width: 42px;
        padding: 0.18rem 0.45rem;
        font-size: 14px;
        line-height: 1.2;
        vertical-align: middle;
    }

    .stockin-safe-badge,
    .stockin-low-badge {
        display: inline-block;
        min-width: 58px;
        padding: 0.22rem 0.45rem;
        border-radius: 3px;
        font-size: 0.72rem;
        text-align: center;
    }

    .stockin-safe-badge {
        background: rgba(25, 174, 83, 0.25);
        border: 1px solid rgba(81, 212, 124, 0.6);
        color: #7af0a1;
    }

    .stockin-low-badge {
        background: rgba(248, 196, 0, 0.2);
        border: 1px solid rgba(255, 214, 77, 0.7);
        color: #ffe477;
    }

    .stockin-quantity-badge::before {
        position: absolute;
        top: 0;
        right: 10%;
        left: 10%;
        height: 48%;
        border-radius: 0 0 50% 50%;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.46), rgba(255, 255, 255, 0));
        content: '';
        pointer-events: none;
    }

    .stockin-actions-cell {
        white-space: nowrap;
    }

    .stockin-action-button {
        position: relative;
        display: inline-flex;
        overflow: hidden;
        width: 31px;
        height: 31px;
        align-items: center;
        justify-content: center;
        margin: 0 0.1rem;
        padding: 0;
        border: 1px solid rgba(255, 255, 255, 0.24) !important;
        color: #ffffff !important;
        line-height: 1;
        vertical-align: middle;
    }

    .stockin-action-button::before {
        position: absolute;
        top: 0;
        right: 12%;
        left: 12%;
        height: 42%;
        border-radius: inherit;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.52), rgba(255, 255, 255, 0.08));
        content: '';
        filter: blur(0.5px);
        pointer-events: none;
    }

    .stockin-view-button {
        background: linear-gradient(180deg, #69d8ed 0%, #159fbe 48%, #08728c 100%) !important;
    }

    .stockin-edit-button {
        background: linear-gradient(180deg, #ffe477 0%, #e7a900 48%, #a76500 100%) !important;
        color: #271900 !important;
    }

        .stockin-add-button {
            background: linear-gradient(180deg, #ffe477 0%, #e7a900 48%, #a76500 100%) !important;
            color: #271900 !important;
        }

    .stockin-delete-button {
        background: linear-gradient(180deg, #ff6570 0%, #dc2638 48%, #8d0d1d 100%) !important;
    }

    .stockin-confirm-modal {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: grid;
        visibility: hidden;
        place-items: center;
        opacity: 0;
        transition: opacity 0.22s ease, visibility 0.22s ease;
    }

    .stockin-confirm-modal.is-visible {
        visibility: visible;
        opacity: 1;
    }

    .stockin-confirm-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 4, 9, 0.8);
        backdrop-filter: blur(4px);
    }

    .stockin-confirm-dialog {
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

    .stockin-confirm-modal.is-visible .stockin-confirm-dialog {
        transform: translateY(0) scale(1);
    }

    .stockin-confirm-icon {
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
        animation: stockin-confirm-pulse 1.8s ease-in-out infinite;
    }

    .stockin-confirm-kicker {
        display: block;
        color: #ff6570;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .stockin-confirm-dialog h3 {
        margin: 0.35rem 0 0.55rem;
        color: #ffffff;
        font-size: 1.3rem;
    }

    .stockin-confirm-dialog p {
        min-height: 2.8rem;
        margin-bottom: 1.35rem;
        color: rgba(244, 247, 250, 0.72);
    }

    .stockin-confirm-actions {
        display: flex;
        justify-content: center;
        gap: 0.6rem;
    }

    .stockin-confirm-actions button {
        min-width: 112px;
        padding: 0.58rem 0.85rem;
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 4px;
        font-weight: 700;
        transition: filter 0.18s ease, transform 0.18s ease;
    }

    .stockin-confirm-actions button:hover,
    .stockin-confirm-actions button:focus {
        filter: brightness(1.12);
        transform: translateY(-1px);
    }

    .stockin-confirm-cancel {
        background: linear-gradient(145deg, #aeb9c4, #465563);
        color: #ffffff;
    }

    .stockin-confirm-submit {
        background: linear-gradient(145deg, #ff6570, #8d0d1d);
        color: #ffffff;
    }

    @keyframes stockin-confirm-pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.06); }
    }

    @media (max-width: 767.98px) {
        .stockin-card-header {
            flex-wrap: wrap;
        }

        .stockin-filter-form {
            width: 100%;
            flex-wrap: wrap;
        }

        .stockin-filter-form input,
        .stockin-filter-form select {
            min-width: 0;
            flex: 1 1 160px;
        }

        .stockin-header-actions {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('stockinConfirmModal');
        const message = document.getElementById('stockinConfirmMessage');
        const submitButton = document.getElementById('stockinConfirmSubmit');
        let activeForm = null;

        function closeModal() {
            modal.classList.remove('is-visible');
            modal.setAttribute('aria-hidden', 'true');
            activeForm = null;
        }

        document.querySelectorAll('.stockin-delete-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                activeForm = form;
                message.textContent = 'Hapus transaksi barang masuk terbaru untuk item "' + form.dataset.item + '"?';
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

        modal.querySelectorAll('[data-stockin-confirm-cancel]').forEach(function (element) {
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
