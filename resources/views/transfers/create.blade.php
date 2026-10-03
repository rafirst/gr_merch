@extends('layouts.app')
@section('title', 'Buat Transfer')

@section('content')
    @include('transfers._form', ['isEditing' => false, 'transfer' => null])
@endsection
