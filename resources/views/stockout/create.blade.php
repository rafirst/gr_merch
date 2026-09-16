@extends('layouts.app')
@section('title', 'Input Barang Keluar')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('stockout.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Item</label>
                <select name="item_id" id="item_id" class="form-control" required>
                    <option value="">-- Pilih Item --</option>
                    @foreach($items as $i)
                        <option value="{{ $i->id }}" data-harga="{{ $i->harga_items }}">{{ $i->kode_items }} - {{ $i->nama_items }} ({{ $i->cabang->nama_cabang ?? '-' }}) | Stok: {{ $i->stok_items }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Jumlah Keluar</label>
                    <input type="number" name="jumlah" class="form-control" min="1" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Jenis Keluar</label>
                    <select name="jenis" id="jenis" class="form-control" required>
                        <option value="penjualan">Penjualan</option>
                        <option value="hadiah">Hadiah</option>
                        <option value="request">Request</option>
                    </select>
                </div>
                <div class="form-group col-md-3" id="hargaJualWrap">
                    <label>Harga Jual (Rp)</label>
                    <input type="number" step="0.01" name="harga_jual" id="harga_jual" class="form-control">
                </div>
                <div class="form-group col-md-3">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            <div class="alert alert-info" id="infoApproval">
                <i class="fas fa-info-circle"></i> Transaksi <strong>Penjualan</strong> akan langsung tercatat & mengurangi stok.
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2" placeholder="misal: nama customer, tujuan hadiah, dll"></textarea>
            </div>
            <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            <a href="{{ route('stockout.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const jenisSelect = document.getElementById('jenis');
    const hargaWrap = document.getElementById('hargaJualWrap');
    const hargaInput = document.getElementById('harga_jual');
    const infoBox = document.getElementById('infoApproval');
    const itemSelect = document.getElementById('item_id');

    function toggleJenis() {
        if (jenisSelect.value === 'penjualan') {
            hargaWrap.style.display = '';
            infoBox.innerHTML = '<i class="fas fa-info-circle"></i> Transaksi <strong>Penjualan</strong> akan langsung tercatat & mengurangi stok.';
            infoBox.className = 'alert alert-info';
        } else {
            hargaWrap.style.display = 'none';
            infoBox.innerHTML = '<i class="fas fa-clock"></i> Transaksi <strong>' + jenisSelect.options[jenisSelect.selectedIndex].text + '</strong> memerlukan approval Admin Pusat sebelum stok dipotong.';
            infoBox.className = 'alert alert-warning';
        }
    }
    itemSelect.addEventListener('change', function () {
        const harga = this.options[this.selectedIndex].getAttribute('data-harga');
        if (harga) hargaInput.value = parseFloat(harga);
    });
    jenisSelect.addEventListener('change', toggleJenis);
    toggleJenis();
</script>
@endpush
