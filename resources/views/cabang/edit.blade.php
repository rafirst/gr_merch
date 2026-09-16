@extends('layouts.app')
@section('title', 'Edit Cabang')
@section('content')
<div class="card"><div class="card-body">
    <form action="{{ route('cabang.update', $cabang) }}" method="POST">
        @csrf @method('PUT')
        @include('cabang._form')
        <button class="btn btn-primary">Update</button>
        <a href="{{ route('cabang.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div></div>
@endsection
