@props([
    'label',
    'value',
    'unit'    => '',
    'icon'    => 'graph-up',
    'color'   => 'accent',
    'barPct'  => null,
])

<div class="nf-card nf-card-stat">
    <div class="nf-card-stat-header">
        <span class="nf-card-stat-label">{{ $label }}</span>
        <i class="bi bi-{{ $icon }}" style="color: var(--nf-{{ $color }});"></i>
    </div>
    <div class="nf-card-stat-value">
        <span class="nf-mono">{{ $value }}</span>
        @if($unit)
            <span class="nf-card-stat-unit">{{ $unit }}</span>
        @endif
    </div>
    @if($barPct !== null)
        <div class="nf-card-stat-bar">
            <div class="nf-card-stat-bar-fill" style="width: {{ $barPct }}%; background: var(--nf-{{ $color }});"></div>
        </div>
    @endif
</div>
