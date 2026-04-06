# M04 — Calculadora de Bottleneck

## Propósito

Calcula o percentual de bottleneck entre CPU e GPU dado um par de componentes e uma resolução. O usuário pode usar seu próprio setup cadastrado ou selecionar manualmente. O resultado mostra qual componente limita a performance, o impacto estimado em FPS e uma explicação em linguagem natural.

## Arquivos

| Arquivo | Papel |
|---------|-------|
| `app/Livewire/Bottleneck/BottleneckCalculator.php` | Componente Livewire — UI e disparo do cálculo |
| `resources/views/livewire/bottleneck/bottleneck-calculator.blade.php` | Layout coluna config + coluna resultado |
| `resources/views/bottleneck/index.blade.php` | Página — apenas @livewire |
| `app/Services/BottleneckEngine.php` | Service — lógica do cálculo (⏳ criar) |

## Tabelas Envolvidas

- `hardware_catalogs` — dados de CPU e GPU selecionados
- `user_components` — para carregar setup do usuário (quando `$useOwnSetup = true`)

## Livewire Component

```php
// app/Livewire/Bottleneck/BottleneckCalculator.php
class BottleneckCalculator extends Component
{
    public bool $useOwnSetup = true;
    public ?int $cpuId = null;
    public ?int $gpuId = null;
    public string $resolution = '1440p';
    public bool $loading = false;
    public bool $hasResult = false;
    public array $result = [];
    public array $cpuOptions = [];
    public array $gpuOptions = [];

    public function mount(): void
    {
        $this->cpuOptions = HardwareCatalog::ofType('cpu')
            ->orderByDesc('benchmark_score')
            ->get(['id', 'name'])
            ->toArray();
        $this->gpuOptions = HardwareCatalog::ofType('gpu')
            ->orderByDesc('benchmark_score')
            ->get(['id', 'name'])
            ->toArray();

        // Pré-carregar setup do usuário
        $userCpu = UserComponent::where('user_id', auth()->id())->where('type', 'cpu')->first();
        $userGpu = UserComponent::where('user_id', auth()->id())->where('type', 'gpu')->first();
        if ($userCpu?->catalog_id) $this->cpuId = $userCpu->catalog_id;
        if ($userGpu?->catalog_id) $this->gpuId = $userGpu->catalog_id;
    }

    public function toggleUseOwnSetup(): void
    {
        $this->useOwnSetup = !$this->useOwnSetup;
        $this->hasResult = false;
    }

    public function calculate(): void
    {
        $this->validate([
            'cpuId' => 'required|exists:hardware_catalogs,id',
            'gpuId' => 'required|exists:hardware_catalogs,id',
        ]);

        $this->loading = true;

        $cpu = HardwareCatalog::findOrFail($this->cpuId);
        $gpu = HardwareCatalog::findOrFail($this->gpuId);
        $this->result = BottleneckEngine::calculate($cpu, $gpu, $this->resolution);

        $this->hasResult = true;
        $this->loading = false;
    }
}
```

## Service: BottleneckEngine

```php
// app/Services/BottleneckEngine.php
class BottleneckEngine
{
    /**
     * @return array{
     *   bottleneck_pct: int,
     *   limited_by: 'cpu'|'gpu',
     *   severity: 'low'|'medium'|'high',
     *   cpu_pct: int,
     *   gpu_pct: int,
     *   fps_impact: int,
     *   explanation: string,
     *   cpu_name: string,
     *   gpu_name: string,
     * }
     */
    public static function calculate(
        HardwareCatalog $cpu,
        HardwareCatalog $gpu,
        string $resolution = '1440p'
    ): array {
        // GPU pesa mais em resoluções altas
        $gpuWeight = match($resolution) {
            '1080p' => 0.60,
            '1440p' => 0.70,
            '4K'    => 0.85,
            default => 0.70,
        };
        $cpuWeight = 1 - $gpuWeight;

        // Normalizar scores pela peso de cada componente na resolução
        $cpuNorm = $cpu->benchmark_score / $cpuWeight;
        $gpuNorm = $gpu->benchmark_score / $gpuWeight;

        $maxNorm = max($cpuNorm, $gpuNorm);
        $diff    = abs($cpuNorm - $gpuNorm);
        $pct     = (int) min(round(($diff / $maxNorm) * 100), 99);

        $limitedBy = $cpuNorm < $gpuNorm ? 'cpu' : 'gpu';

        $total   = $cpuNorm + $gpuNorm;
        $cpuPct  = (int) round(($cpuNorm / $total) * 100);
        $gpuPct  = 100 - $cpuPct;

        $severity = match(true) {
            $pct <= 10 => 'low',
            $pct <= 25 => 'medium',
            default    => 'high',
        };

        $fpsImpact = (int) round($pct * -0.4);

        $explanation = self::buildExplanation($limitedBy, $pct, $resolution, $cpu->name, $gpu->name);

        return [
            'bottleneck_pct' => $pct,
            'limited_by'     => $limitedBy,
            'severity'       => $severity,
            'cpu_pct'        => $cpuPct,
            'gpu_pct'        => $gpuPct,
            'fps_impact'     => $fpsImpact,
            'explanation'    => $explanation,
            'cpu_name'       => $cpu->name,
            'gpu_name'       => $gpu->name,
        ];
    }

    private static function buildExplanation(
        string $limitedBy, int $pct, string $resolution, string $cpuName, string $gpuName
    ): string {
        $component  = $limitedBy === 'cpu' ? $cpuName : $gpuName;
        $severityTxt = match(true) {
            $pct <= 10 => 'baixo',
            $pct <= 25 => 'moderado',
            default    => 'alto',
        };

        return "Em {$resolution}, o {$component} está limitando a performance em {$pct}% — nível {$severityTxt}. "
             . ($limitedBy === 'cpu'
                 ? "A GPU ({$gpuName}) é mais poderosa do que o processador consegue alimentar."
                 : "O processador ({$cpuName}) está aguardando a GPU processar os frames.");
    }
}
```

## Fluxo Completo

```
1. GET /bottleneck → mount() → carrega cpuOptions + gpuOptions + setup do usuário
2. Usuário escolhe resolução (radio: 1080p / 1440p / 4K)
3. Usuário alterna toggle "Usar meu setup" (ativa/desativa selects manuais)
4. Usuário clica "Calcular" → calculate()
   a. validate() — cpuId e gpuId devem existir na tabela
   b. $loading = true → spinner na UI
   c. BottleneckEngine::calculate(cpu, gpu, resolution)
   d. $result populado → $hasResult = true → $loading = false
5. View exibe resultado:
   - BottleneckBar (cpuPct, gpuPct, severity) — animada via Alpine
   - Card "Limitado por: CPU/GPU"
   - Card "Impacto estimado: -X fps"
   - Caixa de explicação em texto
   - Link para /upgrade
```

## Regras Críticas

- `BottleneckEngine` deve ser um service stateless (métodos estáticos) — sem injeção de dependência
- O resultado sempre retorna `limited_by` como `'cpu'` ou `'gpu'` — nunca nulo quando há resultado
- `fps_impact` é negativo (representa perda de frames)
- Nunca retornar `bottleneck_pct = 100` — capa em 99 no service
- `$loading = false` deve ocorrer mesmo em exceções — usar try/finally se necessário
- O cálculo com `severity` deve usar os mesmos thresholds de `window.NF.bottleneckSeverity()`: ≤10 = low, ≤25 = medium, >25 = high
