# 00 — Visão Geral da Arquitetura

Versão resumida para consulta rápida. Documento completo: [../ARCHITECTURE.md](../ARCHITECTURE.md).

---

## Stack

| Camada | Tecnologia |
|--------|-----------|
| PHP | 8.3 |
| Framework | Laravel 11 |
| UI reativa | Livewire 3 |
| Estado local | Alpine.js 3 |
| CSS | `public/css/custom.css` — prefixo `nf-*` |
| Grid | Bootstrap 5.3 — somente `.row` / `.col-*` |
| Ícones | Bootstrap Icons 1.11 |
| Banco | SQLite (local) / MySQL (produção) |
| Testes | PHPUnit 11 |

---

## Banco de Dados

| Tabela | Status | Colunas principais |
|--------|--------|--------------------|
| `users` | ✅ existe | id, name, email, password |
| `sessions` | ✅ existe | Laravel default |
| `cache` | ✅ existe | Laravel default |
| `jobs` | ✅ existe | Laravel default |
| `hardware_catalogs` | ⏳ planejada | id, type, name, benchmark_score, price, specs (JSON) |
| `user_components` | ⏳ planejada | id, user_id, catalog_id (nullable), type, name |

---

## Diagrama de Relacionamentos

```
users (1) ──────── (N) user_components
                          │
                          │ catalog_id (nullable)
                          │
                    hardware_catalogs (catálogo global)
```

---

## Fluxos Principais

```
[1] Usuário cadastra setup
    /hardware → HardwareManager → HardwareForm
    → UserComponent::updateOrCreate(user_id, type)

[2] Calcular bottleneck
    /bottleneck → BottleneckCalculator
    → BottleneckEngine::calculate(cpu, gpu, resolution)
    → resultado: {pct, limited_by, severity, fps_impact}

[3] Sugestões de upgrade
    /upgrade → UpgradeSuggestions
    → BottleneckEngine::calculate() (reutiliza)
    → UpgradeEngine::suggest(userComponents, bottleneckResult, priority, budget)
    → lista de HardwareCatalog ordenados por custo-benefício

[4] Comparar componentes
    /compare?left=X&right=Y&type=gpu
    → ComponentComparer::mount() pré-carrega
    → buildDiff() gera tabela de diferenças + veredicto
```

---

## Estrutura de Pastas

```
app/
├── Livewire/
│   ├── Dashboard/PerformanceDashboard.php
│   ├── Hardware/HardwareManager.php
│   ├── Hardware/HardwareForm.php
│   ├── Catalog/HardwareCatalog.php
│   ├── Bottleneck/BottleneckCalculator.php
│   ├── Compare/ComponentComparer.php
│   └── Upgrade/UpgradeSuggestions.php
├── Models/
│   ├── User.php                    ✅ existe
│   ├── HardwareCatalog.php         ⏳ planejado
│   └── UserComponent.php           ⏳ planejado
└── Services/
    ├── BottleneckEngine.php        ⏳ planejado
    └── UpgradeEngine.php           ⏳ planejado

resources/views/
├── layouts/app.blade.php           — área autenticada
├── layouts/guest.blade.php         — landing + auth
├── components/                     — Blade components reutilizáveis
│   ├── alert.blade.php
│   ├── card-stat.blade.php
│   ├── hardware-badge.blade.php
│   └── bottleneck-bar.blade.php
├── livewire/                       — views dos componentes Livewire
└── auth/                           — login + register

public/
├── css/custom.css                  — TODO o CSS visual (1515 linhas)
└── js/app.js                       — bridge Livewire→Alpine + window.NF

routes/web.php                      — TODAS as rotas (inline, sem controllers)
```

---

## Bridge Livewire → Alpine

Livewire não dispara `CustomEvent` — Alpine não consegue escutar eventos Livewire diretamente. O `public/js/app.js` faz a conversão:

```
Livewire.$dispatch('open-panel')
    → app.js converte em new CustomEvent('open-panel')
        → Alpine @open-panel.window="open = true"
```

Eventos ativos: `component-saved`, `close-panel`, `open-panel`.

---

## Regras Críticas

1. **Todo texto visível ao usuário em PT-BR**
2. **Nunca alterar migrations já aplicadas** — criar nova migration
3. **Bootstrap apenas para grid** — nunca componentes Bootstrap
4. **Tailwind não está em uso no HTML** — não adicionar classes Tailwind nos Blades
5. **Livewire para dados do servidor, Alpine para estado local**
6. **Novos eventos Livewire→Alpine precisam ser registrados em `public/js/app.js`**
