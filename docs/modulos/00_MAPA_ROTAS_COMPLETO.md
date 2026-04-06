# 00 — Mapa Completo de Rotas

Todas as rotas estão em `routes/web.php`. Não há controllers separados — lógica de auth é inline nas rotas.

---

## Rotas Públicas (sem middleware)

| Método | URI | Nome | Handler | Descrição |
|--------|-----|------|---------|-----------|
| GET | `/` | `welcome` | `return view('welcome')` | Landing page |
| GET | `/login` | `login` | `return view('auth.login')` | Formulário de login |
| POST | `/login` | `login.post` | Inline — `Auth::attempt()` | Processa login |
| GET | `/register` | `register` | `return view('auth.register')` | Formulário de cadastro |
| POST | `/register` | `register.post` | Inline — `Hash::make()` + `Auth::login()` | Cria usuário e loga |
| POST | `/logout` | `logout` | Inline — `Auth::logout()` | Encerra sessão |

---

## Rotas Autenticadas (middleware `auth`)

| Método | URI | Nome | Livewire Component | Descrição |
|--------|-----|------|--------------------|-----------|
| GET | `/dashboard` | `dashboard` | `Dashboard\PerformanceDashboard` | Painel principal do usuário |
| GET | `/hardware` | `hardware.index` | `Hardware\HardwareManager` | Gerenciar setup pessoal |
| GET | `/catalog` | `catalog.index` | `Catalog\HardwareCatalog` | Navegar catálogo de componentes |
| GET | `/bottleneck` | `bottleneck.index` | `Bottleneck\BottleneckCalculator` | Calcular bottleneck |
| GET | `/compare` | `compare.index` | `Compare\ComponentComparer` | Comparar dois componentes |
| GET | `/upgrade` | `upgrade.index` | `Upgrade\UpgradeSuggestions` | Ver sugestões de upgrade |

---

## Query Params Suportados

| Rota | Parâmetro | Tipo | Uso |
|------|-----------|------|-----|
| `/compare` | `left` | int | ID do componente A |
| `/compare` | `right` | int | ID do componente B |
| `/compare` | `type` | string | Tipo: `cpu\|gpu\|ram\|storage` |

Exemplo: `/compare?left=5&right=12&type=gpu`

O `ComponentComparer::mount()` lê esses params via `Request::query()`.

---

## Redirecionamentos

| De | Para | Condição |
|----|------|----------|
| Qualquer rota `auth` | `/login` | Usuário não autenticado |
| POST `/login` (sucesso) | `/dashboard` | `redirect()->intended('/dashboard')` |
| POST `/register` (sucesso) | `/dashboard` | Automático após `Auth::login()` |
| POST `/logout` | `/` | Fixo |

---

## Rotas a Criar (futuro)

> Não há rotas de API planejadas. Todo o backend é via Livewire.

| Possível rota | Justificativa |
|---------------|---------------|
| `GET /profile` | Edição de perfil do usuário (não implementado) |
| `GET /catalog/{id}` | Página de detalhe de um componente (não planejada ainda) |
