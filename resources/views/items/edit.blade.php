@extends('layouts.app')
@section('title', 'Edit Item')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('items.update', $item) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('items._form')
            <button class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            <a href="{{ route('items.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
