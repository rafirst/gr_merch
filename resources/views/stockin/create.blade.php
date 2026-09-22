@extends('layouts.app')
@section('title', 'Input Barang Masuk')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('stockin.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Item</label>
                <div class="item-search-wrapper">
                    <input type="hidden" name="item_id" id="itemId">
                    <input type="text" id="itemSearch" class="form-control item-search-input" placeholder="Ketik kode atau nama item..." autocomplete="off" required>
                    <div id="itemOptions" class="item-options" role="listbox">
                    @foreach($items as $i)
                        <button type="button" class="item-option" data-item-id="{{ $i->id }}" data-item-name="{{ $i->nama_items }}" data-item-photo="{{ $i->foto ? url('storage/'.ltrim($i->foto, '/')) : '' }}" data-search="{{ strtolower($i->kode_items.' '.$i->nama_items.' '.($i->cabang->nama_cabang ?? '')) }}" role="option">
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
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Jumlah Masuk</label>
                    <input type="number" name="jumlah" class="form-control" min="1" required>
                </div>
                <div class="form-group col-md-4">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required readonly>
                </div>
                <div class="form-group col-md-4">
                    <label>Sumber</label>
                    <input type="text" name="sumber" class="form-control" placeholder="misal: Kiriman Pusat, Supplier X">
                </div>
            </div>
            <div class="form-row stockin-detail-row">
                <div class="form-group col-md-6">
                    <label>Foto Item</label>
                    <div id="itemPhotoPreview" class="item-photo-preview item-photo-empty">
                        <i class="fas fa-image"></i>
                        <span>Pilih item untuk melihat foto</span>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="keterangan">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" class="form-control stockin-notes" rows="6" placeholder="Tambahkan keterangan bila diperlukan"></textarea>
                </div>
            </div>
            <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            <a href="{{ route('stockin.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .item-search-wrapper {
        position: relative;
    }

    .item-search-input {
        padding-right: 2.5rem;
        background-image: linear-gradient(45deg, transparent 50%, #f70008 50%), linear-gradient(135deg, #f70008 50%, transparent 50%);
        background-position: calc(100% - 1.1rem) 52%, calc(100% - 0.75rem) 52%;
        background-size: 0.4rem 0.4rem, 0.4rem 0.4rem;
        background-repeat: no-repeat;
    }

    .item-options {
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

    .item-options.is-open {
        display: block;
    }

    .item-option {
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

    .item-option:last-of-type {
        border-bottom: 0;
    }

    .item-option:hover,
    .item-option:focus {
        outline: 0;
        border-radius: 0.25rem;
        background: linear-gradient(90deg, rgba(247, 0, 8, 0.22), rgba(247, 0, 8, 0.06));
    }

    .item-option-main {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 0.2rem;
    }

    .item-option-main strong {
        overflow: hidden;
        color: #ffffff;
        font-size: 0.92rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .item-option-main small {
        color: rgba(244, 247, 250, 0.62);
        font-size: 0.75rem;
    }

    .item-stock {
        flex: 0 0 auto;
        padding: 0.25rem 0.45rem;
        border: 1px solid rgba(247, 0, 8, 0.5);
        border-radius: 0.2rem;
        color: #ff6268;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .item-no-results {
        margin: 0;
        padding: 0.8rem;
        color: rgba(244, 247, 250, 0.62);
        font-size: 0.85rem;
        text-align: center;
    }

    .stockin-detail-row {
        align-items: stretch;
    }

    .item-photo-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 150px;
        overflow: hidden;
        border: 1px solid rgba(105, 143, 176, 0.45);
        border-radius: 0.35rem;
        background: rgba(0, 0, 0, 0.22);
    }

    .item-photo-preview img {
        display: block;
        width: 100%;
        height: 190px;
        object-fit: contain;
    }

    .item-photo-empty {
        flex-direction: column;
        gap: 0.45rem;
        color: rgba(244, 247, 250, 0.55);
        font-size: 0.85rem;
    }

    .item-photo-empty i {
        color: rgba(247, 0, 8, 0.75);
        font-size: 1.8rem;
    }

    .stockin-notes {
        min-height: 190px;
        resize: vertical;
    }
</style>
@endpush

@push('scripts')
<script>
    const itemSearch = document.getElementById('itemSearch');
    const itemId = document.getElementById('itemId');
    const itemOptions = document.getElementById('itemOptions');
    const itemOptionButtons = Array.from(itemOptions.querySelectorAll('.item-option'));
    const itemNoResults = itemOptions.querySelector('.item-no-results');
    const itemPhotoPreview = document.getElementById('itemPhotoPreview');

    function filterItems() {
        const query = itemSearch.value.trim().toLowerCase();
        let visibleItems = 0;

        itemOptionButtons.forEach((option) => {
            const isVisible = !query || option.dataset.search.includes(query);
            option.hidden = !isVisible;
            visibleItems += isVisible ? 1 : 0;
        });

        itemNoResults.hidden = visibleItems > 0;
        itemOptions.classList.add('is-open');
    }

    function selectItem(event) {
        const selectedOption = event.currentTarget;
        const itemPhoto = selectedOption.dataset.itemPhoto;

        itemSearch.value = selectedOption.dataset.itemName;
        itemId.value = selectedOption.dataset.itemId;
        itemSearch.setCustomValidity('');
        itemOptions.classList.remove('is-open');

        itemPhotoPreview.className = 'item-photo-preview';
        itemPhotoPreview.innerHTML = itemPhoto
            ? `<img src="${itemPhoto}" alt="Foto ${selectedOption.dataset.itemName}">`
            : '<div class="item-photo-empty"><i class="fas fa-image"></i><span>Item belum memiliki foto</span></div>';
    }

    itemSearch.addEventListener('input', function () {
        itemId.value = '';
        itemSearch.setCustomValidity('Pilih item dari daftar yang tersedia.');
        filterItems();
    });
    itemSearch.addEventListener('focus', filterItems);
    itemOptionButtons.forEach((option) => option.addEventListener('click', selectItem));
    document.addEventListener('click', function (event) {
        if (!event.target.closest('.item-search-wrapper')) {
            itemOptions.classList.remove('is-open');
        }
    });
</script>
@endpush
