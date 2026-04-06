# docs/decisions/correcoes.md — Log de Bugs e Armadilhas

Registro de problemas encontrados, causas e soluções aplicadas. Atualize este arquivo ao resolver qualquer bug não trivial.

---

## Formato de entrada

```
## [AAAA-MM-DD] — [Título curto do problema]

**Problema**: O que estava acontecendo de errado.
**Causa**: Por que acontecia.
**Solução**: O que foi feito para corrigir.
**Arquivos alterados**: lista de arquivos
```

---

## [A PREENCHER] — Problemas futuros

> Este arquivo está vazio. Registre aqui bugs conforme forem descobertos e resolvidos.

---

## Armadilhas Conhecidas (pré-implementação)

### Livewire — Resetar paginação ao filtrar

**Problema**: Ao mudar filtros de busca no catálogo ou no setup, a paginação não volta para a página 1, exibindo página vazia.

**Causa**: `WithPagination` mantém o estado da página atual independente dos filtros.

**Solução**: Chamar `$this->resetPage()` em todos os métodos que alteram filtros (`updatedSearch`, `updatedTypeFilter`, `setSort`).

**Arquivos afetados**: `app/Livewire/Catalog/HardwareCatalog.php`, `app/Livewire/Hardware/HardwareManager.php`

---

### Livewire — `wire:model` em select sem opção default

**Problema**: Select com `wire:model` sem `<option value="">` causa erro de validação falso positivo quando o formulário é submetido antes de selecionar.

**Causa**: O valor inicial da propriedade Livewire é `null` mas não há opção `value=""` no select.

**Solução**: Sempre incluir `<option value="">Selecione...</option>` como primeira opção e incluir `'required'` na validação para dar feedback claro.

---

### Alpine.js — `@event.window` precisa do bridge em `app.js`

**Problema**: Alpine não consegue escutar eventos disparados pelo Livewire com `$dispatch('event-name')` diretamente via `@event-name.window`.

**Causa**: Livewire usa seu próprio sistema de eventos interno, não `CustomEvent` do DOM.

**Solução**: O bridge em `public/js/app.js` converte eventos Livewire em `CustomEvent` do DOM. Qualquer novo evento Livewire→Alpine precisa ser adicionado no listener `livewire:initialized` desse arquivo.

**Arquivos afetados**: `public/js/app.js`

---

### Migrations — Nunca alterar migrations já aplicadas

**Problema**: Alterar o schema de uma migration já rodada quebra o estado do banco em outros ambientes.

**Causa**: `php artisan migrate` não re-aplica migrations já registradas na tabela `migrations`.

**Solução**: Sempre criar uma NOVA migration para alterar schema (`php artisan make:migration add_column_to_table`). Nunca editar o arquivo de migration existente.

---

### `user_components` — Unique por `(user_id, type)`

**Problema**: Sem a constraint, o usuário pode cadastrar múltiplas CPUs ou GPUs, quebrando a lógica do BottleneckEngine que espera exatamente 1 CPU e 1 GPU.

**Causa**: Schema sem `$table->unique(['user_id', 'type'])`.

**Solução**: A migration de `user_components` deve incluir `$table->unique(['user_id', 'type'])`. Ao salvar no Livewire, usar `updateOrCreate` com chave `['user_id', 'type']`.

**Arquivos afetados**: `database/migrations/*_create_user_components_table.php`, `app/Livewire/Hardware/HardwareForm.php`

---

### CSS — Tailwind classes não funcionam nos Blades

**Problema**: Adicionar classes Tailwind nos templates não gera estilo nenhum.

**Causa**: As diretivas `@tailwind` em `resources/css/app.css` geram um arquivo CSS mínimo, mas o CSS visual real está em `public/css/custom.css` com prefixo `nf-*`. O projeto foi desenhado para não usar Tailwind no HTML.

**Solução**: Usar apenas classes `nf-*` e Bootstrap grid (`.row`, `.col-*`). Para qualquer novo estilo, adicionar em `public/css/custom.css`.

---

### Bootstrap — Usar apenas para grid

**Problema**: Usar componentes Bootstrap (`.btn`, `.card`, `.modal`, `.alert`) quebrará o visual do projeto que usa exclusivamente `nf-*`.

**Causa**: Os estilos Bootstrap de componentes conflitam com o design system `nf-*`.

**Solução**: Bootstrap só para `.container`, `.row`, `.col-*`, `.d-flex`, `.d-none`, `.justify-content-*`, `.align-items-*`. Tudo visual usa classes `nf-*`.
