@props(['type'])

@php
$validTypes = ['cpu', 'gpu', 'ram', 'storage'];
$safeType = in_array(strtolower($type), $validTypes) ? strtolower($type) : 'accent';
@endphp

<span class="nf-badge nf-badge--{{ $safeType }}">{{ strtoupper($type) }}</span>
