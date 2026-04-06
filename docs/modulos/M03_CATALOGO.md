# M03 — Catálogo de Hardware

## Propósito

Exibe todos os componentes cadastrados globalmente com busca por texto, filtro por tipo (CPU/GPU/RAM/Storage) e ordenação por nome ou score. Permite adicionar um componente direto ao setup do usuário.

## Arquivos

| Arquivo | Papel |
|---------|-------|
| `app/Livewire/Catalog/HardwareCatalog.php` | Componente Livewire com filtros, paginação, addToMySetup |
| `resources/views/livewire/catalog/hardware-catalog.blade.php` | Grid de cards |
| `resources/views/catalog/index.blade.php` | Página simples — apenas @livewire |
| `app/Models/HardwareCatalog.php` | Model (⏳ criar) |

## Tabela Envolvida

**`hardware_catalogs`** (⏳ migration a criar)

| Coluna | Tipo | Obs |
|--------|------|-----|
| `id` | bigint unsigned | PK |
| `type` | enum(cpu,gpu,ram,storage) | Index |
| `name` | varchar(255) | Nome do produto |
| `benchmark_score` | int unsigned | Score de benchmark (Index) |
| `price` | decimal(10,2) nullable | Preço em reais |
| `specs` | json nullable | Specs adicionais: cores, clock, vram, etc. |
| `created_at` / `updated_at` | timestamp | — |

## Model

```php
// app/Models/HardwareCatalog.php
class HardwareCatalog extends Model
{
    protected $fillable = ['type', 'name', 'benchmark_score', 'price', 'specs'];
    protected $casts = ['specs' => 'array'];

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
```

## Livewire Component

```php
// app/Livewire/Catalog/HardwareCatalog.php
class HardwareCatalog extends Component
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = 'all';
    public string $sortBy = 'score_desc';
    public int $totalCount = 0;

    public function updatedSearch(): void    { $this->resetPage(); }
    public function updatedTypeFilter(): void { $this->resetPage(); }

    public function setType(string $type): void
    {
        $this->typeFilter = $type;
        $this->resetPage();
    }

    public function setSort(string $sort): void
    {
        $this->sortBy = $sort;
        $this->resetPage();
    }

    public function addToMySetup(int $catalogId): void
    {
        $item = \App\Models\HardwareCatalog::findOrFail($catalogId);
        UserComponent::updateOrCreate(
            ['user_id' => auth()->id(), 'type' => $item->type],
            ['catalog_id' => $catalogId, 'name' => $item->name]
        );
        session()->flash('success', "{$item->name} adicionado ao seu setup!");
    }

    public function render(): View
    {
        $query = \App\Models\HardwareCatalog::query()
            ->when($this->typeFilter !== 'all', fn($q) => $q->where('type', $this->typeFilter))
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"));

        [$col, $dir] = match($this->sortBy) {
            'score_asc'  => ['benchmark_score', 'asc'],
            'name'       => ['name', 'asc'],
            default      => ['benchmark_score', 'desc'],
        };
        $query->orderBy($col, $dir);

        $this->totalCount = $query->count();
        $components = $query->paginate(12);

        return view('livewire.catalog.hardware-catalog', compact('components'));
    }
}
```

## View Principal

Cards em grid 3 colunas (lg), 2 (md), 1 (sm):

```html
<div class="row g-3">
    @foreach($components as $item)
        <div class="col-lg-4 col-md-6 col-12" wire:key="catalog-{{ $item->id }}">
            <div class="nf-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <x-hardware-badge :type="$item->type" />
                    <span class="nf-mono" style="color: var(--nf-text-secondary); font-size: 12px;">
                        #{{ $item->id }}
                    </span>
                </div>

                <div class="nf-mono" style="font-size: 28px; font-weight: 700; color: var(--nf-text-primary);">
                    {{ number_format($item->benchmark_score) }}
                </div>
                <div style="font-size: 11px; color: var(--nf-text-secondary);">benchmark score</div>

                <h4 style="margin: 12px 0 4px; font-size: 15px;">{{ $item->name }}</h4>

                <!-- Barra de score proporcional -->
                <div style="background: var(--nf-bg-border); height: 3px; border-radius: 2px; margin: 8px 0;">
                    <div style="background: var(--nf-accent); height: 3px; width: {{ min(($item->benchmark_score / 40000) * 100, 100) }}%;"></div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    @if($item->price)
                        <span class="nf-mono" style="color: var(--nf-green);">
                            R$ {{ number_format($item->price, 2, ',', '.') }}
                        </span>
                    @endif
                    <button class="nf-btn nf-btn-sm" wire:click="addToMySetup({{ $item->id }})">
                        + Setup
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{ $components->links() }}
```

## Fluxo Completo

```
1. GET /catalog → HardwareCatalog::render()
2. Query com filtros ativos (typeFilter, search, sortBy)
3. $totalCount = query sem paginate
4. $components = query->paginate(12)
5. Grid exibe cards com score, nome, badge, preço
6. Usuário digita na busca → wire:model.live.debounce.300ms → updatedSearch() → resetPage() → re-render
7. Usuário clica tipo → setType() → resetPage() → re-render
8. Usuário clica "+ Setup" → addToMySetup(id) → updateOrCreate → flash success
```

## Regras Críticas

- `wire:key="catalog-{{ $item->id }}"` obrigatório no loop de paginação
- `resetPage()` em todos os métodos que alteram filtros (evita página vazia)
- `addToMySetup` usa `updateOrCreate` com `['user_id', 'type']` para respeitar o unique constraint
- Score máximo para a barra proporcional pode precisar de ajuste conforme o catálogo cresce (atualmente hardcoded em 40000)
