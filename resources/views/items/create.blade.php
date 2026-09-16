@extends('layouts.app')
@section('title', 'Tambah Item')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('items._form')
            <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            <a href="{{ route('items.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
