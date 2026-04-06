@props([
    'cpuPct'   => 50,
    'gpuPct'   => 50,
    'severity' => 'low',
    'limitedBy' => null,
])

<div class="nf-bottleneck nf-bottleneck--{{ $severity }}"
     x-data="{ animated: false }"
     x-init="setTimeout(() => animated = true, 150)">

    <div class="nf-bottleneck-labels">
        <span class="nf-bottleneck-label nf-bottleneck-label--cpu">
            CPU {{ $cpuPct }}%
            @if($limitedBy === 'cpu')
                <i class="bi bi-arrow-up-circle-fill" style="font-size:10px; margin-left:2px;"></i>
            @endif
        </span>
        <span class="nf-bottleneck-label nf-bottleneck-label--gpu">
            @if($limitedBy === 'gpu')
                <i class="bi bi-arrow-up-circle-fill" style="font-size:10px; margin-right:2px;"></i>
            @endif
            GPU {{ $gpuPct }}%
        </span>
    </div>

    <div class="nf-bottleneck-track">
        <div class="nf-bottleneck-fill nf-bottleneck-fill--cpu"
             :style="'width: ' + (animated ? '{{ $cpuPct }}' : '0') + '%'">
        </div>
        <div class="nf-bottleneck-fill nf-bottleneck-fill--gpu"
             :style="'width: ' + (animated ? '{{ $gpuPct }}' : '0') + '%'">
        </div>
    </div>
</div>
