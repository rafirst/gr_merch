<div class="form-row">
    <div class="form-group col-md-4">
        <label>Kode Cabang</label>
        <input type="text" name="kode_cabang" class="form-control" value="{{ old('kode_cabang', $cabang->kode_cabang ?? '') }}" required>
    </div>
    <div class="form-group col-md-8">
        <label>Nama Cabang</label>
        <input type="text" name="nama_cabang" class="form-control" value="{{ old('nama_cabang', $cabang->nama_cabang ?? '') }}" required>
    </div>
</div>
<div class="form-group">
    <label>Alamat</label>
    <input type="text" name="alamat" class="form-control" value="{{ old('alamat', $cabang->alamat ?? '') }}">
</div>
<div class="form-group">
    <label>Telepon</label>
    <input type="text" name="telepon" class="form-control" value="{{ old('telepon', $cabang->telepon ?? '') }}">
</div>
