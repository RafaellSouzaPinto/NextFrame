# M07 — Dashboard de Performance

## Propósito

Painel principal do usuário autenticado. Apresenta um resumo completo do setup: scores de CPU e GPU, percentual de bottleneck, FPS estimado por resolução e por jogo, e quantidade de upgrades disponíveis. É a primeira tela vista após o login.

## Arquivos

| Arquivo | Papel |
|---------|-------|
| `app/Livewire/Dashboard/PerformanceDashboard.php` | Componente Livewire — agrega todos os dados |
| `resources/views/livewire/dashboard/performance-dashboard.blade.php` | Layout do dashboard |
| `resources/views/dashboard/index.blade.php` | Página — apenas @livewire |
| `app/Services/BottleneckEngine.php` | Reutilizado para calcular bottleneck |
| `app/Services/UpgradeEngine.php` | Reutilizado para contar upgrades |

## Tabelas Envolvidas

- `user_components` — setup do usuário
- `hardware_catalogs` — scores dos componentes

## Livewire Component

```php
// app/Livewire/Dashboard/PerformanceDashboard.php
class PerformanceDashboard extends Component
{
    public string $resolution = '1440p';
    public array $setup = [];
    public array $scores = ['cpu' => 0, 'gpu' => 0, 'bottleneck' => 0];
    public array $bottleneckSummary = ['limited_by' => null, 'pct' => 0, 'severity' => 'low'];
    public int $upgradeCount = 0;
    public array $fpsData = [];

    public function mount(): void { $this->loadData(); }

    public function switchResolution(string $res): void
    {
        $this->resolution = $res;
        $this->loadFpsData();
    }

    private function loadData(): void
    {
        $userComponents = UserComponent::where('user_id', auth()->id())
            ->with('catalog')
            ->get()
            ->keyBy('type');

        $this->setup = $userComponents->map(fn($uc) => [
            'type'  => $uc->type,
            'name'  => $uc->name,
            'score' => $uc->catalog?->benchmark_score ?? 0,
        ])->values()->toArray();

        $cpu = $userComponents->get('cpu')?->catalog;
        $gpu = $userComponents->get('gpu')?->catalog;

        if (!$cpu || !$gpu) return;

        $result = BottleneckEngine::calculate($cpu, $gpu, $this->resolution);

        $this->scores = [
            'cpu'        => $cpu->benchmark_score,
            'gpu'        => $gpu->benchmark_score,
            'bottleneck' => $result['bottleneck_pct'],
        ];

        $this->bottleneckSummary = [
            'limited_by' => $result['limited_by'],
            'pct'        => $result['bottleneck_pct'],
            'severity'   => $result['severity'],
        ];

        $componentsMap = $userComponents->map(fn($uc) => $uc->catalog?->toArray() ?? [])->toArray();
        $this->upgradeCount = count(UpgradeEngine::suggest($componentsMap, $result));

        $this->loadFpsData();
    }

    private function loadFpsData(): void
    {
        // Dados simulados de FPS por jogo — substituir por engine real futuramente
        $baseMap = ['1080p' => 144, '1440p' => 100, '4K' => 60];
        $base = $baseMap[$this->resolution] ?? 100;

        $games = [
            'Cyberpunk 2077' => 0.65,
            'Fortnite'       => 1.40,
            'CS2'            => 1.80,
            'Hogwarts Legacy' => 0.75,
            'The Witcher 4'  => 0.80,
        ];

        $this->fpsData = collect($games)->map(fn($mult, $game) => [
            'game' => $game,
            'fps'  => (int) ($base * $mult),
            'pct'  => (int) min(($base * $mult / ($base * 2)) * 100, 100),
        ])->values()->toArray();
    }
}
```

## View Principal (estrutura)

```html
<!-- Header do dashboard -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="nf-page-title">Olá, {{ auth()->user()->name }}</h1>
        <p class="nf-page-subtitle">Resumo do seu setup</p>
    </div>
    @if($bottleneckSummary['limited_by'])
        <span class="nf-badge nf-badge--{{ $bottleneckSummary['severity'] === 'low' ? 'green' : ($bottleneckSummary['severity'] === 'medium' ? 'amber' : 'red') }}">
            Gargalo: {{ strtoupper($bottleneckSummary['limited_by']) }} ({{ $bottleneckSummary['pct'] }}%)
        </span>
    @endif
</div>

<!-- 4 cards de métricas -->
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6 col-6">
        <x-card-stat label="CPU Score" :value="number_format($scores['cpu'])" icon="cpu" color="cpu" :bar-pct="min($scores['cpu'] / 400, 100)" />
    </div>
    <div class="col-lg-3 col-md-6 col-6">
        <x-card-stat label="GPU Score" :value="number_format($scores['gpu'])" icon="gpu-card" color="gpu" :bar-pct="min($scores['gpu'] / 400, 100)" />
    </div>
    <div class="col-lg-3 col-md-6 col-6">
        <x-card-stat label="Bottleneck" :value="$scores['bottleneck']" unit="%" icon="activity" color="red" :bar-pct="$scores['bottleneck']" />
    </div>
    <div class="col-lg-3 col-md-6 col-6">
        <x-card-stat label="Upgrades" :value="$upgradeCount" icon="arrow-up-circle" color="accent" />
    </div>
</div>

<!-- Tabs de resolução + gráfico de FPS -->
<div class="nf-card">
    <div class="nf-radio-group mb-3">
        <label class="nf-radio-option {{ $resolution === '1080p' ? 'active' : '' }}">
            <input type="radio" wire:model="resolution" value="1080p" wire:change="switchResolution('1080p')"> 1080p
        </label>
        <!-- 1440p, 4K -->
    </div>
    @foreach($fpsData as $item)
        <div class="d-flex align-items-center gap-3 mb-2">
            <span style="width: 160px; font-size: 13px;">{{ $item['game'] }}</span>
            <div style="flex: 1; background: var(--nf-bg-border); height: 6px; border-radius: 3px;">
                <div style="background: var(--nf-accent); height: 6px; width: {{ $item['pct'] }}%;"></div>
            </div>
            <span class="nf-mono" style="width: 60px; text-align: right;">{{ $item['fps'] }} fps</span>
        </div>
    @endforeach
</div>
```

## Fluxo Completo

```
1. GET /dashboard → mount() → loadData()
2. loadData():
   a. Carrega UserComponents com catalogs
   b. Monta $setup (lista para exibir)
   c. Se tem CPU e GPU → BottleneckEngine::calculate()
   d. Popula $scores, $bottleneckSummary, $upgradeCount
   e. loadFpsData() → FPS simulado por jogo
3. Usuário clica aba de resolução → switchResolution('4K')
   → $resolution = '4K' → loadFpsData() com novo base FPS → re-render
```

## Regras Críticas

- Dashboard mostra empty state quando setup não tem CPU + GPU — não exibir zeros como se fosse real
- `switchResolution()` deve recalcular o bottleneck também, pois os pesos mudam com a resolução
- `upgradeCount` usa `count()` não `->count()` (já é array retornado pelo UpgradeEngine)
- FPS por jogo é estimativa — deixar claro na UI ("estimado")
- Todos os dados são do usuário autenticado — sempre filtrar com `auth()->id()`
