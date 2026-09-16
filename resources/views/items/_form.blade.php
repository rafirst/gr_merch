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
        <label>Harga Items (Rp)</label>
        <input type="text" inputmode="decimal" name="harga_items" class="form-control" value="{{ old('harga_items', isset($item) ? number_format($item->harga_items, 0, ',', '.') : '') }}" placeholder="Contoh: 100.000" required>
    </div>
    <div class="form-group col-md-4">
        <label>Satuan</label>
        <input type="text" name="satuan" class="form-control" value="{{ old('satuan', $item->satuan ?? 'pcs') }}">
    </div>
</div>
<div class="form-row">
    @if(!isset($item))
    <div class="form-group col-md-4">
        <label>Stok Awal</label>
        <input type="number" name="stok_items" class="form-control" value="{{ old('stok_items', 0) }}" required>
    </div>
    @endif
    <div class="form-group col-md-4">
        <label>Stok Minimum (alert)</label>
        <input type="number" name="stok_minimum" class="form-control" value="{{ old('stok_minimum', $item->stok_minimum ?? 5) }}">
    </div>
    <div class="form-group col-md-4">
        <label>Foto Item (opsional)</label>
        <input type="file" name="foto" class="form-control-file">
    </div>
</div>

@if(auth()->user()->isAdminHo())
<div class="form-group">
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
