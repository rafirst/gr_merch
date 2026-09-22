<div class="form-row">
    <div class="form-group col-md-4">
        <label>Kode Items</label>
        <input type="text" name="kode_items" class="form-control" value="{{ old('kode_items', $item->kode_items ?? '') }}" required>
    </div>
    <div class="form-group col-md-8">
        <label>Nama Items</label>
        <input type="text" name="nama_items" class="form-control" value="{{ old('nama_items', $item->nama_items ?? '') }}" required>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('foto')?.addEventListener('change', function () {
        const label = document.querySelector('label[for="foto"]');
        label.textContent = this.files[0]?.name || 'Pilih foto item';
    });
</script>
@endpush
<div class="form-row">
    <div class="form-group col-md-4">
        <label>Kategori</label>
        <select name="kategori" class="form-control">
            <option value="">Pilih kategori</option>
            @foreach($kategoriOptions as $kategori)
                <option value="{{ $kategori }}" {{ old('kategori', $item->kategori ?? '') === $kategori ? 'selected' : '' }}>{{ ucfirst($kategori) }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-4">
        <label>Harga Beli (Rp)</label>
        <input type="text" inputmode="decimal" name="harga_items" class="form-control" value="{{ old('harga_items', isset($item) ? number_format($item->harga_items, 0, ',', '.') : '') }}" placeholder="Contoh: 100.000" required>
    </div>
    <div class="form-group col-md-4">
        <label>Harga Jual (Rp)</label>
        <input type="text" inputmode="decimal" name="harga_jual" class="form-control" value="{{ old('harga_jual', isset($item) ? number_format($item->harga_jual, 0, ',', '.') : '') }}" placeholder="Contoh: 150.000">
    </div>
</div>
<div class="form-row">
    @if(auth()->user()->isAdminHo())
    <div class="form-group col-md-6">
        <label>Cabang</label>
        <select name="cabang_id" class="form-control" required>
            @foreach($cabangs as $c)
                <option value="{{ $c->id }}" {{ old('cabang_id', $item->cabang_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->nama_cabang }}</option>
            @endforeach
        </select>
    </div>
    @else
    <input type="hidden" name="cabang_id" value="{{ auth()->user()->cabang_id }}">
    @endif
    <div class="form-group col-md-6">
        <label>Foto Item</label>
        <div class="custom-file">
            <input type="file" name="foto" id="foto" class="custom-file-input" accept="image/*">
            <label class="custom-file-label" for="foto">Pilih foto item</label>
        </div>
    </div>
</div>


