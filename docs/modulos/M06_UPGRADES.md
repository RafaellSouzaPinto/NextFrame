# M06 — Sugestões de Upgrade

## Propósito

Gera uma lista personalizada de upgrades recomendados com base no bottleneck atual do setup do usuário. Filtros por orçamento máximo (R$) e critério de prioridade (gargalo, FPS, custo-benefício). Cada sugestão mostra o componente atual vs o sugerido, ganho estimado de FPS e preço.

## Arquivos

| Arquivo | Papel |
|---------|-------|
| `app/Livewire/Upgrade/UpgradeSuggestions.php` | Componente Livewire — filtros + disparo do UpgradeEngine |
| `resources/views/livewire/upgrade/upgrade-suggestions.blade.php` | Lista de cards de upgrade |
| `resources/views/upgrade/index.blade.php` | Página — apenas @livewire |
| `app/Services/UpgradeEngine.php` | Service — lógica de sugestões (⏳ criar) |
| `app/Services/BottleneckEngine.php` | Service — reutilizado para calcular bottleneck atual |

## Tabelas Envolvidas

- `user_components` — setup atual do usuário
- `hardware_catalogs` — candidatos a upgrade

## Livewire Component

```php
// app/Livewire/Upgrade/UpgradeSuggestions.php
class UpgradeSuggestions extends Component
{
    public string $budget = '';
    public string $priorityFilter = 'bottleneck';
    public array $suggestions = [];
    public ?string $currentBottleneck = null;

    public function mount(): void { $this->loadSuggestions(); }

    public function updatedBudget(): void { $this->filterByBudget(); }

    public function setPriority(string $priority): void
    {
        $this->priorityFilter = $priority;
        $this->loadSuggestions();
    }

    private function loadSuggestions(): void
    {
        $userId = auth()->id();
        $userComponents = UserComponent::where('user_id', $userId)
            ->with('catalog')
            ->get()
            ->keyBy('type');

        $cpu = $userComponents->get('cpu')?->catalog;
        $gpu = $userComponents->get('gpu')?->catalog;

        if (!$cpu || !$gpu) {
            $this->suggestions = [];
            return;
        }

        $bottleneck = BottleneckEngine::calculate($cpu, $gpu);
        $this->currentBottleneck = $bottleneck['limited_by'];

        $budget = $this->budget ? (float) $this->budget : null;

        $this->suggestions = UpgradeEngine::suggest(
            $userComponents->map(fn($uc) => $uc->catalog?->toArray() ?? [])->toArray(),
            $bottleneck,
            $this->priorityFilter,
            $budget
        );

        // Marcar melhor custo-benefício
        if (!empty($this->suggestions)) {
            $bestIdx = collect($this->suggestions)
                ->keys()
                ->sortByDesc(fn($i) => $this->suggestions[$i]['fps_gain'] / max($this->suggestions[$i]['suggested']['price'] ?? 1, 1))
                ->first();
            $this->suggestions[$bestIdx]['is_best_value'] = true;
        }
    }

    private function filterByBudget(): void { $this->loadSuggestions(); }
}
```

## Service: UpgradeEngine

```php
// app/Services/UpgradeEngine.php
class UpgradeEngine
{
    public static function suggest(
        array $userComponents,   // keyed by type, each is HardwareCatalog array or []
        array $bottleneckResult,
        string $priority = 'bottleneck',
        ?float $budget = null
    ): array {
        $limitedBy    = $bottleneckResult['limited_by']; // 'cpu' ou 'gpu'
        $currentScore = $userComponents[$limitedBy]['benchmark_score'] ?? 0;
        $currentComp  = $userComponents[$limitedBy] ?? null;

        $query = HardwareCatalog::where('type', $limitedBy)
            ->where('benchmark_score', '>', $currentScore)
            ->when($budget, fn($q) => $q->where('price', '<=', $budget));

        $query->orderByDesc(
            match($priority) {
                'fps'          => 'benchmark_score',
                'cost_benefit' => DB::raw('benchmark_score / NULLIF(price, 0)'),
                default        => 'benchmark_score', // 'bottleneck'
            }
        );

        return $query->limit(6)->get()->map(function ($candidate) use ($currentComp) {
            $scoreDiff = $candidate->benchmark_score - ($currentComp['benchmark_score'] ?? 0);
            $fpsGain   = max((int) round($scoreDiff / 500), 1);

            return [
                'current'       => $currentComp,
                'suggested'     => $candidate->toArray(),
                'fps_gain'      => $fpsGain,
                'is_best_value' => false,
            ];
        })->values()->toArray();
    }
}
```

## View Principal (trecho de card de sugestão)

```html
@foreach($suggestions as $s)
<div class="nf-card nf-card--accent-top mb-3">
    <div class="row align-items-center">
        <!-- Atual -->
        <div class="col-5">
            <x-hardware-badge :type="$currentBottleneck" />
            <div class="mt-2 fw-bold">{{ $s['current']['name'] ?? 'Sem componente' }}</div>
            <div class="nf-mono {{ NF::scoreClass($s['current']['benchmark_score'] ?? 0) }}">
                {{ number_format($s['current']['benchmark_score'] ?? 0) }}
            </div>
        </div>
        <!-- Seta -->
        <div class="col-2 text-center" style="color: var(--nf-accent);">
            <i class="bi bi-arrow-right" style="font-size: 20px;"></i>
        </div>
        <!-- Sugerido -->
        <div class="col-5">
            <x-hardware-badge :type="$currentBottleneck" />
            <div class="mt-2 fw-bold">{{ $s['suggested']['name'] }}</div>
            <div class="nf-mono nf-score--high">{{ number_format($s['suggested']['benchmark_score']) }}</div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-3 mt-3">
        <span class="nf-badge nf-badge--green">+{{ $s['fps_gain'] }} FPS estimado</span>
        @if($s['suggested']['price'])
            <span class="nf-mono" style="color: var(--nf-text-secondary);">
                R$ {{ number_format($s['suggested']['price'], 2, ',', '.') }}
            </span>
        @endif
        @if($s['is_best_value'])
            <span class="nf-badge nf-badge--accent">Melhor Custo-Benefício</span>
        @endif
        <a href="/compare?left={{ $s['current']['id'] ?? '' }}&right={{ $s['suggested']['id'] }}&type={{ $currentBottleneck }}"
           class="nf-btn-ghost nf-btn-sm ms-auto">
            Comparar
        </a>
    </div>
</div>
@endforeach
```

## Fluxo Completo

```
1. GET /upgrade → mount() → loadSuggestions()
2. loadSuggestions():
   a. Carrega UserComponents com catalogs
   b. Se não tem CPU ou GPU → suggestions = [], retorna
   c. BottleneckEngine::calculate(cpu, gpu) → currentBottleneck = 'cpu' ou 'gpu'
   d. UpgradeEngine::suggest() → lista de candidatos do catálogo
   e. Marca is_best_value no melhor ratio fps_gain/price
3. Usuário digita orçamento → updatedBudget() (debounce 500ms) → loadSuggestions() com budget
4. Usuário clica prioridade → setPriority() → loadSuggestions() com nova ordenação
5. Usuário clica "Comparar" → navega para /compare?left=X&right=Y&type=T
```

## Regras Críticas

- `UpgradeEngine` só sugere componentes com `benchmark_score > currentScore` (melhorias, não downgrade)
- `fps_gain` é estimativa heurística — deixar claro na UI como "estimado"
- `is_best_value` é marcado no Livewire, não no Service, para manter o Service puro
- Empty state quando: (1) setup sem CPU/GPU cadastrado, (2) nenhum upgrade dentro do orçamento
- O link "Comparar" deve passar o `id` correto do componente atual (pode ser nulo se manual — tratar)
