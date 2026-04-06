<div x-data="{ panelOpen: false }"
     @open-panel.window="panelOpen = true"
     @close-panel.window="panelOpen = false">

    {{-- Header --}}
    <div class="nf-page-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <h1>Meu Setup</h1>
            <p>Gerencie os componentes do seu PC.</p>
        </div>
        <button class="nf-btn" @click="panelOpen = true">
            <i class="bi bi-plus-lg"></i> Adicionar Componente
        </button>
    </div>

    {{-- Lista de componentes --}}
    @if($components->isEmpty())
        <div class="nf-card">
            <div class="nf-empty-state">
                <i class="bi bi-cpu"></i>
                <p>Nenhum componente cadastrado ainda.<br>
                   <span style="font-size:13px;">Adicione sua CPU, GPU, RAM e armazenamento para começar.</span>
                </p>
                <button class="nf-btn" @click="panelOpen = true">
                    <i class="bi bi-plus-lg"></i> Adicionar Primeiro Componente
                </button>
            </div>
        </div>
    @else
        <div class="nf-card" style="padding:0; overflow:hidden;">
            <table class="nf-table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Componente</th>
                        <th>Benchmark Score</th>
                        <th style="text-align:right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($components as $component)
                    <tr x-data="{ confirming: false }">
                        <td><x-hardware-badge :type="$component['type']" /></td>
                        <td style="font-weight:500;">{{ $component['name'] }}</td>
                        <td>
                            <span class="nf-mono nf-score {{ $component['score'] > 10000 ? 'nf-score--high' : ($component['score'] > 5000 ? 'nf-score--mid' : 'nf-score--low') }}">
                                {{ number_format($component['score'], 0, ',', '.') }}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex; justify-content:flex-end; gap:8px; align-items:center;">
                                <template x-if="!confirming">
                                    <div style="display:flex; gap:6px;">
                                        <button class="nf-btn-ghost nf-btn-sm"
                                                wire:click="editComponent({{ $component['id'] }})"
                                                @click="panelOpen = true">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="nf-btn-danger nf-btn-sm" @click="confirming = true">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </template>
                                <template x-if="confirming">
                                    <div style="display:flex; gap:6px; align-items:center;">
                                        <span style="font-size:12px; color:var(--nf-text-secondary);">Confirmar?</span>
                                        <button class="nf-btn-danger nf-btn-sm"
                                                wire:click="deleteComponent({{ $component['id'] }})"
                                                @click="confirming = false">
                                            Sim
                                        </button>
                                        <button class="nf-btn-ghost nf-btn-sm" @click="confirming = false">
                                            Não
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Painel lateral deslizante --}}
    <template x-if="panelOpen">
        <div>
            <div class="nf-panel-overlay" @click="panelOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
            </div>
            <div class="nf-panel"
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                <div class="nf-panel-header">
                    <h2>Adicionar Componente</h2>
                    <button class="nf-panel-close" @click="panelOpen = false">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="nf-panel-body">
                    @livewire('hardware.hardware-form')
                </div>
            </div>
        </div>
    </template>

</div>
