# CLAUDE.md — NextFrame

## Sobre o projeto

NextFrame é uma plataforma web para gamers identificarem o gargalo (bottleneck) entre CPU e GPU do seu PC, compararem componentes do mercado e receberem sugestões de upgrade ordenadas por custo-benefício. O usuário cadastra seu setup, a engine calcula qual componente está limitando a performance e quanto FPS está sendo perdido, e o sistema sugere upgrades compatíveis com o orçamento informado.

Público-alvo: gamers e entusiastas de hardware que querem decisões baseadas em dados, não em achismo.

## Stack

| Camada | Tecnologia |
|--------|------------|
| Linguagem | PHP 8.3 |
| Framework | Laravel 11 |
| UI reativa | Livewire 3 (SSR + AJAX) |
| UI local | Alpine.js 3 (modais, tabs, toggles) |
| CSS | `public/css/custom.css` proprietário — prefixo `nf-*`, zero Tailwind em produção |
| Grid | Bootstrap 5.3 — **somente** `.row` / `.col-*` / reset |
| Ícones | Bootstrap Icons 1.11 |
| Fonte | Inter (corpo) + JetBrains Mono (scores/dados) |
| Banco | SQLite (padrão local); configurável para MySQL via `.env` |
| Testes | PHPUnit 11 |
| Build | Vite (só para compilar `resources/css/app.css` — CSS real está em `public/css/`) |

## Como rodar

```bash
# 1. Instalar dependências
composer install
npm install

# 2. Configurar ambiente
cp .env.example .env
php artisan key:generate

# 3. Banco de dados
touch database/database.sqlite
php artisan migrate

# 4. Rodar (todos os processos juntos)
composer run dev
# equivale a: php artisan serve + php artisan queue:listen + php artisan pail + npm run dev

# Ou separadamente:
php artisan serve      # http://localhost:8000
npm run dev            # Vite HMR
```

## Login / Acesso

Não há seeders ainda. Criar conta via `http://localhost:8000/register`.

| Campo | Valor |
|-------|-------|
| URL local | http://localhost:8000 |
| Conta de teste | criar via /register |
| Área pública | `/` (landing), `/login`, `/register` |
| Área autenticada | `/dashboard`, `/hardware`, `/catalog`, `/bottleneck`, `/compare`, `/upgrade` |

## Documentação — leia ANTES de codar

| Arquivo | O que contém |
|---------|-------------|
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Estrutura de camadas, Livewire components, fluxo de dados, services planejados |
| [docs/FRONTEND-SPEC.md](docs/FRONTEND-SPEC.md) | Sistema de design completo: variáveis CSS, classes `nf-*`, Blade components, padrões Alpine |
| [docs/FEATURES.md](docs/FEATURES.md) | Features implementadas (frontend) e planejadas (backend), estado de cada módulo |
| [docs/modulos/](docs/modulos/) | Um arquivo por módulo funcional com schema, código e fluxo completo |
| [docs/testes/](docs/testes/) | Casos de teste por módulo com código PHP pronto |
| [docs/decisions/correcoes.md](docs/decisions/correcoes.md) | Log de bugs e armadilhas conhecidas do projeto |

## Skills — leia ANTES de codar

| Skill | Arquivo | O que contém |
|-------|---------|-------------|
| Laravel 11 | [.claude/skills/laravel/SKILL.md](.claude/skills/laravel/SKILL.md) | Padrões de rotas inline, Models, migrations, Services |
| Livewire 3 | [.claude/skills/livewire/SKILL.md](.claude/skills/livewire/SKILL.md) | Dispatch, listeners, paginação, debounce, wire:key |
| Alpine.js 3 | [.claude/skills/alpine/SKILL.md](.claude/skills/alpine/SKILL.md) | Painel lateral, dropdown, bridge Livewire→Alpine |
| CSS nf-* | [.claude/skills/css-nf/SKILL.md](.claude/skills/css-nf/SKILL.md) | Variáveis CSS, classes nf-*, regras de uso |

## Banco de dados — tabelas

| Tabela | Status | Descrição |
|--------|--------|-----------|
| `users` | ✅ existe | Usuários — Laravel default, inclui `name`, `email`, `password` |
| `hardware_catalogs` | ⏳ planejada | Catálogo global de componentes — CPU, GPU, RAM, Storage com benchmark scores |
| `user_components` | ⏳ planejada | Setup de cada usuário — FK `user_id` + FK `catalog_id` (nullable) + `type` + `name` manual |
| `sessions` | ✅ existe | Sessions — Laravel default |
| `cache` | ✅ existe | Cache — Laravel default |
| `jobs` | ✅ existe | Queue jobs — Laravel default |

> **Nota:** `bottleneck_results` pode ser necessária para cache de cálculos, mas decide após a engine estar funcionando.

## Regras críticas

- **Todo texto visível ao usuário em PT-BR** — labels, mensagens de erro, flash messages, comentários em Blade.
- **Nunca alterar migrations já aplicadas** — criar nova migration em vez de editar.
- **Bootstrap apenas para grid** — nunca usar componentes Bootstrap (buttons, cards, modals, alerts). Todo visual usa classes `nf-*`.
- **Tailwind não está em uso no HTML** — o `app.css` só tem as diretivas `@tailwind` mas não são usadas nos templates. Não adicionar classes Tailwind nos Blades.
- **Stubs nos Livewire components** — todos os `render()` retornam dados vazios/mock. Antes de implementar a lógica real, criar o Model e a migration correspondente.
- **Livewire para dados do servidor, Alpine para estado local** — não duplicar responsabilidades: formulários e queries ficam no Livewire, modais/tabs/toggles ficam no Alpine.
- **Bridge Livewire→Alpine via CustomEvents** — usar o padrão já estabelecido em `public/js/app.js`: Livewire dispara evento, `app.js` converte em `CustomEvent`, Alpine escuta com `@event.window`.
- **IDs de componentes Livewire** — usar `wire:key` em loops para evitar re-renders desnecessários.
