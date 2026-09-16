@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
<div class="card"><div class="card-body">
    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf @method('PUT')
        @include('users._form')
        <button class="btn btn-primary">Update</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div></div>
@endsection
