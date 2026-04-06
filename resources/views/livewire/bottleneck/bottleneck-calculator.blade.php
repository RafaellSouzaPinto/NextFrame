<div>
    <div class="nf-page-header">
        <h1>Calculadora de Bottleneck</h1>
        <p>Descubra qual componente está limitando a performance do seu PC.</p>
    </div>

    <div class="row g-4">

        {{-- Coluna de configuração --}}
        <div class="col-lg-5">
            <div class="nf-card">
                <div style="font-size:13px; font-weight:600; color:var(--nf-text-primary); margin-bottom:20px; display:flex; align-items:center; gap:8px;">
                    <i class="bi bi-sliders" style="color:var(--nf-accent);"></i> Configuração
                </div>

                {{-- Toggle usar meu setup --}}
                <div class="nf-form-group" style="padding-bottom:16px; border-bottom:1px solid var(--nf-bg-border);">
                    <label class="nf-toggle-wrap" style="cursor:pointer;">
                        <div class="nf-toggle">
                            <input type="checkbox" wire:model.live="useOwnSetup"
                                   @change="$wire.toggleUseOwnSetup()"
                                   {{ $useOwnSetup ? 'checked' : '' }}>
                            <span class="nf-toggle-slider"></span>
                        </div>
                        <div>
                            <div style="font-size:14px; font-weight:500; color:var(--nf-text-primary);">
                                Usar meu setup atual
                            </div>
                            <div style="font-size:12px; color:var(--nf-text-secondary);">
                                Pré-preenche com os seus componentes cadastrados
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Seleção manual --}}
                @if(!$useOwnSetup)
                <div style="padding-top:16px;">
                    <div class="nf-form-group">
                        <label class="nf-label">
                            <i class="bi bi-cpu" style="color:var(--nf-cpu);"></i> Processador (CPU)
                        </label>
                        <select class="nf-select" wire:model="cpuId">
                            <option value="">Selecione uma CPU...</option>
                            @foreach($cpuOptions as $cpu)
                                <option value="{{ $cpu['id'] }}">{{ $cpu['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="nf-form-group">
                        <label class="nf-label">
                            <i class="bi bi-gpu-card" style="color:var(--nf-gpu);"></i> Placa de Vídeo (GPU)
                        </label>
                        <select class="nf-select" wire:model="gpuId">
                            <option value="">Selecione uma GPU...</option>
                            @foreach($gpuOptions as $gpu)
                                <option value="{{ $gpu['id'] }}">{{ $gpu['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                {{-- Resolução --}}
                <div class="nf-form-group" style="padding-top:16px; border-top:1px solid var(--nf-bg-border);">
                    <label class="nf-label">Resolução de Referência</label>
                    <div class="nf-radio-group">
                        @foreach(['1080p', '1440p', '4K'] as $res)
                        <input type="radio" id="res_{{ $res }}" name="resolution_radio"
                               value="{{ $res }}" wire:model="resolution" style="display:none;">
                        <label for="res_{{ $res }}"
                               style="{{ $resolution === $res ? 'background:var(--nf-accent-glow); border-color:var(--nf-accent-border); color:var(--nf-accent);' : '' }}">
                            {{ $res }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Botão calcular --}}
                <button class="nf-btn" wire:click="calculate" style="width:100%; justify-content:center; margin-top:8px;">
                    <span wire:loading.remove wire:target="calculate">
                        <i class="bi bi-lightning-charge-fill"></i> Calcular Bottleneck
                    </span>
                    <span wire:loading wire:target="calculate">
                        <span class="nf-spinner"></span> Calculando...
                    </span>
                </button>
            </div>
        </div>

        {{-- Coluna de resultado --}}
        <div class="col-lg-7">
            @if(!$hasResult)
                <div class="nf-card" style="height:100%; display:flex; align-items:center; justify-content:center; min-height:300px;">
                    <div class="nf-empty-state" style="padding:0;">
                        <i class="bi bi-activity" style="color:var(--nf-accent);"></i>
                        <p>Configure os parâmetros ao lado e clique em <strong>"Calcular"</strong> para ver a análise do gargalo.</p>
                    </div>
                </div>
            @else
                <div style="display:flex; flex-direction:column; gap:16px;">

                    {{-- Bottleneck bar --}}
                    <x-bottleneck-bar
                        :cpuPct="$result['cpu_pct']"
                        :gpuPct="$result['gpu_pct']"
                        :severity="$result['severity']"
                        :limitedBy="$result['limited_by']"
                    />

                    {{-- Cards de resultado --}}
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="nf-card" style="text-align:center; padding:20px;">
                                <div style="font-size:11px; letter-spacing:.08em; text-transform:uppercase; color:var(--nf-text-secondary); margin-bottom:8px;">
                                    Limitado por
                                </div>
                                <div class="nf-mono" style="font-size:28px; font-weight:700; color:{{ $result['limited_by'] === 'cpu' ? 'var(--nf-cpu)' : 'var(--nf-gpu)' }};">
                                    {{ strtoupper($result['limited_by']) }}
                                </div>
                                <div style="font-size:13px; color:var(--nf-text-secondary); margin-top:4px;">
                                    em {{ $result['bottleneck_pct'] }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="nf-card" style="text-align:center; padding:20px;">
                                <div style="font-size:11px; letter-spacing:.08em; text-transform:uppercase; color:var(--nf-text-secondary); margin-bottom:8px;">
                                    Impacto estimado
                                </div>
                                <div class="nf-mono" style="font-size:28px; font-weight:700; color:var(--nf-red);">
                                    {{ $result['fps_impact'] }} fps
                                </div>
                                <div style="font-size:13px; color:var(--nf-text-secondary); margin-top:4px;">
                                    a {{ $resolution }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Explicação --}}
                    <div class="nf-card">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                            <i class="bi bi-info-circle" style="color:var(--nf-accent);"></i>
                            <span style="font-size:13px; font-weight:600; color:var(--nf-text-primary);">Análise Detalhada</span>
                        </div>
                        <p style="font-size:14px; color:var(--nf-text-secondary); line-height:1.7; margin:0;">
                            {{ $result['explanation'] }}
                        </p>
                    </div>

                    {{-- CTA --}}
                    <a href="{{ route('upgrade.index') }}" class="nf-btn" style="justify-content:center; width:100%;">
                        <i class="bi bi-arrow-up-circle"></i>
                        Ver upgrades recomendados para corrigir o bottleneck
                    </a>

                </div>
            @endif
        </div>

    </div>
</div>
