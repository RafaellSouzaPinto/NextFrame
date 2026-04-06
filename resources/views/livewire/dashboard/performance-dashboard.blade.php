<div>
    {{-- Header --}}
    <div class="nf-page-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <h1>Bom dia, {{ explode(' ', auth()->user()->name)[0] }}</h1>
            <p>
                @if($bottleneckSummary['limited_by'])
                    Seu setup está
                    <span class="nf-badge nf-badge--{{ $bottleneckSummary['severity'] === 'low' ? 'green' : ($bottleneckSummary['severity'] === 'medium' ? 'amber' : 'red') }}">
                        {{ strtoupper($bottleneckSummary['limited_by']) }}-bound
                    </span>
                @else
                    Cadastre seu setup para ver a análise de performance.
                @endif
            </p>
        </div>
        <a href="{{ route('bottleneck.index') }}" class="nf-btn-ghost">
            <i class="bi bi-activity"></i> Analisar Bottleneck
        </a>
    </div>

    {{-- Score cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-card-stat
                label="CPU Score"
                :value="number_format($scores['cpu'], 0, ',', '.')"
                icon="cpu"
                color="cpu"
                :barPct="min(100, $scores['cpu'] / 150)"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-card-stat
                label="GPU Score"
                :value="number_format($scores['gpu'], 0, ',', '.')"
                icon="gpu-card"
                color="gpu"
                :barPct="min(100, $scores['gpu'] / 200)"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-card-stat
                label="Bottleneck"
                :value="$bottleneckSummary['pct']"
                unit="%"
                icon="activity"
                :color="$bottleneckSummary['severity'] === 'low' ? 'green' : ($bottleneckSummary['severity'] === 'medium' ? 'amber' : 'red')"
                :barPct="$bottleneckSummary['pct']"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-card-stat
                label="Upgrades"
                :value="$upgradeCount"
                icon="arrow-up-circle"
                color="accent"
            />
        </div>
    </div>

    {{-- FPS por resolução --}}
    <div class="nf-card mb-4">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
            <div>
                <div style="font-size:13px; font-weight:600; color:var(--nf-text-primary); margin-bottom:2px;">FPS Estimado</div>
                <div style="font-size:12px; color:var(--nf-text-secondary);">Baseado no benchmark do seu setup</div>
            </div>
            {{-- Tabs de resolução --}}
            <div class="nf-tabs" style="margin-bottom:0;">
                @foreach(['1080p', '1440p', '4K'] as $res)
                <button
                    class="nf-tab {{ $resolution === $res ? 'active' : '' }}"
                    wire:click="switchResolution('{{ $res }}')"
                    style="{{ $resolution === $res ? 'background:var(--nf-bg-surface); color:var(--nf-accent); box-shadow:0 1px 4px rgba(0,0,0,.3);' : '' }}"
                >{{ $res }}</button>
                @endforeach
            </div>
        </div>

        @if(empty($fpsData) || $fpsData[0]['fps'] === 0)
            <div class="nf-empty-state" style="padding:30px 0;">
                <i class="bi bi-bar-chart"></i>
                <p>Cadastre seu setup para ver os FPS estimados.</p>
                <a href="{{ route('hardware.index') }}" class="nf-btn">
                    <i class="bi bi-plus-lg"></i> Cadastrar Setup
                </a>
            </div>
        @else
            <div class="nf-fps-graph">
                @foreach($fpsData as $row)
                <div class="nf-fps-row">
                    <div class="nf-fps-game">{{ $row['game'] }}</div>
                    <div class="nf-fps-bar-wrap">
                        <div class="nf-fps-bar-fill" style="width:{{ $row['pct'] }}%;">
                            <span class="nf-fps-value">{{ $row['fps'] }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Meu Setup --}}
    <div class="nf-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
            <div style="font-size:13px; font-weight:600; color:var(--nf-text-primary);">Meu Setup</div>
            <a href="{{ route('hardware.index') }}" class="nf-btn-ghost nf-btn-sm">
                <i class="bi bi-pencil"></i> Editar
            </a>
        </div>

        @if(empty($setup) || $setup[0]['score'] === 0)
            <div class="nf-empty-state" style="padding:24px 0;">
                <i class="bi bi-cpu"></i>
                <p>Nenhum componente cadastrado ainda.</p>
                <a href="{{ route('hardware.index') }}" class="nf-btn">
                    <i class="bi bi-plus-lg"></i> Adicionar Componentes
                </a>
            </div>
        @else
            <div style="display:flex; flex-direction:column; gap:8px;">
                @foreach($setup as $component)
                <div style="display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid var(--nf-bg-border-subtle);">
                    <x-hardware-badge :type="$component['type']" />
                    <span style="flex:1; font-size:14px; color:var(--nf-text-primary);">{{ $component['name'] }}</span>
                    <span class="nf-mono nf-score {{ $component['score'] > 10000 ? 'nf-score--high' : ($component['score'] > 5000 ? 'nf-score--mid' : 'nf-score--low') }}" style="font-size:13px;">
                        {{ number_format($component['score'], 0, ',', '.') }}
                    </span>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
