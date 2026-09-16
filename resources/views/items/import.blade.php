@extends('layouts.app')
@section('title', 'Import Data Items')

@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Import Data Items dari Excel</h3></div>
    <div class="card-body">
        <p>Format kolom pada baris pertama (header) file Excel/CSV:</p>
        <code>kode_items | nama_items | kategori | harga_items | stok_items | stok_minimum | satuan | kode_cabang</code>
        <p class="mt-2 text-muted">Jika kombinasi kode_items + cabang sudah ada, datanya akan di-update. Jika belum ada, akan dibuat baru.</p>

        <form action="{{ route('items.import') }}" method="POST" enctype="multipart/form-data" class="mt-3">
            @csrf
            <div class="form-group">
                <input type="file" name="file" class="form-control-file" accept=".xlsx,.xls,.csv" required>
            </div>
            <button class="btn btn-primary"><i class="fas fa-upload"></i> Import Sekarang</button>
            <a href="{{ route('items.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
