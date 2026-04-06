<div>
    {{-- Header --}}
    <div class="nf-page-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <h1>Catálogo de Hardware</h1>
            <p>
                <span class="nf-mono" style="color:var(--nf-accent);">{{ number_format($totalCount, 0, ',', '.') }}</span>
                componentes indexados
            </p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="nf-card mb-4" style="padding:16px 20px;">
        <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            {{-- Busca --}}
            <div style="position:relative; flex:1; min-width:200px;">
                <input
                    type="text"
                    class="nf-input"
                    placeholder="Buscar componente..."
                    wire:model.live.debounce.300ms="search"
                    style="padding-left:36px;"
                >
                <i class="bi bi-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--nf-text-muted);"></i>
            </div>

            {{-- Tipo --}}
            <div class="nf-filter-group">
                @foreach(['all' => 'Todos', 'cpu' => 'CPU', 'gpu' => 'GPU', 'ram' => 'RAM', 'storage' => 'Storage'] as $val => $label)
                <button class="nf-filter-btn {{ $typeFilter === $val ? 'active' : '' }}"
                        wire:click="setType('{{ $val }}')">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            {{-- Ordenação --}}
            <select class="nf-select" wire:model.live="sortBy" style="width:auto; min-width:150px;">
                <option value="name">Nome A-Z</option>
                <option value="score_desc">Maior Score</option>
                <option value="score_asc">Menor Score</option>
            </select>
        </div>
    </div>

    {{-- Grid de componentes --}}
    @if($components->isEmpty())
        <div class="nf-empty-state">
            <i class="bi bi-database-x"></i>
            <p>
                @if($search || $typeFilter !== 'all')
                    Nenhum componente encontrado com os filtros aplicados.
                @else
                    O catálogo ainda não foi populado.<br>
                    <span style="font-size:13px;">Execute <code style="color:var(--nf-accent);">php artisan db:seed</code> para importar os dados.</span>
                @endif
            </p>
        </div>
    @else
        <div class="row g-3">
            @foreach($components as $component)
            <div class="col-sm-6 col-xl-4">
                <div class="nf-card" style="padding:0; overflow:hidden;">
                    <div style="padding:18px 20px 14px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                            <x-hardware-badge :type="$component['type']" />
                            <span class="nf-mono nf-score {{ $component['score'] > 10000 ? 'nf-score--high' : ($component['score'] > 5000 ? 'nf-score--mid' : 'nf-score--low') }}" style="font-size:20px;">
                                {{ number_format($component['score'], 0, ',', '.') }}
                            </span>
                        </div>
                        <div style="font-size:14px; font-weight:600; color:var(--nf-text-primary); margin-bottom:4px;">
                            {{ $component['name'] }}
                        </div>
                        <div style="font-size:12px; color:var(--nf-text-secondary);">
                            {{ $component['specs_summary'] ?? '' }}
                        </div>
                        <div class="nf-bench-bar">
                            <div class="nf-bench-bar-fill" style="width:{{ min(100, $component['score'] / 200) }}%;"></div>
                        </div>
                    </div>
                    <div style="padding:10px 20px; border-top:1px solid var(--nf-bg-border-subtle); display:flex; justify-content:space-between; align-items:center;">
                        @if(isset($component['price']) && $component['price'])
                            <span class="nf-mono" style="font-size:13px; color:var(--nf-text-secondary);">
                                R$ {{ number_format($component['price'], 0, ',', '.') }}
                            </span>
                        @else
                            <span></span>
                        @endif
                        <button class="nf-btn-ghost nf-btn-sm" wire:click="addToMySetup({{ $component['id'] }})">
                            <i class="bi bi-plus-lg"></i> Adicionar
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $components->links() }}
        </div>
    @endif
</div>
