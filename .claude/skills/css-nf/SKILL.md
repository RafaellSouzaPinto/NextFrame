# Skill: CSS nf-* — Design System NextFrame

## Versão usada

CSS customizado — `public/css/custom.css` (~1515 linhas). Sem framework CSS de componentes.

## Padrões deste projeto

### Localização do CSS
```
public/css/custom.css    ← TODO o CSS visual. Editar aqui.
resources/css/app.css    ← Apenas diretivas @tailwind. NÃO editar para estilo visual.
```

### Prefixo obrigatório
Todas as classes customizadas têm prefixo `nf-`. Nenhuma classe Bootstrap de componente (`.btn`, `.card`, `.modal`).

Bootstrap permitido: `.row`, `.col-*`, `.d-flex`, `.d-none`, `.d-lg-none`, `.justify-content-*`, `.align-items-*`, `.gap-*`, `.mb-*`, `.mt-*`, `.ms-auto`, `.text-center`, `.fw-bold`, `.w-100`.

### Variáveis CSS — usar SEMPRE, nunca hex direto
```css
/* CORRETO */
color: var(--nf-text-primary);
background: var(--nf-bg-elevated);
border-color: var(--nf-accent);

/* ERRADO */
color: #e2e8f0;
background: #1a1e28;
```

### Adicionar novos estilos
```css
/* Sempre adicionar no final de public/css/custom.css */
/* Sempre usar variáveis CSS, não valores hardcoded */
/* Sempre prefixo nf- */

.nf-novo-componente {
    background: var(--nf-bg-elevated);
    border: 1px solid var(--nf-bg-border);
    border-radius: var(--nf-radius);
    padding: 12px 16px;
    color: var(--nf-text-primary);
}
```

### Classes de score (usar com `.nf-mono`)
```html
<span class="nf-mono nf-score--high">38000</span>   <!-- verde: > 10000 -->
<span class="nf-mono nf-score--mid">7500</span>      <!-- âmbar: 5000-10000 -->
<span class="nf-mono nf-score--low">2000</span>      <!-- vermelho: < 5000 -->
```

### Classes de texto colorido
```html
<span class="nf-text-cpu">dado de CPU</span>         <!-- cyan -->
<span class="nf-text-gpu">dado de GPU</span>         <!-- lavender -->
<span class="nf-text-green">positivo</span>          <!-- verde -->
<span class="nf-text-amber">aviso</span>             <!-- âmbar -->
<span class="nf-text-red">erro</span>                <!-- vermelho -->
```

### Spinner de loading
```html
<i class="bi bi-arrow-repeat nf-spinner"></i>
```

### Variáveis mais usadas (referência rápida)
```css
/* Backgrounds */
--nf-bg-base: #0d0f14         /* body */
--nf-bg-surface: #13161e      /* sidebar, navbar */
--nf-bg-elevated: #1a1e28     /* cards, painéis */
--nf-bg-highlight: #21263a    /* hover de linha de tabela */
--nf-bg-border: #1f2535       /* bordas */

/* Accent (roxo) */
--nf-accent: #7b6ef6
--nf-accent-dim: #4a42b0      /* hover do botão primário */
--nf-accent-glow: rgba(123,110,246,0.20)

/* Hardware */
--nf-cpu: #22d3ee             /* cyan */
--nf-gpu: #a78bfa             /* lavender */
--nf-ram: #34d399             /* emerald */
--nf-storage: #fb923c         /* orange */

/* Status */
--nf-green: #22c55e
--nf-amber: #f59e0b
--nf-red: #ef4444

/* Texto */
--nf-text-primary: #e2e8f0
--nf-text-secondary: #6b7a99
--nf-text-muted: #374151

/* Fontes */
--nf-font-base: 'Inter', sans-serif
--nf-font-mono: 'JetBrains Mono', monospace

/* Layout */
--nf-sidebar-width: 220px
--nf-navbar-height: 56px
--nf-radius: 4px
--nf-radius-lg: 6px
--nf-shadow: 0 1px 8px rgba(0,0,0,0.4)
```

## Armadilhas Comuns

- **Usar hex diretamente**: quebra a consistência visual. Sempre usar variável CSS
- **Usar classes Tailwind nos templates**: o CSS Tailwind não é purgeado/gerado corretamente — sem efeito visual
- **Usar `.btn`, `.card`, `.alert` do Bootstrap**: conflita com o design system `nf-*`
- **Adicionar estilos em `resources/css/app.css`**: esse arquivo só tem diretivas `@tailwind`. CSS real fica em `public/css/custom.css`
- **Esquecer o prefixo `nf-`**: pode colidir com Bootstrap ou outros estilos globais

## Exemplos de Código Correto

```html
<!-- Card com borda colorida no topo -->
<div class="nf-card nf-card--cpu-top">
    <div class="nf-mono nf-score--high">38000</div>
    <div style="color: var(--nf-text-secondary);">benchmark score</div>
    <h4 style="color: var(--nf-text-primary);">AMD Ryzen 9 7950X</h4>
</div>

<!-- Botão primário com loading -->
<button class="nf-btn" wire:click="save" wire:loading.attr="disabled">
    <span wire:loading.remove>Salvar</span>
    <span wire:loading><i class="bi bi-arrow-repeat nf-spinner"></i> Salvando…</span>
</button>

<!-- Badge de hardware -->
<span class="nf-badge nf-badge--gpu">GPU</span>

<!-- Input com label -->
<div class="nf-form-group">
    <label class="nf-label">Orçamento máximo</label>
    <input type="number" class="nf-input" wire:model.live.debounce.500ms="budget" placeholder="R$ 0,00">
</div>
```

## O Que NUNCA Fazer

- Nunca adicionar classes Tailwind (`text-gray-500`, `bg-blue-600`, etc.) nos templates Blade
- Nunca usar `.btn`, `.card`, `.badge`, `.alert`, `.modal` do Bootstrap (componentes)
- Nunca colocar CSS de componente em `resources/css/app.css`
- Nunca usar valores de cor hardcoded — sempre variáveis `var(--nf-*)`
- Nunca criar uma classe sem prefixo `nf-` (exceto utilitários Bootstrap permitidos)
