<div>
    {{-- Tipo de componente --}}
    <div class="nf-form-group">
        <label class="nf-label">Tipo de Componente</label>
        <div class="nf-radio-group">
            @foreach(['cpu' => 'CPU', 'gpu' => 'GPU', 'ram' => 'RAM', 'storage' => 'Storage'] as $val => $label)
            <input type="radio" id="type_{{ $val }}" name="type_radio" value="{{ $val }}"
                   wire:model.live="type" style="display:none;">
            <label for="type_{{ $val }}"
                   style="{{ $type === $val ? 'background:var(--nf-accent-glow); border-color:var(--nf-accent-border); color:var(--nf-accent);' : '' }}">
                {{ $label }}
            </label>
            @endforeach
        </div>
    </div>

    {{-- Busca no catálogo --}}
    <div class="nf-form-group" style="position:relative;">
        <label class="nf-label">Buscar no Catálogo</label>
        <div style="position:relative;">
            <input
                type="text"
                class="nf-input"
                placeholder="Digite para buscar... ex: RTX 4070"
                wire:model.live.debounce.300ms="catalogSearch"
                autocomplete="off"
            >
            <i class="bi bi-search" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:var(--nf-text-muted); pointer-events:none;"></i>
        </div>

        @if(count($catalogResults) > 0)
        <div style="position:absolute; top:100%; left:0; right:0; background:var(--nf-bg-elevated); border:1px solid var(--nf-bg-border); border-radius:var(--nf-radius); z-index:50; margin-top:4px; box-shadow:var(--nf-shadow); overflow:hidden;">
            @foreach($catalogResults as $result)
            <button type="button"
                    class="nf-dropdown-item"
                    wire:click="selectFromCatalog({{ $result['id'] }}, '{{ $result['name'] }}')">
                <x-hardware-badge :type="$result['type']" />
                <span>{{ $result['name'] }}</span>
                <span class="nf-mono" style="margin-left:auto; font-size:12px; color:var(--nf-text-secondary);">
                    {{ number_format($result['score'] ?? 0, 0, ',', '.') }}
                </span>
            </button>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Nome manual (se não encontrar no catálogo) --}}
    <div class="nf-form-group">
        <label class="nf-label">
            Nome do Componente
            @if(!$catalogId)
                <span style="font-size:11px; color:var(--nf-text-muted); font-weight:400;"> — ou insira manualmente</span>
            @else
                <span class="nf-badge nf-badge--green" style="font-size:10px; margin-left:6px;">
                    <i class="bi bi-check-circle"></i> Do catálogo
                </span>
            @endif
        </label>
        <input
            type="text"
            class="nf-input @error('name') border-red-500 @enderror"
            placeholder="ex: Intel Core i5-12400F"
            wire:model="name"
        >
        @error('name')
            <div class="nf-input-error">{{ $message }}</div>
        @enderror
    </div>

    <hr class="nf-divider">

    <div style="display:flex; gap:10px;">
        <button type="button" class="nf-btn" wire:click="save" style="flex:1; justify-content:center;">
            <span wire:loading.remove wire:target="save">
                <i class="bi bi-check-lg"></i> Salvar
            </span>
            <span wire:loading wire:target="save">
                <span class="nf-spinner"></span> Salvando...
            </span>
        </button>
        <button type="button" class="nf-btn-ghost" wire:click="cancel">
            Cancelar
        </button>
    </div>
</div>
