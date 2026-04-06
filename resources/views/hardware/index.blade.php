@extends('layouts.app')

@section('title', 'Meu Setup — NextFrame')
@section('nav_hardware', 'active')

@section('content')
    @livewire('hardware.hardware-manager')
@endsection
