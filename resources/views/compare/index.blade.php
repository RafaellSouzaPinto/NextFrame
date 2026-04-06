@extends('layouts.app')

@section('title', 'Comparador — NextFrame')
@section('nav_compare', 'active')

@section('content')
    @livewire('compare.component-comparer', [
        'left' => request('left'),
        'right' => request('right'),
        'type' => request('type', 'gpu'),
    ])
@endsection
