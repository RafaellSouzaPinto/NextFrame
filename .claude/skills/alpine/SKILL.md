# Skill: Alpine.js 3 — Referência para Claude Code

## Versão usada

Alpine.js **3.x** (via CDN no layout `app.blade.php`)

## Padrões deste projeto

### Responsabilidade do Alpine
Alpine gerencia **exclusivamente estado local de UI**: abrir/fechar modais, painéis, dropdowns, toggles, tabs, animações. Nunca faz chamadas ao servidor.

### Carregamento via CDN
```html
<!-- resources/views/layouts/app.blade.php -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

### Painel lateral controlado pelo Alpine
```html
<!-- hardware/index.blade.php -->
<div x-data="{ open: false, editId: null }"
     @open-panel.window="open = true; editId = $event.detail.id ?? null"
     @close-panel.window="open = false; editId = null">

    <!-- Overlay -->
    <div class="nf-panel-overlay" x-show="open" @click="open = false"
         x-transition:enter="..." x-transition:leave="..."></div>

    <!-- Painel -->
    <div class="nf-panel" x-show="open"
         x-transition:enter="transition duration-250"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0">
        <template x-if="open">
            @livewire('hardware.hardware-form', key: 'hardware-form')
        </template>
    </div>
</div>
```

### Dropdown do avatar
```html
<div x-data="{ menuOpen: false }">
    <button @click="menuOpen = !menuOpen" class="nf-avatar-btn">
        <!-- initials -->
        <i class="bi bi-chevron-down" :class="{ 'rotate-180': menuOpen }"></i>
    </button>
    <div x-show="menuOpen"
         @click.outside="menuOpen = false"
         x-transition
         class="nf-dropdown">
        <!-- links -->
    </div>
</div>
```

### Sidebar mobile
```html
<div x-data="{ sidebarOpen: false }">
    <!-- Hamburger -->
    <button @click="sidebarOpen = true" class="nf-hamburger d-lg-none">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar -->
    <nav class="nf-sidebar" :class="{ 'nf-sidebar--open': sidebarOpen }">
        <button @click="sidebarOpen = false" class="nf-sidebar-close d-lg-none">
            <i class="bi bi-x"></i>
        </button>
        <!-- links -->
    </nav>
    <div class="nf-sidebar-overlay" x-show="sidebarOpen" @click="sidebarOpen = false"></div>
</div>
```

### Barra animada de bottleneck
```html
<div x-data="{ cpuWidth: 0, gpuWidth: 0 }"
     x-init="setTimeout(() => { cpuWidth = {{ $cpuPct }}; gpuWidth = {{ $gpuPct }}; }, 100)">
    <div class="nf-bottleneck-fill--cpu"
         :style="'width: ' + cpuWidth + '%'"
         style="transition: width 0.6s ease;"></div>
    <div class="nf-bottleneck-fill--gpu"
         :style="'width: ' + gpuWidth + '%'"
         style="transition: width 0.6s ease;"></div>
</div>
```

### Alert com auto-dismiss
```html
<div x-data="{ show: true }"
     x-show="show"
     x-init="setTimeout(() => show = false, 5000)"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <!-- conteúdo -->
    <button @click="show = false"><i class="bi bi-x"></i></button>
</div>
```

### Receber eventos do Livewire via bridge
```html
<!-- Alpine escuta CustomEvent do DOM (convertido pelo app.js) -->
<div @component-saved.window="mensagem = 'Salvo!'">
<div @open-panel.window="open = true; id = $event.detail.id">
<div @close-panel.window="open = false">
```

## Armadilhas Comuns

- **`x-if` vs `x-show`**: usar `x-if` quando o componente deve ser destruído/recriado (ex: painel com Livewire); usar `x-show` para simples visibilidade
- **`@click.outside`**: não funciona se o elemento não estiver fora do `x-data` — garantir que o dropdown está dentro do escopo correto
- **Eventos Livewire não chegam direto**: Livewire usa seu sistema próprio de eventos — o bridge em `app.js` é obrigatório
- **`x-init` com setTimeout**: a animação de entrada da barra precisa do delay para o CSS transition funcionar (iniciar com width 0 e animar para o valor real)
- **Alpine carrega antes do Livewire**: `defer` no script Alpine garante que o DOM está pronto, mas o bridge `livewire:initialized` é necessário para eventos Livewire

## Exemplos de Código Correto

```html
<!-- Toggle simples -->
<div x-data="{ active: false }">
    <button @click="active = !active">Toggle</button>
    <div x-show="active">Conteúdo</div>
</div>

<!-- Tabs -->
<div x-data="{ tab: '1080p' }">
    <button @click="tab = '1080p'" :class="{ 'active': tab === '1080p' }">1080p</button>
    <button @click="tab = '1440p'" :class="{ 'active': tab === '1440p' }">1440p</button>
    <div x-show="tab === '1080p'">...</div>
    <div x-show="tab === '1440p'">...</div>
</div>
```

## O Que NUNCA Fazer

- Nunca usar Alpine para fetch/AJAX — toda comunicação com servidor é via Livewire
- Nunca usar `$wire` dentro de Alpine `x-data` para lógica complexa — apenas para ações simples e diretas
- Nunca duplicar estado entre Livewire e Alpine — se o dado vem do servidor, é do Livewire; se é só UI, é Alpine
- Nunca usar `x-show` em componentes Livewire que precisam ser reiniciados — usar `x-if` + `wire:key`
