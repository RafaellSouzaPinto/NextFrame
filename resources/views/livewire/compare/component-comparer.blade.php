<div>
    <div class="nf-page-header">
        <h1>Comparador de Componentes</h1>
        <p>Compare dois componentes lado a lado e veja a diferença real em cada especificação.</p>
    </div>

    {{-- Filtro de tipo --}}
    <div class="nf-card mb-4" style="padding:16px 20px;">
        <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
            <span style="font-size:13px; color:var(--nf-text-secondary); white-space:nowrap;">Tipo de componente:</span>
            <div class="nf-filter-group">
                @foreach(['cpu' => 'CPU', 'gpu' => 'GPU', 'ram' => 'RAM', 'storage' => 'Storage'] as $val => $label)
                <button class="nf-filter-btn {{ $typeFilter === $val ? 'active' : '' }}"
                        wire:click="updatedTypeFilter" wire:model="typeFilter"
                        onclick="@this.set('typeFilter', '{{ $val }}')">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Seletor lado a lado --}}
    <div class="nf-card mb-4">
        <div style="display:grid; grid-template-columns:1fr auto 1fr; gap:20px; align-items:end;">

            {{-- Componente A --}}
            <div>
                <label class="nf-label" style="margin-bottom:8px;">
                    <span class="nf-badge nf-badge--{{ $typeFilter }}">A</span>
                    Componente A
                </label>
                <select class="nf-select" wire:model.live="leftId">
                    <option value="">Selecionar {{ strtoupper($typeFilter) }}...</option>
                    @foreach($componentOptions as $opt)
                        <option value="{{ $opt['id'] }}">{{ $opt['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Botão Trocar --}}
            <div style="text-align:center; padding-bottom:2px;">
                <button class="nf-btn-ghost" wire:click="swapComponents"
                        title="Trocar componentes"
                        style="padding:9px 12px;">
                    <i class="bi bi-arrow-left-right"></i>
                </button>
            </div>

            {{-- Componente B --}}
            <div>
                <label class="nf-label" style="margin-bottom:8px;">
                    <span class="nf-badge nf-badge--accent">B</span>
                    Componente B
                </label>
                <select class="nf-select" wire:model.live="rightId">
                    <option value="">Selecionar {{ strtoupper($typeFilter) }}...</option>
                    @foreach($componentOptions as $opt)
                        <option value="{{ $opt['id'] }}">{{ $opt['name'] }}</option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    {{-- Resultado --}}
    @if(!$leftComponent || !$rightComponent)
        <div class="nf-empty-state">
            <i class="bi bi-layout-split"></i>
            <p>Selecione dois componentes acima para ver a comparação detalhada.</p>
        </div>
    @else
        {{-- Cabeçalho dos componentes --}}
        <div class="nf-card mb-3" style="padding:20px;">
            <div style="display:grid; grid-template-columns:1fr auto 1fr; gap:20px; text-align:center;">
                <div>
                    <x-hardware-badge :type="$typeFilter" />
                    <div style="font-size:16px; font-weight:700; color:var(--nf-text-primary); margin-top:8px;">
                        {{ $leftComponent['name'] }}
                    </div>
                    <div class="nf-mono nf-score {{ $leftComponent['score'] > 10000 ? 'nf-score--high' : ($leftComponent['score'] > 5000 ? 'nf-score--mid' : 'nf-score--low') }}"
                         style="font-size:28px; margin-top:4px;">
                        {{ number_format($leftComponent['score'], 0, ',', '.') }}
                    </div>
                </div>

                <div style="display:flex; align-items:center; color:var(--nf-text-muted); font-size:20px;">vs</div>

                <div>
                    <x-hardware-badge :type="$typeFilter" />
                    <div style="font-size:16px; font-weight:700; color:var(--nf-text-primary); margin-top:8px;">
                        {{ $rightComponent['name'] }}
                    </div>
                    <div class="nf-mono nf-score {{ $rightComponent['score'] > 10000 ? 'nf-score--high' : ($rightComponent['score'] > 5000 ? 'nf-score--mid' : 'nf-score--low') }}"
                         style="font-size:28px; margin-top:4px;">
                        {{ number_format($rightComponent['score'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabela de specs --}}
        @if(!empty($diffTable))
        <div class="nf-card" style="padding:0; overflow:hidden;">
            <table class="nf-table nf-compare-table">
                <thead>
                    <tr>
                        <th>Especificação</th>
                        <th>{{ $leftComponent['name'] }}</th>
                        <th>Δ Delta</th>
                        <th>{{ $rightComponent['name'] }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($diffTable as $row)
                    <tr x-data="{ delta: {{ $row['delta'] ?? 0 }} }">
                        <td style="color:var(--nf-text-secondary); font-size:13px;">{{ $row['spec'] }}</td>
                        <td class="nf-mono" style="font-size:13px;">{{ $row['left'] }}</td>
                        <td x-bind:class="delta > 0 ? 'nf-delta-negative' : (delta < 0 ? 'nf-delta-positive' : 'nf-delta-neutral')"
                            class="nf-mono">
                            <span x-text="delta === 0 ? '=' : (delta > 0 ? '+' + delta + '%' : delta + '%')"></span>
                        </td>
                        <td class="nf-mono" style="font-size:13px;">{{ $row['right'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Veredicto --}}
        @if($verdict)
        <div class="nf-card mt-3" style="border-left:3px solid var(--nf-accent);">
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="bi bi-trophy" style="color:var(--nf-accent); font-size:18px;"></i>
                <span style="font-size:15px; font-weight:600; color:var(--nf-text-primary);">
                    {{ $verdict }}
                </span>
            </div>
        </div>
        @endif

    @endif
</div>
