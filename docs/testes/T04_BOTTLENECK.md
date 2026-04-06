# T04 — Testes: Calculadora de Bottleneck

## Objetivo

Validar a lógica do `BottleneckEngine` (unit tests puros) e o comportamento do `BottleneckCalculator` Livewire (feature tests). Garantir que os thresholds de severidade, o componente limitante e o impacto em FPS são calculados corretamente.

## Setup

```php
// BottleneckEngine é um service stateless — testar com new HardwareCatalog(['benchmark_score' => ...])
// tests/Unit/BottleneckEngineTest.php
// tests/Feature/BottleneckCalculatorTest.php
```

## Unit Tests — BottleneckEngine

### CT01 — CPU gargalo quando score muito menor que GPU
- **Dado**: CPU score 5000, GPU score 20000, resolução 1440p
- **Quando**: `BottleneckEngine::calculate(cpu, gpu, '1440p')`
- **Então**: `limited_by = 'cpu'`, `bottleneck_pct > 0`
- **Código**:
```php
public function test_cpu_e_gargalo_quando_score_menor(): void
{
    $cpu = new HardwareCatalog(['benchmark_score' => 5000,  'name' => 'CPU Fraca']);
    $gpu = new HardwareCatalog(['benchmark_score' => 20000, 'name' => 'GPU Forte']);

    $result = BottleneckEngine::calculate($cpu, $gpu, '1440p');

    $this->assertEquals('cpu', $result['limited_by']);
    $this->assertGreaterThan(0, $result['bottleneck_pct']);
}
```

### CT02 — GPU gargalo quando score muito menor que CPU
- **Dado**: CPU score 20000, GPU score 5000
- **Quando**: calcular
- **Então**: `limited_by = 'gpu'`
- **Código**:
```php
public function test_gpu_e_gargalo_quando_score_menor(): void
{
    $cpu = new HardwareCatalog(['benchmark_score' => 20000, 'name' => 'CPU Forte']);
    $gpu = new HardwareCatalog(['benchmark_score' => 5000,  'name' => 'GPU Fraca']);

    $result = BottleneckEngine::calculate($cpu, $gpu, '1440p');

    $this->assertEquals('gpu', $result['limited_by']);
}
```

### CT03 — Severidade low quando bottleneck <= 10%
- **Dado**: scores similares (bottleneck pequeno)
- **Quando**: calcular
- **Então**: `severity = 'low'`
- **Código**:
```php
public function test_severidade_low_quando_scores_similares(): void
{
    $cpu = new HardwareCatalog(['benchmark_score' => 15000, 'name' => 'CPU']);
    $gpu = new HardwareCatalog(['benchmark_score' => 15500, 'name' => 'GPU']);

    $result = BottleneckEngine::calculate($cpu, $gpu, '1440p');

    $this->assertEquals('low', $result['severity']);
    $this->assertLessThanOrEqual(10, $result['bottleneck_pct']);
}
```

### CT04 — bottleneck_pct nunca chega a 100
- **Dado**: GPU score 10x maior que CPU
- **Quando**: calcular
- **Então**: `bottleneck_pct <= 99`
- **Código**:
```php
public function test_bottleneck_pct_nunca_e_100(): void
{
    $cpu = new HardwareCatalog(['benchmark_score' => 1000,  'name' => 'CPU']);
    $gpu = new HardwareCatalog(['benchmark_score' => 40000, 'name' => 'GPU']);

    $result = BottleneckEngine::calculate($cpu, $gpu, '1440p');

    $this->assertLessThanOrEqual(99, $result['bottleneck_pct']);
}
```

### CT05 — fps_impact é negativo
- **Dado**: qualquer par com bottleneck > 0
- **Quando**: calcular
- **Então**: `fps_impact <= 0`
- **Código**:
```php
public function test_fps_impact_e_negativo_ou_zero(): void
{
    $cpu = new HardwareCatalog(['benchmark_score' => 5000,  'name' => 'CPU']);
    $gpu = new HardwareCatalog(['benchmark_score' => 20000, 'name' => 'GPU']);

    $result = BottleneckEngine::calculate($cpu, $gpu, '1440p');

    $this->assertLessThanOrEqual(0, $result['fps_impact']);
}
```

### CT06 — Resolução 4K aumenta peso da GPU
- **Dado**: mesmo par de componentes
- **Quando**: calcular em 1080p e em 4K
- **Então**: resultado em 4K tem maior impacto para CPU fraca (GPU pesa mais)
- **Código**:
```php
public function test_resolucao_4k_aumenta_peso_gpu(): void
{
    $cpu = new HardwareCatalog(['benchmark_score' => 5000,  'name' => 'CPU']);
    $gpu = new HardwareCatalog(['benchmark_score' => 20000, 'name' => 'GPU']);

    $r1080 = BottleneckEngine::calculate($cpu, $gpu, '1080p');
    $r4K   = BottleneckEngine::calculate($cpu, $gpu, '4K');

    // GPU pesa mais em 4K → bottleneck da CPU fraca é maior
    $this->assertGreaterThan($r1080['bottleneck_pct'], $r4K['bottleneck_pct']);
}
```

### CT07 — cpu_pct + gpu_pct = 100
- **Dado**: qualquer par
- **Quando**: calcular
- **Então**: `cpu_pct + gpu_pct == 100`
- **Código**:
```php
public function test_soma_cpu_gpu_pct_e_100(): void
{
    $cpu = new HardwareCatalog(['benchmark_score' => 12000, 'name' => 'CPU']);
    $gpu = new HardwareCatalog(['benchmark_score' => 18000, 'name' => 'GPU']);

    $result = BottleneckEngine::calculate($cpu, $gpu, '1440p');

    $this->assertEquals(100, $result['cpu_pct'] + $result['gpu_pct']);
}
```

## Feature Tests — BottleneckCalculator Livewire

### CT08 — Calcular com IDs válidos retorna resultado
- **Dado**: catálogo com CPU e GPU, usuário autenticado
- **Quando**: `calculate()` com `cpuId` e `gpuId` válidos
- **Então**: `hasResult = true`, `result` não vazio
- **Código**:
```php
public function test_calcular_retorna_resultado(): void
{
    $user = User::factory()->create();
    $cpu  = HardwareCatalog::factory()->create(['type' => 'cpu', 'benchmark_score' => 10000]);
    $gpu  = HardwareCatalog::factory()->create(['type' => 'gpu', 'benchmark_score' => 20000]);

    Livewire::actingAs($user)
        ->test(BottleneckCalculator::class)
        ->set('cpuId', $cpu->id)
        ->set('gpuId', $gpu->id)
        ->call('calculate')
        ->assertSet('hasResult', true);
}
```

### CT09 — Calcular sem selecionar CPU mostra erro de validação
- **Dado**: apenas GPU selecionada
- **Quando**: `calculate()`
- **Então**: erro de validação em `cpuId`
- **Código**:
```php
public function test_calcular_sem_cpu_mostra_erro(): void
{
    $user = User::factory()->create();
    $gpu  = HardwareCatalog::factory()->create(['type' => 'gpu']);

    Livewire::actingAs($user)
        ->test(BottleneckCalculator::class)
        ->set('gpuId', $gpu->id)
        ->call('calculate')
        ->assertHasErrors('cpuId');
}
```

## Comando para Rodar

```bash
# Unit tests do Engine
php artisan test --filter=BottleneckEngineTest
php artisan test tests/Unit/BottleneckEngineTest.php

# Feature tests do Livewire
php artisan test --filter=BottleneckCalculatorTest
php artisan test tests/Feature/BottleneckCalculatorTest.php

# Ambos
php artisan test --filter=Bottleneck
```
