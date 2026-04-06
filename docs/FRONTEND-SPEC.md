# FRONTEND-SPEC.md — NextFrame

Documento de referência visual completo. Use este arquivo para reconstruir ou estender o frontend fielmente, sem inventar estilos.

---

## Stack Frontend

| Item | Tecnologia |
|------|-----------|
| Framework PHP | Laravel 11 (Blade templates) |
| UI reativa | Livewire 3 (AJAX, SSR) |
| Estado local de UI | Alpine.js 3 (CDN) |
| CSS | `public/css/custom.css` — 1515 linhas, prefixo `nf-*` |
| Grid/Reset | Bootstrap 5.3.2 — **somente** `.row` / `.col-*` / reset |
| Ícones | Bootstrap Icons 1.11 (CDN, tag `<i class="bi bi-*">`) |
| Fontes | Inter (corpo) + JetBrains Mono (scores/dados) — Google Fonts |
| Build | Vite — apenas compila `resources/css/app.css` (Tailwind stubs) |
| JS global | `public/js/app.js` — bridge Livewire→Alpine + helpers `window.NF` |

> **Atenção:** Tailwind está no `app.css` apenas como diretivas (`@tailwind base/components/utilities`). Não usa classes Tailwind nos templates.

---

## Paleta de Cores

Todas as cores são variáveis CSS declaradas em `:root` em `public/css/custom.css`.

### Fundos

| Token CSS | Hex | Uso |
|-----------|-----|-----|
| `--nf-bg-base` | `#0d0f14` | Fundo da página (`<body>`) |
| `--nf-bg-surface` | `#13161e` | Sidebar, navbar |
| `--nf-bg-elevated` | `#1a1e28` | Cards, painéis, dropdowns |
| `--nf-bg-highlight` | `#21263a` | Hover de linhas de tabela, selecionado |
| `--nf-bg-border` | `#1f2535` | Bordas de cards e separadores |
| `--nf-bg-border-subtle` | `#161b28` | Bordas muito sutis |

### Accent (Roxo — cor primária)

| Token CSS | Hex / Valor | Uso |
|-----------|-------------|-----|
| `--nf-accent` | `#7b6ef6` | Botão primário, links ativos, destaques |
| `--nf-accent-dim` | `#4a42b0` | Hover do botão primário |
| `--nf-accent-glow` | `rgba(123,110,246,0.20)` | Glow/halo de elementos accent |
| `--nf-accent-border` | `rgba(123,110,246,0.35)` | Borda sutil de elementos accent |

### Cores Semânticas de Hardware

| Token CSS | Hex | Uso |
|-----------|-----|-----|
| `--nf-cpu` | `#22d3ee` | Tudo relacionado a CPU (badge, barra, score) |
| `--nf-cpu-glow` | `rgba(34,211,238,0.15)` | Glow do CPU |
| `--nf-gpu` | `#a78bfa` | Tudo relacionado a GPU |
| `--nf-gpu-glow` | `rgba(167,139,250,0.15)` | Glow do GPU |
| `--nf-ram` | `#34d399` | Tudo relacionado a RAM |
| `--nf-storage` | `#fb923c` | Tudo relacionado a Storage |

### Status

| Token CSS | Hex | Uso |
|-----------|-----|-----|
| `--nf-green` | `#22c55e` | Sucesso, FPS gain, score alto |
| `--nf-green-glow` | `rgba(34,197,94,0.15)` | Glow verde |
| `--nf-amber` | `#f59e0b` | Aviso, severidade média |
| `--nf-amber-glow` | `rgba(245,158,11,0.15)` | Glow âmbar |
| `--nf-red` | `#ef4444` | Erro, severidade alta, deleção |
| `--nf-red-glow` | `rgba(239,68,68,0.15)` | Glow vermelho |

### Tipografia

| Token CSS | Valor | Uso |
|-----------|-------|-----|
| `--nf-text-primary` | `#e2e8f0` | Texto principal do corpo |
| `--nf-text-secondary` | `#6b7a99` | Labels, subtítulos, textos auxiliares |
| `--nf-text-muted` | `#374151` | Placeholder, texto muito apagado |
| `--nf-font-base` | `'Inter', sans-serif` | Fonte padrão |
| `--nf-font-mono` | `'JetBrains Mono', monospace` | Scores, dados numéricos, código |

### Layout

| Token CSS | Valor | Uso |
|-----------|-------|-----|
| `--nf-sidebar-width` | `220px` | Largura da sidebar fixa |
| `--nf-navbar-height` | `56px` | Altura do navbar |
| `--nf-radius` | `4px` | Border-radius padrão |
| `--nf-radius-sm` | `2px` | Border-radius pequeno |
| `--nf-radius-lg` | `6px` | Border-radius grande |
| `--nf-shadow` | `0 1px 8px rgba(0,0,0,0.4)` | Sombra padrão de cards |

---

## Tipografia

| Elemento | Fonte | Peso | Tamanho | Cor |
|----------|-------|------|---------|-----|
| `<body>` | Inter | 400 | 14px | `--nf-text-primary` |
| Heading da página | Inter | 700 | 22px | `--nf-text-primary` |
| Subtitle da página | Inter | 400 | 14px | `--nf-text-secondary` |
| `.nf-label` | Inter | 600 | 13px | `--nf-text-secondary` |
| Valor de `.nf-card-stat` | JetBrains Mono | 700 | 28px | `--nf-text-primary` |
| Badge `.nf-badge` | JetBrains Mono | 600 | 11px | varia por tipo |
| Cabeçalho de tabela | Inter | 600 | 11px | `--nf-text-secondary` |
| Score colorido | JetBrains Mono | 700 | varia | `--nf-green/amber/red` |
| Placeholder de input | Inter | 400 | 14px | `--nf-text-muted` |

---

## Componentes Globais

### Botões

```html
<!-- Primário -->
<button class="nf-btn">Calcular</button>

<!-- Secundário (ghost) -->
<button class="nf-btn-ghost">Cancelar</button>

<!-- Destrutivo -->
<button class="nf-btn-danger">Excluir</button>

<!-- Primário pequeno -->
<button class="nf-btn nf-btn-sm">Salvar</button>

<!-- Com ícone e loading (Livewire) -->
<button class="nf-btn" wire:click="save" wire:loading.attr="disabled">
    <span wire:loading.remove>Salvar</span>
    <span wire:loading><i class="bi bi-arrow-repeat nf-spinner"></i> Salvando…</span>
</button>
```

Estilos chave:
- `.nf-btn` → `background: var(--nf-accent)`, padding `9px 20px`, radius `var(--nf-radius)`, white text
- `.nf-btn-ghost` → transparent, border `1px solid var(--nf-bg-border)`, hover eleva background
- `.nf-btn-sm` → padding `6px 14px`, font-size `13px`

### Inputs e Labels

```html
<div class="nf-form-group">
    <label class="nf-label">Nome do componente</label>
    <input type="text" class="nf-input" wire:model="name" placeholder="Ex: RTX 4080">
    @error('name') <span class="nf-field-error">{{ $message }}</span> @enderror
</div>

<select class="nf-select" wire:model="typeFilter">
    <option value="all">Todos</option>
    <option value="cpu">CPU</option>
</select>
```

Estilos chave:
- `.nf-form-group` → `margin-bottom: 18px`
- `.nf-label` → 13px semi-bold, cor secondary, `margin-bottom: 6px`
- `.nf-input`, `.nf-select` → `background: var(--nf-bg-elevated)`, borda sutil, focus muda borda para accent
- `.nf-select` → seta customizada via `background-image` SVG
- `.nf-field-error` → 12px, cor `--nf-red`, display block, margin-top 4px

### Cards

```html
<!-- Card base -->
<div class="nf-card">
    <p>Conteúdo</p>
</div>

<!-- Card com borda colorida no topo -->
<div class="nf-card nf-card--cpu-top">...</div>
<div class="nf-card nf-card--gpu-top">...</div>
<div class="nf-card nf-card--accent-top">...</div>
```

Estilos chave:
- `.nf-card` → `background: var(--nf-bg-elevated)`, border, padding `20px 24px`, radius lg, shadow
- Hover → borda transiciona para `var(--nf-accent-border)`
- Variantes `--*-top` → `border-top: 2px solid var(--nf-*)` (2px top, resto sutil)

### Card de Métrica (Blade component)

```blade
<x-card-stat
    label="CPU Score"
    value="{{ $scores['cpu'] }}"
    icon="cpu"
    color="cpu"
    :bar-pct="$scores['cpu'] / 200"
/>
```

Renderiza: label + icon colorido no topo, valor grande em monospace, unidade opcional, barra de progresso de 3px.

### Badges

```html
<span class="nf-badge nf-badge--cpu">CPU</span>
<span class="nf-badge nf-badge--gpu">GPU</span>
<span class="nf-badge nf-badge--ram">RAM</span>
<span class="nf-badge nf-badge--storage">STORAGE</span>
<span class="nf-badge nf-badge--accent">GARGALO</span>
<span class="nf-badge nf-badge--green">+15 FPS</span>
<span class="nf-badge nf-badge--amber">MÉDIO</span>
<span class="nf-badge nf-badge--red">ALTO</span>
```

Estilos chave:
- Base: inline-flex, padding `3px 8px`, font-size 11px, `font-family: mono`, border-radius sm
- Cada variante tem: `background` tintado, `border` colorida, `color` da cor respectiva

Blade component: `<x-hardware-badge type="cpu" />`

### Alertas / Flash Messages

```blade
<x-alert type="success" message="Componente salvo com sucesso!" />
```

```html
<!-- Renderizado -->
<div class="nf-alert nf-alert--success" x-data="{ show: true }" x-show="show"
     x-init="setTimeout(() => show = false, 5000)"
     x-transition:leave="...">
    <i class="bi bi-check-circle"></i>
    <span>Mensagem</span>
    <button @click="show = false"><i class="bi bi-x"></i></button>
</div>
```

Variantes: `nf-alert--success` (verde), `nf-alert--danger` (vermelho), `nf-alert--warning` (âmbar), `nf-alert--info` (accent).

### Toggle / Switch

```html
<label class="nf-toggle">
    <input type="checkbox" wire:model="useOwnSetup" @change="$wire.toggleUseOwnSetup()">
    <span class="nf-toggle-slider"></span>
</label>
```

Estilos: 40x22px, background muda para accent quando checked, dot desliza com transição.

### Radio Group (tipo botões)

```html
<div class="nf-radio-group">
    <label class="nf-radio-option {{ $resolution === '1080p' ? 'active' : '' }}">
        <input type="radio" wire:model="resolution" value="1080p"> 1080p
    </label>
    <label class="nf-radio-option {{ $resolution === '1440p' ? 'active' : '' }}">
        <input type="radio" wire:model="resolution" value="1440p"> 1440p
    </label>
</div>
```

Estilos: flex row, cada opção tem padding e borda, `.active` tem borda accent e background tintado.

### Spinner

```html
<i class="bi bi-arrow-repeat nf-spinner"></i>
```

Estilos: `animation: spin 0.8s linear infinite`.

---

## Layout das Telas

### Layout Autenticado (`layouts/app.blade.php`)

```
┌─────────────────────────────────────────────────────┐
│  NAVBAR (.nf-navbar) — height: 56px, fixed top      │
│  [logo]  [dashboard] [setup] [catálogo] [bottleneck] [avatar▼] │
└─────────────────────────────────────────────────────┘
┌──────────────┬──────────────────────────────────────┐
│ SIDEBAR      │ MAIN (.nf-main)                      │
│ (.nf-sidebar)│ padding: 28px                        │
│ width: 220px │ [flash messages]                     │
│ sticky top   │ @yield('content')                    │
│              │                                      │
│ [Análise]    │                                      │
│  Dashboard   │                                      │
│  Bottleneck  │                                      │
│  Upgrades    │                                      │
│              │                                      │
│ [Hardware]   │                                      │
│  Meu Setup   │                                      │
│  Catálogo    │                                      │
│  Comparar    │                                      │
└──────────────┴──────────────────────────────────────┘
```

### Layout Guest (`layouts/guest.blade.php`)

```
┌─────────────────────────────────────────────────────┐
│  NAVBAR GUEST — fixed, centered                     │
│  [logo]                    [Entrar] [Criar Conta]   │
└─────────────────────────────────────────────────────┘
│  @yield('content')                                  │
```

### Painel Lateral (`HardwareForm`)

```
┌────────────────────────────────────────────────────────────────┐
│ OVERLAY (.nf-panel-overlay) rgba(0,0,0,0.65)                  │
│         ┌──────────────────────────────────────┐              │
│         │ PANEL (.nf-panel)                    │              │
│         │ width: 420px, height: 100vh, fixed   │              │
│         │ right: 0                             │              │
│         ├──────────────────────────────────────┤              │
│         │ HEADER: título + botão fechar        │              │
│         ├──────────────────────────────────────┤              │
│         │ BODY (scrollable)                    │              │
│         │ @livewire('hardware.hardware-form')   │              │
│         └──────────────────────────────────────┘              │
└────────────────────────────────────────────────────────────────┘
```

---

## Responsividade

| Breakpoint | Comportamento |
|-----------|---------------|
| `≥ 992px` (lg) | Sidebar visível, navbar com links, grid 3 colunas no catálogo |
| `< 992px` (md/sm) | Sidebar oculta (transform translateX(-100%)), hamburger no navbar |
| Mobile | Sidebar abre como overlay fixo, grid passa a 1 coluna |

Bootstrap grid usado:
- `.col-lg-3` + `.col-md-6` + `.col-12` — cards de catálogo
- `.col-lg-6` — colunas da calculadora de bottleneck
- `.col-lg-4` — card-stats no dashboard (3 por linha)

---

## Ícones

Biblioteca: **Bootstrap Icons 1.11** via CDN.

Ícones principais usados:

| Ícone | Classe | Onde |
|-------|--------|------|
| CPU | `bi-cpu` | Badge CPU, card-stat, sidebar |
| GPU | `bi-gpu-card` | Badge GPU, card-stat |
| RAM | `bi-memory` | Badge RAM |
| Storage | `bi-device-hdd` | Badge Storage |
| Dashboard | `bi-speedometer2` | Sidebar link |
| Bottleneck | `bi-activity` | Sidebar link |
| Upgrade | `bi-arrow-up-circle` | Sidebar link |
| Setup | `bi-pc-display` | Sidebar link |
| Catálogo | `bi-collection` | Sidebar link |
| Comparar | `bi-arrows-collapse-vertical` | Sidebar link |
| Sucesso | `bi-check-circle` | Alert success |
| Erro | `bi-exclamation-circle` | Alert danger |
| Aviso | `bi-exclamation-triangle` | Alert warning |
| Info | `bi-info-circle` | Alert info |
| Fechar | `bi-x` | Botão fechar alert/panel |
| Spinner | `bi-arrow-repeat nf-spinner` | Loading states |
| Troféu | `bi-trophy` | Veredicto no comparador |
| Play | `bi-play-circle-fill` | Logo do NextFrame |

---

## Animações e Transições

| Elemento | Animação | Duração |
|----------|----------|---------|
| Alerta (saída) | `x-transition:leave` Alpine — fade + slide | 300ms |
| Painel lateral | `transform: translateX(100%)` → `translateX(0)` | 250ms |
| Sidebar mobile | `transform: translateX(-100%)` → `translateX(0)` | 250ms |
| Barra de bottleneck | `width` via Alpine `x-init` com transição CSS | 600ms |
| Hover nos cards | `border-color` transition | 150ms |
| Hover nos botões | `background`, `box-shadow` transition | 150ms |
| `.nf-spinner` | `spin` keyframe — `transform: rotate(360deg)` | 0.8s linear infinite |
| Dropdown do avatar | Alpine `x-show` com `x-transition` | 150ms |

---

## Padrão Alpine.js

Usos típicos no projeto:

```html
<!-- Modal/painel controlado por Alpine -->
<div x-data="{ open: false }"
     @open-panel.window="open = true"
     @close-panel.window="open = false">
    <div class="nf-panel-overlay" x-show="open" @click="open = false"></div>
    <div class="nf-panel" x-show="open">...</div>
</div>

<!-- Dropdown avatar -->
<div x-data="{ menuOpen: false }">
    <button @click="menuOpen = !menuOpen">Avatar</button>
    <div x-show="menuOpen" @click.outside="menuOpen = false" x-transition>
        Dropdown
    </div>
</div>

<!-- Barra animada de bottleneck -->
<div x-data="{ width: 0 }" x-init="setTimeout(() => width = {{ $cpuPct }}, 50)">
    <div class="nf-bottleneck-fill" :style="'width: ' + width + '%'"></div>
</div>
```

---

## Bridge Livewire → Alpine

Definido em `public/js/app.js`. Padrão: Livewire dispara evento → `app.js` converte em `CustomEvent` → Alpine escuta com `@event.window`.

```javascript
// public/js/app.js
document.addEventListener('livewire:initialized', () => {
    Livewire.on('component-saved', () =>
        document.dispatchEvent(new CustomEvent('component-saved')));
    Livewire.on('close-panel', () =>
        document.dispatchEvent(new CustomEvent('close-panel')));
    Livewire.on('open-panel', (data) =>
        document.dispatchEvent(new CustomEvent('open-panel', { detail: data })));
});
```

Helpers globais `window.NF`:
- `NF.fmt(n)` — formata número pt-BR (separador de milhar)
- `NF.bottleneckColor(pct)` — `var(--nf-green)` / `var(--nf-amber)` / `var(--nf-red)`
- `NF.bottleneckSeverity(pct)` — `'low'` (≤10%) / `'medium'` (≤25%) / `'high'` (>25%)
- `NF.scoreClass(score)` — `'nf-score--high'` (>10000) / `'nf-score--mid'` (>5000) / `'nf-score--low'`
