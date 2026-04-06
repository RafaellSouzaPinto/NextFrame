# Skill: Livewire 3 — Referência para Claude Code

## Versão usada

Livewire **3.x** (via `livewire/livewire: ^3`)

## Padrões deste projeto

### Componentes — em `app/Livewire/[Modulo]/`
```
app/Livewire/
├── Dashboard/PerformanceDashboard.php
├── Hardware/HardwareManager.php
├── Hardware/HardwareForm.php
├── Catalog/HardwareCatalog.php
├── Bottleneck/BottleneckCalculator.php
├── Compare/ComponentComparer.php
└── Upgrade/UpgradeSuggestions.php
```

Views em `resources/views/livewire/[modulo]/[nome].blade.php`.

### Propriedades e validação com atributos PHP 8
```php
use Livewire\Attributes\Validate;

class HardwareForm extends Component
{
    #[Validate('required|in:cpu,gpu,ram,storage')]
    public string $type = 'cpu';

    #[Validate('required|min:2|max:150')]
    public string $name = '';
}
```

### Paginação com trait
```php
use Livewire\WithPagination;

class HardwareManager extends Component
{
    use WithPagination;

    // Sempre chamar resetPage() ao mudar filtros
    public function updatedSearch(): void { $this->resetPage(); }
}
```

### Debounce em inputs de busca
```html
<!-- 300ms para busca rápida -->
<input wire:model.live.debounce.300ms="search">

<!-- 500ms para inputs que disparam queries pesadas -->
<input wire:model.live.debounce.500ms="budget">
```

### Eventos Livewire → Alpine (via bridge)
```php
// No componente Livewire
$this->dispatch('open-panel', id: $this->editingId);
$this->dispatch('close-panel');
$this->dispatch('component-saved');
```

```javascript
// public/js/app.js converte para CustomEvent
// Alpine escuta com @event.window
```

```html
<!-- Na view Alpine -->
<div @open-panel.window="open = true; editId = $event.detail.id">
```

**Todo novo evento Livewire→Alpine precisa ser registrado em `public/js/app.js`.**

### Listeners de eventos entre componentes
```php
protected $listeners = ['component-saved' => 'handleComponentSaved'];

public function handleComponentSaved(): void
{
    $this->dispatch('close-panel');
    session()->flash('success', 'Salvo!');
    $this->resetPage();
}
```

### wire:key em loops — obrigatório
```html
@foreach($components as $item)
    <div wire:key="component-{{ $item->id }}">
        <!-- conteúdo -->
    </div>
@endforeach
```

### Loading states
```html
<button wire:click="calculate" wire:loading.attr="disabled">
    <span wire:loading.remove>Calcular</span>
    <span wire:loading><i class="bi bi-arrow-repeat nf-spinner"></i> Calculando…</span>
</button>
```

## Armadilhas Comuns

- **Esquecer `resetPage()` ao mudar filtros** → paginação mostra página vazia
- **Não registrar evento em `app.js`** → Alpine não recebe o evento Livewire
- **`wire:model` sem opção default em select** → valor inicial null causa erro de validação falso
- **`x-if` vs `x-show` no painel** → usar `x-if="panelOpen"` para recriar o Livewire ao reabrir (evita form com dados stale)
- **`wire:key` ausente em loops paginados** → re-render quebra a identidade dos elementos
- **Mudar propriedade em `updatedX()` que dispara outro `updatedX()`** → loop infinito

## Exemplos de Código Correto

```php
// render() sempre retorna view com dados
public function render(): View
{
    return view('livewire.hardware.hardware-manager', [
        'components' => UserComponent::where('user_id', auth()->id())
            ->with('catalog')
            ->paginate(10),
    ]);
}

// Dispatch com payload
$this->dispatch('open-panel', id: $this->editingId);

// Dispatch sem payload
$this->dispatch('component-saved');
```

```html
<!-- Autocomplete com dropdown absoluto -->
<div style="position: relative;">
    <input wire:model.live.debounce.300ms="catalogSearch" class="nf-input">
    @if(!empty($catalogResults))
        <div class="nf-dropdown" style="position: absolute; top: 100%; left: 0; width: 100%; z-index: 50;">
            @foreach($catalogResults as $r)
                <button wire:click="selectFromCatalog({{ $r['id'] }}, '{{ $r['name'] }}')" class="nf-dropdown-item">
                    <x-hardware-badge :type="$r['type']" /> {{ $r['name'] }}
                </button>
            @endforeach
        </div>
    @endif
</div>
```

## O Que NUNCA Fazer

- Nunca usar Alpine para fazer requisições ao servidor — isso é responsabilidade do Livewire
- Nunca usar `$this->emit()` (Livewire 2) — usar `$this->dispatch()` (Livewire 3)
- Nunca esquecer `wire:key` em loops com paginação
- Nunca chamar `Auth::attempt()` dentro de um Livewire component — lógica de auth está nas rotas
- Nunca misturar estado de UI (aberto/fechado de modal) com estado de dados do servidor — UI fica no Alpine
