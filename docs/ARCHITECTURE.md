# ARCHITECTURE.md — NextFrame

## Visão geral

NextFrame segue a arquitetura padrão Laravel com Livewire como camada de UI reativa. Não há API REST — toda comunicação servidor/cliente passa pelo protocolo AJAX do Livewire. Alpine.js lida exclusivamente com estado local de UI (modais, tabs, toggles).

```
Navegador
  ├── HTML renderizado (Blade + Livewire)
  ├── Alpine.js         → estado local de UI (sem chamadas ao servidor)
  ├── Livewire (AJAX)   → state reativo + ações no servidor
  └── public/js/app.js  → bridge Livewire→Alpine via CustomEvents

Servidor (Laravel 11)
  ├── routes/web.php           → rotas simples (views + auth inline)
  ├── app/Livewire/            → componentes reativos (lógica de negócio)
  ├── app/Models/              → Eloquent (parcialmente implementado)
  ├── app/Services/            → engines de negócio (planejadas)
  └── resources/views/         → Blade templates
```

---

## Rotas

Todas as rotas estão em `routes/web.php`. Não há controllers dedicados — as rotas públicas retornam views diretamente e as rotas de auth têm a lógica inline (validate → Auth::attempt/login → redirect).

### Rotas públicas

| Método | URI | Nome | View |
|--------|-----|------|------|
| GET | `/` | `welcome` | `welcome.blade.php` |
| GET | `/login` | `login` | `auth/login.blade.php` |
| POST | `/login` | `login.post` | — |
| GET | `/register` | `register` | `auth/register.blade.php` |
| POST | `/register` | `register.post` | — |
| POST | `/logout` | `logout` | — |

### Rotas autenticadas (middleware `auth`)

| Método | URI | Nome | Livewire principal |
|--------|-----|------|-------------------|
| GET | `/dashboard` | `dashboard` | `PerformanceDashboard` |
| GET | `/hardware` | `hardware.index` | `HardwareManager` + `HardwareForm` |
| GET | `/catalog` | `catalog.index` | `HardwareCatalog` |
| GET | `/bottleneck` | `bottleneck.index` | `BottleneckCalculator` |
| GET | `/compare` | `compare.index` | `ComponentComparer` |
| GET | `/upgrade` | `upgrade.index` | `UpgradeSuggestions` |

---

## Livewire Components

Todos em `app/Livewire/`. Cada componente tem uma view correspondente em `resources/views/livewire/`.

### `Dashboard/PerformanceDashboard`

**Arquivo:** `app/Livewire/Dashboard/PerformanceDashboard.php`
**View:** `livewire/dashboard/performance-dashboard.blade.php`

Responsabilidade: painel central do usuário autenticado. Exibe scores de CPU/GPU, resumo do bottleneck, contagem de upgrades disponíveis e FPS estimado por jogo.

Propriedades públicas:
- `$resolution` — resolução ativa (`'1080p'`, `'1440p'`, `'4K'`)
- `$setup` — array de componentes do usuário
- `$scores` — array `['cpu' => int, 'gpu' => int, 'bottleneck' => int]`
- `$bottleneckSummary` — array `['limited_by' => string|null, 'pct' => int, 'severity' => string]`
- `$upgradeCount` — total de upgrades sugeridos
- `$fpsData` — array de `['game', 'fps', 'pct']` para a resolução ativa

Ações:
- `switchResolution(string $res)` — troca a resolução e recarrega FPS data

**Status:** stub — todos os dados retornam zeros até os Models existirem.

---

### `Hardware/HardwareManager`

**Arquivo:** `app/Livewire/Hardware/HardwareManager.php`
**View:** `livewire/hardware/hardware-manager.blade.php`

Responsabilidade: lista paginada dos componentes do setup do usuário. Gerencia confirmação de exclusão e aciona o painel lateral de edição.

Propriedades públicas:
- `$confirmingDeleteId` — ID sendo confirmado para exclusão
- `$editingId` — ID sendo editado (abre o panel)

Ações:
- `confirmDelete(int $id)` / `cancelDelete()` / `deleteComponent(int $id)`
- `editComponent(int $id)` — despacha evento `open-panel` para abrir `HardwareForm`

Listeners: `component-saved` → fecha painel, flash de sucesso, reset de paginação.

**Status:** stub — `render()` retorna `collect([])` até `UserComponent` model existir.

---

### `Hardware/HardwareForm`

**Arquivo:** `app/Livewire/Hardware/HardwareForm.php`
**View:** `livewire/hardware/hardware-form.blade.php`

Responsabilidade: formulário de criação/edição de componente. Funciona como painel lateral (slide-in). Permite busca no catálogo (autocomplete) ou entrada manual de nome.

Propriedades públicas:
- `$componentId` — null para novo, int para edição
- `$type` — `'cpu'|'gpu'|'ram'|'storage'` (validado)
- `$name` — nome do componente (validado: required, min:2, max:150)
- `$catalogSearch` — texto de busca no catálogo
- `$catalogResults` — array de resultados da busca
- `$catalogId` — ID do catálogo selecionado (nullable — permite entrada manual)

Ações:
- `updatedCatalogSearch()` — busca no catálogo (debounce 300ms no template)
- `selectFromCatalog(int $id, string $name)` — preenche o form com item do catálogo
- `save()` — valida e salva, despacha `component-saved`
- `cancel()` — despacha `close-panel`

**Status:** stub — `save()` não persiste nada até `UserComponent` model existir.

---

### `Catalog/HardwareCatalog`

**Arquivo:** `app/Livewire/Catalog/HardwareCatalog.php`
**View:** `livewire/catalog/hardware-catalog.blade.php`

Responsabilidade: catálogo global de componentes com busca por texto, filtro por tipo e ordenação. Paginado. Permite adicionar item direto ao setup do usuário.

Propriedades públicas:
- `$search` — texto de busca (rebounce → resetPage)
- `$typeFilter` — `'all'|'cpu'|'gpu'|'ram'|'storage'`
- `$sortBy` — `'name'|'score'`
- `$totalCount` — total de registros (para exibição)

Ações:
- `setType(string $type)` / `setSort(string $sort)` / `addToMySetup(int $catalogId)`

**Status:** stub — `render()` retorna `collect([])` até `HardwareCatalog` model existir.

---

### `Bottleneck/BottleneckCalculator`

**Arquivo:** `app/Livewire/Bottleneck/BottleneckCalculator.php`
**View:** `livewire/bottleneck/bottleneck-calculator.blade.php`

Responsabilidade: interface da calculadora de bottleneck. O usuário pode usar seu próprio setup cadastrado ou selecionar CPU/GPU manualmente. Exibe resultado com a barra CPU vs GPU, percentual e explicação textual.

Propriedades públicas:
- `$useOwnSetup` — bool (toggle "usar meu setup")
- `$cpuId`, `$gpuId` — IDs do catálogo selecionados manualmente
- `$resolution` — `'1080p'|'1440p'|'4K'`
- `$loading`, `$hasResult` — estados de UI
- `$result` — array com `bottleneck_pct`, `limited_by`, `severity`, `cpu_pct`, `gpu_pct`, `fps_impact`, `explanation`, `cpu_name`, `gpu_name`
- `$cpuOptions`, `$gpuOptions` — opções dos selects (do catálogo)

Ações:
- `toggleUseOwnSetup()` — alterna entre setup próprio e seleção manual
- `calculate()` — chama `BottleneckEngine::calculate()` (a implementar)

**Status:** stub — `calculate()` retorna resultado fixo de demonstração.

---

### `Compare/ComponentComparer`

**Arquivo:** `app/Livewire/Compare/ComponentComparer.php`
**View:** `livewire/compare/component-comparer.blade.php`

Responsabilidade: comparador lado a lado de dois componentes do catálogo. Gera tabela de diferenças com delta percentual por especificação e veredicto automático.

Propriedades públicas:
- `$leftId`, `$rightId` — IDs dos componentes comparados
- `$typeFilter` — tipo ativo (`'gpu'` default)
- `$leftComponent`, `$rightComponent` — dados carregados
- `$diffTable` — array de linhas `['spec', 'left', 'right', 'delta']`
- `$verdict` — string com conclusão automática
- `$componentOptions` — opções dos selects (filtradas por tipo)

Ações:
- `updatedLeftId()` / `updatedRightId()` — recarrega e recalcula diff
- `updatedTypeFilter()` — reseta seleção e recarrega opções
- `swapComponents()` — inverte esquerda/direita

**Status:** stub — `buildDiff()` retorna arrays vazios até os Models existirem.

---

### `Upgrade/UpgradeSuggestions`

**Arquivo:** `app/Livewire/Upgrade/UpgradeSuggestions.php`
**View:** `livewire/upgrade/upgrade-suggestions.blade.php`

Responsabilidade: lista de sugestões de upgrade personalizada. Ordena por `priorityFilter` e filtra pelo `budget` informado.

Propriedades públicas:
- `$budget` — orçamento em reais (string/input)
- `$priorityFilter` — `'bottleneck'|'cost_benefit'|'performance'`
- `$suggestions` — array de sugestões
- `$currentBottleneck` — componente gargalo atual (string|null)

Ações:
- `setPriority(string $priority)` — altera critério e recarrega
- `updatedBudget()` — filtra por orçamento ao digitar

Dependências planejadas: `BottleneckEngine::calculate()` + `UpgradeEngine::suggest()`.

**Status:** stub — `loadSuggestions()` retorna array vazio.

---

## Blade Components reutilizáveis

Todos em `resources/views/components/`.

| Componente | Tag | Parâmetros | Descrição |
|------------|-----|------------|-----------|
| `alert` | `<x-alert>` | `type` (success/danger/warning/info), `message` | Alerta com auto-dismiss Alpine (5s) |
| `card-stat` | `<x-card-stat>` | `label`, `value`, `unit?`, `icon`, `color`, `barPct?` | Card de métrica com mini barra de progresso |
| `hardware-badge` | `<x-hardware-badge>` | `type` (cpu/gpu/ram/storage) | Badge colorido de tipo de hardware |
| `bottleneck-bar` | `<x-bottleneck-bar>` | `cpuPct`, `gpuPct`, `severity` | Barra dupla animada CPU vs GPU |

---

## Services planejados

Criar em `app/Services/`. Ainda não existem.

### `BottleneckEngine`

Calcula o percentual de bottleneck entre CPU e GPU dado um par de componentes do catálogo e uma resolução.

Interface esperada:
```php
BottleneckEngine::calculate(
    HardwareCatalog $cpu,
    HardwareCatalog $gpu,
    string $resolution = '1440p'
): array // ['pct', 'limited_by', 'severity', 'cpu_pct', 'gpu_pct', 'fps_impact', 'explanation']
```

### `UpgradeEngine`

Gera lista de sugestões de upgrade ordenadas por custo-benefício com base no setup atual e no resultado do BottleneckEngine.

Interface esperada:
```php
UpgradeEngine::suggest(
    Collection $userComponents,
    array $bottleneckResult,
    string $priority = 'bottleneck',
    ?float $budget = null
): array // array de sugestões ordenadas
```

---

## Bridge Livewire → Alpine

Definido em `public/js/app.js`. O padrão converte eventos do Livewire em `CustomEvent` do DOM para que componentes Alpine possam escutar com `@event.window`.

Eventos ativos:

| Evento Livewire | CustomEvent DOM | Quem escuta |
|-----------------|-----------------|-------------|
| `component-saved` | `component-saved` | `HardwareManager` (fecha panel + flash) |
| `close-panel` | `close-panel` | Layout do hardware.index |
| `open-panel` | `open-panel` | Layout do hardware.index (abre `HardwareForm`) |

Helpers globais em `window.NF`:
- `NF.fmt(n)` — formata número pt-BR
- `NF.bottleneckColor(pct)` — retorna variável CSS de cor pelo %
- `NF.bottleneckSeverity(pct)` — retorna `'low'|'medium'|'high'`
- `NF.scoreClass(score)` — retorna classe CSS `nf-score--*`

Thresholds de severidade: `≤ 10%` = low (verde), `≤ 25%` = medium (âmbar), `> 25%` = high (vermelho).

---

## Layouts

| Layout | Arquivo | Usado por |
|--------|---------|-----------|
| `layouts.app` | `resources/views/layouts/app.blade.php` | Área autenticada — navbar + sidebar + main |
| `layouts.guest` | `resources/views/layouts/guest.blade.php` | Landing, login, register |

O layout `app` inclui: navbar com avatar dropdown (Alpine), sidebar com navegação agrupada por seção, overlay mobile, sessão de flash messages.
