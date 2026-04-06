@props(['type' => 'success', 'message'])

@php
$icons = [
    'success' => 'check-circle',
    'danger'  => 'exclamation-triangle',
    'warning' => 'exclamation-circle',
    'info'    => 'info-circle',
];
$icon = $icons[$type] ?? 'info-circle';
@endphp

<div x-data="{ show: true }"
     x-show="show"
     x-init="setTimeout(() => show = false, 5000)"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 -translate-y-2"
     class="nf-alert nf-alert--{{ $type }}"
     style="display:none;">
    <i class="bi bi-{{ $icon }}"></i>
    <span>{{ $message }}</span>
    <button @click="show = false" class="nf-alert-close" type="button">×</button>
</div>
