@extends('layouts.app')
@section('title', 'Edit Barang Masuk')

@section('content')
<div class="card stockin-edit-card">
    <div class="card-header stockin-edit-header">
        <h3 class="card-title mb-0">Edit Barang Masuk</h3>
        <a href="{{ route('stockin.index', $stockIn) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="stockin-edit-note">
            <i class="fas fa-info-circle"></i>
            @if(auth()->user()->isAdminHo())
                <span>Perubahan jumlah atau item akan menyesuaikan stok secara otomatis.</span>
            @else
                <span>Perubahan akan diajukan ke administrator. Stok belum berubah sampai request disetujui.</span>
            @endif
        </div>

        <form action="{{ route('stockin.update', $stockIn) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-row stockin-item-selection-row">
                <div class="form-group col-md-8">
                    <label for="item_id">Item</label>
                    <select name="item_id" id="item_id" class="form-control" required>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" data-stock="{{ $item->stok_items }}" {{ (string) old('item_id', $stockIn->item_id) === (string) $item->id ? 'selected' : '' }}>
                                {{ $item->kode_items }} - {{ $item->nama_items }} ({{ $item->cabang->nama_cabang ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="current_stock">Stok Saat Ini</label>
                    <input type="text" id="current_stock" class="form-control stockin-current-stock" value="{{ old('item_id', $stockIn->item_id) == $stockIn->item_id ? $stockIn->item->stok_items : 0 }}" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="jumlah">Jumlah Masuk</label>
                    <input type="number" name="jumlah" id="jumlah" class="form-control" min="1" value="{{ old('jumlah', $stockIn->jumlah) }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ old('tanggal', $stockIn->tanggal?->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="sumber">Sumber</label>
                    <input type="text" name="sumber" id="sumber" class="form-control" maxlength="150" value="{{ old('sumber', $stockIn->sumber) }}" placeholder="misal: Kiriman Pusat, Supplier X">
                </div>
            </div>

            <div class="form-group">
                <label for="keterangan">Keterangan</label>
                <textarea name="keterangan" id="keterangan" class="form-control" rows="4" maxlength="255" placeholder="Tambahkan keterangan bila diperlukan">{{ old('keterangan', $stockIn->keterangan) }}</textarea>
            </div>

            <button class="btn btn-primary">
                <i class="fas {{ auth()->user()->isAdminHo() ? 'fa-save' : 'fa-paper-plane' }}"></i>
                {{ auth()->user()->isAdminHo() ? 'Update' : 'Ajukan Perubahan' }}
            </button>
            <a href="{{ route('stockin.index', $stockIn) }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stockin-edit-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .stockin-edit-header .card-title {
        margin: 0;
    }

    .stockin-edit-header .btn {
        flex: 0 0 auto;
        margin-left: auto;
    }

    .stockin-edit-note {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        margin-bottom: 1.15rem;
        padding: 0.65rem 0.8rem;
        border-left: 3px solid #159fbe;
        background: rgba(21, 159, 190, 0.12);
        color: rgba(244, 247, 250, 0.78);
        font-size: 0.82rem;
    }

    .stockin-edit-note i {
        color: #69d8ed;
    }

    .stockin-edit-card label {
        color: rgba(244, 247, 250, 0.82);
        font-size: 0.78rem;
        font-weight: 700;
    }

    .stockin-edit-card .form-control {
        border-color: rgba(105, 143, 176, 0.38);
        background-color: rgba(2, 10, 17, 0.78);
        color: #f4f7fa;
    }

    .stockin-edit-card .form-control:focus {
        border-color: rgba(247, 0, 8, 0.85);
        box-shadow: 0 0 0 0.12rem rgba(247, 0, 8, 0.12);
    }

    .stockin-edit-card .stockin-current-stock {
        color: #54e47a;
        font-weight: 700;
    }

    @media (max-width: 767.98px) {
        .stockin-edit-header {
            align-items: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    const stockInItemSelect = document.getElementById('item_id');
    const currentStockInput = document.getElementById('current_stock');

    function updateCurrentStock() {
        const selectedOption = stockInItemSelect.options[stockInItemSelect.selectedIndex];
        currentStockInput.value = selectedOption?.dataset.stock || '0';
    }

    stockInItemSelect.addEventListener('change', updateCurrentStock);
    updateCurrentStock();
</script>
@endpush
