@extends('layouts.app')
@section('title', 'Edit Transfer')

@section('content')
    @include('transfers._form', ['isEditing' => true, 'transfer' => $transfer])
@endsection
