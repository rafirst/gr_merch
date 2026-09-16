@extends('layouts.app')
@section('title', 'Tambah Cabang')
@section('content')
<div class="card"><div class="card-body">
    <form action="{{ route('cabang.store') }}" method="POST">
        @csrf
        @include('cabang._form')
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('cabang.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div></div>
@endsection
