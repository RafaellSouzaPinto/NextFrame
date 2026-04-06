# M02 — Hardware Manager (Meu Setup)

## Propósito

Permite ao usuário cadastrar, editar e remover os componentes do seu PC. Suporta busca no catálogo global (autocomplete) ou entrada manual de nome. Cada usuário pode ter no máximo 1 componente de cada tipo (CPU, GPU, RAM, Storage).

## Arquivos

| Arquivo | Papel |
|---------|-------|
| `app/Livewire/Hardware/HardwareManager.php` | Lista paginada + ações de delete/editar |
| `app/Livewire/Hardware/HardwareForm.php` | Formulário no painel lateral — create/edit |
| `resources/views/livewire/hardware/hardware-manager.blade.php` | View da lista |
| `resources/views/livewire/hardware/hardware-form.blade.php` | View do formulário |
| `resources/views/hardware/index.blade.php` | Página — monta painel lateral Alpine |
| `app/Models/UserComponent.php` | Model (⏳ criar) |
| `public/js/app.js` | Bridge open-panel / close-panel / component-saved |

## Tabela Envolvida

**`user_components`** (⏳ migration a criar)

| Coluna | Tipo | Obs |
|--------|------|-----|
| `id` | bigint unsigned | PK |
| `user_id` | bigint unsigned | FK → users, cascade delete |
| `catalog_id` | bigint unsigned nullable | FK → hardware_catalogs, null on delete |
| `type` | enum(cpu,gpu,ram,storage) | — |
| `name` | varchar(255) | Nome do componente |
| `created_at` / `updated_at` | timestamp | — |
| UNIQUE | `(user_id, type)` | 1 por tipo por usuário |

## Model

```php
// app/Models/UserComponent.php
class UserComponent extends Model
{
    protected $fillable = ['user_id', 'catalog_id', 'type', 'name'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(HardwareCatalog::class, 'catalog_id');
    }
}
```

## Livewire Components

### HardwareManager

```php
// app/Livewire/Hardware/HardwareManager.php
class HardwareManager extends Component
{
    use WithPagination;

    public ?int $confirmingDeleteId = null;
    public ?int $editingId = null;

    protected $listeners = ['component-saved' => 'handleComponentSaved'];

    public function handleComponentSaved(): void
    {
        $this->dispatch('close-panel'); // Alpine fecha o painel
        session()->flash('success', 'Componente salvo com sucesso!');
        $this->resetPage();
    }

    public function confirmDelete(int $id): void   { $this->confirmingDeleteId = $id; }
    public function cancelDelete(): void           { $this->confirmingDeleteId = null; }

    public function deleteComponent(int $id): void
    {
        UserComponent::where('id', $id)->where('user_id', auth()->id())->delete();
        $this->confirmingDeleteId = null;
        session()->flash('success', 'Componente removido.');
    }

    public function editComponent(int $id): void
    {
        $this->editingId = $id;
        $this->dispatch('open-panel', id: $id); // Alpine abre painel com o ID
    }

    public function render(): View
    {
        return view('livewire.hardware.hardware-manager', [
            'components' => UserComponent::where('user_id', auth()->id())
                ->with('catalog')
                ->orderBy('type')
                ->paginate(10),
        ]);
    }
}
```

### HardwareForm

```php
// app/Livewire/Hardware/HardwareForm.php
class HardwareForm extends Component
{
    #[Validate('required|in:cpu,gpu,ram,storage')]
    public string $type = 'cpu';

    #[Validate('required|min:2|max:150')]
    public string $name = '';

    public ?int $componentId = null;
    public string $catalogSearch = '';
    public array $catalogResults = [];
    public ?int $catalogId = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $component = UserComponent::findOrFail($id);
            $this->componentId = $id;
            $this->type = $component->type;
            $this->name = $component->name;
            $this->catalogId = $component->catalog_id;
        }
    }

    public function updatedCatalogSearch(): void
    {
        if (strlen($this->catalogSearch) < 2) {
            $this->catalogResults = [];
            return;
        }
        $this->catalogResults = HardwareCatalog::query()
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->where('name', 'like', "%{$this->catalogSearch}%")
            ->orderByDesc('benchmark_score')
            ->limit(8)
            ->get(['id', 'type', 'name', 'benchmark_score'])
            ->toArray();
    }

    public function selectFromCatalog(int $id, string $name): void
    {
        $this->catalogId = $id;
        $this->name = $name;
        $this->catalogSearch = '';
        $this->catalogResults = [];
    }

    public function save(): void
    {
        $this->validate();
        UserComponent::updateOrCreate(
            ['user_id' => auth()->id(), 'type' => $this->type],
            ['catalog_id' => $this->catalogId, 'name' => $this->name]
        );
        $this->dispatch('component-saved');
    }

    public function cancel(): void { $this->dispatch('close-panel'); }
}
```

## View Principal (hardware/index.blade.php)

O painel lateral é controlado por Alpine no index. O `HardwareForm` é montado dentro do painel.

```html
@section('content')
<div x-data="{ panelOpen: false, editId: null }"
     @open-panel.window="panelOpen = true; editId = $event.detail.id ?? null"
     @close-panel.window="panelOpen = false; editId = null">

    @livewire('hardware.hardware-manager')

    <!-- Overlay + Panel -->
    <div class="nf-panel-overlay" x-show="panelOpen" @click="panelOpen = false"></div>
    <div class="nf-panel" x-show="panelOpen">
        <div class="nf-panel-header">
            <span x-text="editId ? 'Editar Componente' : 'Novo Componente'"></span>
            <button @click="panelOpen = false"><i class="bi bi-x"></i></button>
        </div>
        <div class="nf-panel-body">
            <template x-if="panelOpen">
                @livewire('hardware.hardware-form', key="hardware-form")
            </template>
        </div>
    </div>
</div>
@endsection
```

> **Nota:** O `wire:key` e o `x-if` garantem que o componente Livewire é recriado quando o painel abre, resetando o form.

## Fluxo Completo

```
[Adicionar componente]
1. Usuário clica "Adicionar Componente" → Alpine seta panelOpen = true
2. HardwareForm monta (mount sem id)
3. Usuário seleciona tipo → digita no catalogSearch → dropdown aparece
4. Usuário clica item → selectFromCatalog() preenche name + catalogId
5. Usuário clica "Salvar" → save() → validate() → updateOrCreate()
6. dispatch('component-saved') → app.js converte em CustomEvent
7. HardwareManager listener → fecha painel + flash success + resetPage()

[Editar componente]
1. Usuário clica "Editar" na linha → editComponent(id) no HardwareManager
2. dispatch('open-panel', id: $id) → Alpine abre painel com editId = id
3. HardwareForm monta com mount($id) → preenche form com dados existentes
4. Mesmo fluxo de save

[Excluir componente]
1. Usuário clica "Excluir" → confirmDelete(id) → exibe botões de confirmação inline
2. Usuário confirma → deleteComponent(id) → delete() + flash success
3. Usuário cancela → cancelDelete() → oculta confirmação
```

## Regras Críticas

- `updateOrCreate` com chave `['user_id', 'type']` — garante o unique constraint
- Nunca deletar sem verificar `where('user_id', auth()->id())` — autorização implícita
- `selectFromCatalog` deve limpar `catalogSearch` e `catalogResults` para fechar o dropdown
- O painel precisa de `x-if="panelOpen"` para recriar o Livewire ao reabrir (reset do form)
- `wire:key` no `HardwareForm` evita reusar instância stale entre aberturas do painel
