@extends('layouts.app')
@section('title', 'Tambah User')
@section('content')
<div class="card"><div class="card-body">
    <form action="{{ route('users.store') }}" method="POST">
        @csrf
        @include('users._form')
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div></div>
@endsection
