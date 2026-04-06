@extends('layouts.app')

@section('title', 'Dashboard — NextFrame')
@section('nav_dashboard', 'active')

@section('content')
    @livewire('dashboard.performance-dashboard')
@endsection
