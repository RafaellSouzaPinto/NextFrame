@extends('layouts.app')

@section('title', 'Catálogo de Hardware — NextFrame')
@section('nav_catalog', 'active')

@section('content')
    @livewire('catalog.hardware-catalog')
@endsection
