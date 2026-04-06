@extends('layouts.app')

@section('title', 'Bottleneck — NextFrame')
@section('nav_bottleneck', 'active')

@section('content')
    @livewire('bottleneck.bottleneck-calculator')
@endsection
