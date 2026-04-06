# M05 — Comparador de Componentes

## Propósito

Permite ao usuário selecionar dois componentes do catálogo do mesmo tipo e compará-los lado a lado. Gera tabela de diferenças com delta percentual por especificação e um veredicto automático. Suporta pré-carregamento via query params.

## Arquivos

| Arquivo | Papel |
|---------|-------|
| `app/Livewire/Compare/ComponentComparer.php` | Componente Livewire — seleção, diff e veredicto |
| `resources/views/livewire/compare/component-comparer.blade.php` | Layout 3 colunas: A | delta | B |
| `resources/views/compare/index.blade.php` | Página — passa query params |

## Tabela Envolvida

- `hardware_catalogs` — fonte dos dois componentes comparados

## Livewire Component

```php
// app/Livewire/Compare/ComponentComparer.php
class ComponentComparer extends Component
{
    public ?int $leftId = null;
    public ?int $rightId = null;
    public string $typeFilter = 'gpu';
    public ?array $leftComponent = null;
    public ?array $rightComponent = null;
    public array $diffTable = [];
    public ?string $verdict = null;
    public array $componentOptions = [];

    public function mount(?int $left = null, ?int $right = null, string $type = 'gpu'): void
    {
        $this->typeFilter = $type;
        $this->leftId  = $left;
        $this->rightId = $right;
        $this->loadOptions();
        if ($left)  $this->loadLeft();
        if ($right) $this->loadRight();
        if ($left && $right) $this->buildDiff();
    }

    public function updatedLeftId(): void  { $this->loadLeft();  $this->buildDiff(); }
    public function updatedRightId(): void { $this->loadRight(); $this->buildDiff(); }

    public function updatedTypeFilter(): void
    {
        $this->leftId = $this->rightId = null;
        $this->leftComponent = $this->rightComponent = null;
        $this->diffTable = [];
        $this->verdict = null;
        $this->loadOptions();
    }

    public function swapComponents(): void
    {
        [$this->leftId, $this->rightId] = [$this->rightId, $this->leftId];
        [$this->leftComponent, $this->rightComponent] = [$this->rightComponent, $this->leftComponent];
        $this->buildDiff();
    }

    private function loadOptions(): void
    {
        $this->componentOptions = HardwareCatalog::ofType($this->typeFilter)
            ->orderByDesc('benchmark_score')
            ->get(['id', 'name', 'benchmark_score'])
            ->toArray();
    }

    private function loadLeft(): void
    {
        $this->leftComponent = $this->leftId
            ? HardwareCatalog::find($this->leftId)?->toArray()
            : null;
    }

    private function loadRight(): void
    {
        $this->rightComponent = $this->rightId
            ? HardwareCatalog::find($this->rightId)?->toArray()
            : null;
    }

    private function buildDiff(): void
    {
        if (!$this->leftComponent || !$this->rightComponent) {
            $this->diffTable = [];
            $this->verdict = null;
            return;
        }

        $l = $this->leftComponent['benchmark_score'];
        $r = $this->rightComponent['benchmark_score'];
        $delta = $l > 0 ? (int) round((($r - $l) / $l) * 100) : 0;

        $this->diffTable = [
            ['spec' => 'Benchmark Score', 'left' => $l, 'right' => $r, 'delta' => $delta],
        ];

        // Specs adicionais do JSON, se existirem
        $leftSpecs  = $this->leftComponent['specs']  ?? [];
        $rightSpecs = $this->rightComponent['specs'] ?? [];
        foreach (array_keys(array_merge($leftSpecs, $rightSpecs)) as $key) {
            $lv = $leftSpecs[$key]  ?? '-';
            $rv = $rightSpecs[$key] ?? '-';
            $this->diffTable[] = ['spec' => $key, 'left' => $lv, 'right' => $rv, 'delta' => 0];
        }

        // Veredicto
        $winner = $l >= $r ? $this->leftComponent['name'] : $this->rightComponent['name'];
        $margin = abs($delta);
        $this->verdict = $margin < 5
            ? "Desempenho equivalente — diferença inferior a 5%."
            : "{$winner} tem {$margin}% de vantagem neste comparativo.";
    }

    public function render(): View
    {
        return view('livewire.compare.component-comparer');
    }
}
```

## View Principal (trecho da tabela de diferenças)

```html
<!-- Cabeçalho com scores e badges -->
<div class="row g-0 mb-3">
    <div class="col-5 text-center">
        <x-hardware-badge :type="$typeFilter" />
        <div class="nf-mono mt-2" style="font-size: 22px; font-weight: 700;">
            {{ number_format($leftComponent['benchmark_score']) }}
        </div>
        <div>{{ $leftComponent['name'] }}</div>
    </div>
    <div class="col-2 d-flex align-items-center justify-content-center">
        <button wire:click="swapComponents" class="nf-btn-ghost nf-btn-sm">
            <i class="bi bi-arrow-left-right"></i>
        </button>
    </div>
    <div class="col-5 text-center">
        <!-- mesmo para rightComponent -->
    </div>
</div>

<!-- Tabela de diferenças -->
<table class="nf-table nf-table--compare">
    <thead>
        <tr>
            <th>Especificação</th>
            <th class="text-center">{{ $leftComponent['name'] }}</th>
            <th class="text-center">Δ</th>
            <th class="text-center">{{ $rightComponent['name'] }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($diffTable as $row)
        <tr>
            <td>{{ $row['spec'] }}</td>
            <td class="text-center nf-mono">{{ $row['left'] }}</td>
            <td class="text-center nf-mono nf-delta {{ $row['delta'] > 0 ? 'positive' : ($row['delta'] < 0 ? 'negative' : '') }}">
                {{ $row['delta'] > 0 ? '+' : '' }}{{ $row['delta'] !== 0 ? $row['delta'] . '%' : '—' }}
            </td>
            <td class="text-center nf-mono">{{ $row['right'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Veredicto -->
@if($verdict)
<div class="nf-card nf-card--accent-top mt-3">
    <i class="bi bi-trophy"></i> {{ $verdict }}
</div>
@endif
```

## Fluxo Completo

```
1. GET /compare?left=5&right=12&type=gpu
2. mount(left: 5, right: 12, type: 'gpu')
   → typeFilter = 'gpu'
   → loadOptions() → componentOptions com todos os GPUs
   → loadLeft(5) + loadRight(12) → arrays dos componentes
   → buildDiff() → diffTable + verdict
3. Usuário muda select da esquerda → updatedLeftId() → loadLeft() + buildDiff()
4. Usuário clica Swap → swapComponents() → inverte left/right + buildDiff()
5. Usuário muda typeFilter → updatedTypeFilter() → reseta tudo + loadOptions()
```

## Regras Críticas

- `mount()` deve aceitar `?int $left = null` e `?int $right = null` — query params opcionais
- `buildDiff()` deve verificar se ambos os componentes estão carregados antes de calcular
- Delta é calculado em relação ao componente da esquerda: `((right - left) / left) * 100`
- Delta positivo = B melhor que A (verde); negativo = A melhor que B (vermelho)
- Diferença < 5% → veredicto de "equivalente" em vez de declarar vencedor
