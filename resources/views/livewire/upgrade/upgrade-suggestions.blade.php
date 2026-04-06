<div>
    <div class="nf-page-header" style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <h1>Sugestões de Upgrade</h1>
            <p>
                @if($currentBottleneck)
                    Baseado no seu bottleneck atual:
                    <span class="nf-badge nf-badge--amber">{{ strtoupper($currentBottleneck) }}-bound</span>
                @else
                    Recomendações personalizadas para o seu setup.
                @endif
            </p>
        </div>
        <a href="{{ route('bottleneck.index') }}" class="nf-btn-ghost">
            <i class="bi bi-activity"></i> Recalcular Bottleneck
        </a>
    </div>

    {{-- Filtros --}}
    <div class="nf-card mb-4" style="padding:16px 20px;">
        <div style="display:flex; gap:16px; flex-wrap:wrap; align-items:center;">
            {{-- Orçamento --}}
            <div style="position:relative; min-width:180px;">
                <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:13px; color:var(--nf-text-secondary); pointer-events:none;">R$</span>
                <input
                    type="number"
                    class="nf-input"
                    placeholder="Orçamento máximo"
                    wire:model.live.debounce.500ms="budget"
                    style="padding-left:36px;"
                    min="0"
                >
            </div>

            {{-- Prioridade --}}
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <span style="font-size:13px; color:var(--nf-text-secondary);">Prioridade:</span>
                <div class="nf-filter-group">
                    @foreach(['bottleneck' => 'Bottleneck', 'fps' => 'FPS', 'cost_benefit' => 'Custo-Benefício'] as $val => $label)
                    <button class="nf-filter-btn {{ $priorityFilter === $val ? 'active' : '' }}"
                            wire:click="setPriority('{{ $val }}')">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de sugestões --}}
    @if(empty($suggestions))
        <div class="nf-empty-state">
            <i class="bi bi-arrow-up-circle"></i>
            <p>
                @if(empty($suggestions) && !$currentBottleneck)
                    Cadastre seu setup e calcule o bottleneck para receber sugestões de upgrade personalizadas.
                @else
                    Nenhum upgrade encontrado para o orçamento ou filtro selecionado.
                @endif
            </p>
            <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                <a href="{{ route('hardware.index') }}" class="nf-btn">
                    <i class="bi bi-cpu"></i> Cadastrar Setup
                </a>
                <a href="{{ route('bottleneck.index') }}" class="nf-btn-ghost">
                    <i class="bi bi-activity"></i> Calcular Bottleneck
                </a>
            </div>
        </div>
    @else
        <div style="display:flex; flex-direction:column; gap:16px;">
            @foreach($suggestions as $suggestion)
            <div class="nf-card" style="padding:0; overflow:hidden;">

                {{-- Corpo: atual → sugerido --}}
                <div class="nf-upgrade-card">

                    {{-- Componente atual --}}
                    <div>
                        <div style="font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:var(--nf-text-muted); margin-bottom:8px;">
                            Atual
                        </div>
                        <x-hardware-badge :type="$suggestion['type']" />
                        <div style="font-size:14px; font-weight:600; color:var(--nf-text-primary); margin-top:8px;">
                            {{ $suggestion['current_name'] }}
                        </div>
                        <div class="nf-mono" style="font-size:20px; font-weight:700; margin-top:4px;
                             color: {{ $suggestion['current_score'] > 10000 ? 'var(--nf-green)' : ($suggestion['current_score'] > 5000 ? 'var(--nf-amber)' : 'var(--nf-red)') }};">
                            {{ number_format($suggestion['current_score'], 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- Seta --}}
                    <div class="nf-upgrade-arrow">
                        <i class="bi bi-arrow-right-circle-fill"></i>
                        <span>upgrade</span>
                    </div>

                    {{-- Componente sugerido --}}
                    <div>
                        <div style="font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:var(--nf-text-muted); margin-bottom:8px;">
                            Sugerido
                        </div>
                        <x-hardware-badge :type="$suggestion['type']" />
                        <div style="font-size:14px; font-weight:600; color:var(--nf-text-primary); margin-top:8px;">
                            {{ $suggestion['suggested_name'] }}
                        </div>
                        <div class="nf-mono nf-score nf-score--high" style="font-size:20px; font-weight:700; margin-top:4px;">
                            {{ number_format($suggestion['suggested_score'], 0, ',', '.') }}
                        </div>
                    </div>

                </div>

                {{-- Rodapé do card --}}
                <div class="nf-upgrade-footer">
                    <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                        <span class="nf-mono" style="font-size:14px; font-weight:600; color:var(--nf-green);">
                            <i class="bi bi-arrow-up-circle"></i>
                            +{{ $suggestion['fps_gain'] }} FPS
                        </span>
                        <span style="font-size:13px; color:var(--nf-text-secondary);">
                            R$ {{ number_format($suggestion['price'], 0, ',', '.') }}
                        </span>
                        @if($suggestion['is_best_value'] ?? false)
                            <span class="nf-badge nf-badge--accent">
                                <i class="bi bi-star-fill"></i> Melhor custo-benefício
                            </span>
                        @endif
                    </div>
                    <div style="display:flex; gap:8px;">
                        <a href="{{ route('compare.index', ['left' => $suggestion['current_id'], 'right' => $suggestion['suggested_id'], 'type' => $suggestion['type']]) }}"
                           class="nf-btn-ghost nf-btn-sm">
                            <i class="bi bi-layout-split"></i> Comparar
                        </a>
                    </div>
                </div>

            </div>
            @endforeach
        </div>
    @endif
</div>
