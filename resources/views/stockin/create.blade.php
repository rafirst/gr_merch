@extends('layouts.app')
@section('title', 'Input Barang Masuk')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('stockin.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Item</label>
                <select name="item_id" class="form-control" required>
                    <option value="">-- Pilih Item --</option>
                    @foreach($items as $i)
                        <option value="{{ $i->id }}">{{ $i->kode_items }} - {{ $i->nama_items }} ({{ $i->cabang->nama_cabang ?? '-' }}) | Stok: {{ $i->stok_items }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Jumlah Masuk</label>
                    <input type="number" name="jumlah" class="form-control" min="1" required>
                </div>
                <div class="form-group col-md-4">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label>Sumber</label>
                    <input type="text" name="sumber" class="form-control" placeholder="misal: Kiriman Pusat, Supplier X">
                </div>
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2"></textarea>
            </div>
            <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            <a href="{{ route('stockin.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
