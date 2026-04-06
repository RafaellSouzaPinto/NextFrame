# FEATURES.md — NextFrame

Lista sequencial de features a implementar. O frontend de todas as features já existe como stub Livewire. O trabalho restante é backend: Models, migrations, Services e lógica nos componentes.

**Legenda de status:**
- ✅ Implementado
- 🔧 Frontend pronto, backend pendente
- ⏳ Planejado

---

## FEATURE 0 — Setup do Ambiente

### Objetivo
Ambiente local funcional com banco, variáveis de ambiente e dependências instaladas.

### Status
✅ Implementado

### Passos
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
composer run dev
```

### Critérios de aceitação
- [x] `php artisan serve` sobe sem erros
- [x] `npm run dev` compila assets sem erros
- [x] SQLite criado e migrations rodadas
- [x] `http://localhost:8000` carrega a landing page

### Arquivos afetados
- `.env` — variáveis de ambiente
- `database/database.sqlite` — arquivo do banco

---

## FEATURE 1 — Autenticação (Login / Register / Logout)

### Objetivo
Usuário consegue criar conta, fazer login e logout. Área autenticada protegida por middleware `auth`.

### Status
✅ Implementado (lógica inline em `routes/web.php`)

### Passos
A lógica de auth está nas rotas — sem controllers separados. Padrão atual:

```php
// routes/web.php
Route::post('/login', function (Request $request) {
    $request->validate(['email' => 'required|email', 'password' => 'required']);
    if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }
    return back()->withErrors(['email' => 'Credenciais inválidas.']);
})->name('login.post');
```

### Critérios de aceitação
- [x] Usuário consegue criar conta via `/register`
- [x] Login com credenciais corretas redireciona para `/dashboard`
- [x] Login com credenciais erradas mostra erro em PT-BR
- [x] Logout destrói sessão e redireciona para `/`
- [x] Rotas autenticadas retornam 302 para `/login` sem sessão

### Arquivos afetados
- `routes/web.php` — lógica inline de auth
- `app/Models/User.php` — model padrão Laravel
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`

---

## FEATURE 2 — Modelos e Migrations de Hardware

### Objetivo
Criar as tabelas `hardware_catalogs` e `user_components` que sustentam todas as features de hardware, catálogo, bottleneck, comparação e upgrade.

### Status
⏳ Pendente

### Passos

**1. Criar migrations:**
```bash
php artisan make:migration create_hardware_catalogs_table
php artisan make:migration create_user_components_table
```

**2. Schema de `hardware_catalogs`:**
```php
Schema::create('hardware_catalogs', function (Blueprint $table) {
    $table->id();
    $table->enum('type', ['cpu', 'gpu', 'ram', 'storage']);
    $table->string('name');
    $table->integer('benchmark_score')->default(0);
    $table->decimal('price', 10, 2)->nullable();
    $table->json('specs')->nullable(); // specs extras: clock, vram, cores, etc.
    $table->timestamps();

    $table->index('type');
    $table->index('benchmark_score');
});
```

**3. Schema de `user_components`:**
```php
Schema::create('user_components', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('catalog_id')->nullable()->constrained('hardware_catalogs')->nullOnDelete();
    $table->enum('type', ['cpu', 'gpu', 'ram', 'storage']);
    $table->string('name'); // preenchido do catálogo ou manualmente
    $table->timestamps();

    $table->unique(['user_id', 'type']); // 1 CPU, 1 GPU, 1 RAM, 1 Storage por usuário
});
```

**4. Criar Models:**
```bash
php artisan make:model HardwareCatalog
php artisan make:model UserComponent
```

```php
// app/Models/HardwareCatalog.php
class HardwareCatalog extends Model {
    protected $fillable = ['type', 'name', 'benchmark_score', 'price', 'specs'];
    protected $casts = ['specs' => 'array'];

    public function scopeOfType(Builder $query, string $type): Builder {
        return $query->where('type', $type);
    }
}

// app/Models/UserComponent.php
class UserComponent extends Model {
    protected $fillable = ['user_id', 'catalog_id', 'type', 'name'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function catalog(): BelongsTo { return $this->belongsTo(HardwareCatalog::class, 'catalog_id'); }
}
```

**5. Rodar:**
```bash
php artisan migrate
```

### Critérios de aceitação
- [ ] `php artisan migrate` sem erros
- [ ] Tabelas criadas no banco com schema correto
- [ ] Models instanciáveis no Tinker
- [ ] Relacionamentos funcionando: `UserComponent::with('catalog')`

### Arquivos afetados
- `database/migrations/*_create_hardware_catalogs_table.php` — novo
- `database/migrations/*_create_user_components_table.php` — novo
- `app/Models/HardwareCatalog.php` — novo
- `app/Models/UserComponent.php` — novo

---

## FEATURE 3 — Seeder do Catálogo de Hardware

### Objetivo
Popular `hardware_catalogs` com dados reais de CPUs e GPUs para que as outras features tenham dados para trabalhar.

### Status
⏳ Pendente (depende da Feature 2)

### Passos

```bash
php artisan make:seeder HardwareCatalogSeeder
```

```php
// database/seeders/HardwareCatalogSeeder.php
public function run(): void
{
    $components = [
        // CPUs
        ['type' => 'cpu', 'name' => 'AMD Ryzen 9 7950X', 'benchmark_score' => 38000, 'price' => 4200.00],
        ['type' => 'cpu', 'name' => 'Intel Core i9-13900K', 'benchmark_score' => 35000, 'price' => 3800.00],
        ['type' => 'cpu', 'name' => 'AMD Ryzen 7 7700X', 'benchmark_score' => 25000, 'price' => 2200.00],
        ['type' => 'cpu', 'name' => 'Intel Core i5-13600K', 'benchmark_score' => 20000, 'price' => 1600.00],
        ['type' => 'cpu', 'name' => 'AMD Ryzen 5 5600X', 'benchmark_score' => 15000, 'price' => 1100.00],
        // GPUs
        ['type' => 'gpu', 'name' => 'NVIDIA RTX 4090', 'benchmark_score' => 40000, 'price' => 9500.00],
        ['type' => 'gpu', 'name' => 'NVIDIA RTX 4080', 'benchmark_score' => 32000, 'price' => 6800.00],
        ['type' => 'gpu', 'name' => 'AMD RX 7900 XTX', 'benchmark_score' => 30000, 'price' => 5900.00],
        ['type' => 'gpu', 'name' => 'NVIDIA RTX 4070 Ti', 'benchmark_score' => 25000, 'price' => 4500.00],
        ['type' => 'gpu', 'name' => 'AMD RX 7800 XT', 'benchmark_score' => 18000, 'price' => 2800.00],
        // RAMs
        ['type' => 'ram', 'name' => 'DDR5 32GB 6000MHz', 'benchmark_score' => 9000, 'price' => 800.00],
        ['type' => 'ram', 'name' => 'DDR4 32GB 3600MHz', 'benchmark_score' => 7000, 'price' => 500.00],
        ['type' => 'ram', 'name' => 'DDR4 16GB 3200MHz', 'benchmark_score' => 5000, 'price' => 280.00],
        // Storages
        ['type' => 'storage', 'name' => 'Samsung 990 Pro NVMe 2TB', 'benchmark_score' => 12000, 'price' => 900.00],
        ['type' => 'storage', 'name' => 'WD Black SN850X 1TB', 'benchmark_score' => 11000, 'price' => 700.00],
    ];

    HardwareCatalog::insert(array_map(
        fn($c) => array_merge($c, ['created_at' => now(), 'updated_at' => now()]),
        $components
    ));
}
```

```bash
php artisan db:seed --class=HardwareCatalogSeeder
```

### Critérios de aceitação
- [ ] Seeder roda sem erros
- [ ] `hardware_catalogs` tem pelo menos 5 CPUs e 5 GPUs
- [ ] Catálogo (`/catalog`) exibe os itens após o seeder

### Arquivos afetados
- `database/seeders/HardwareCatalogSeeder.php` — novo
- `database/seeders/DatabaseSeeder.php` — adicionar chamada ao seeder

---

## FEATURE 4 — Meu Setup (HardwareManager + HardwareForm)

### Objetivo
Usuário cadastra, edita e remove os componentes do seu PC. Pode buscar no catálogo ou digitar manualmente.

### Status
🔧 Frontend pronto, backend pendente (depende da Feature 2)

### Passos

**1. Implementar `HardwareForm::save()`:**
```php
public function save(): void
{
    $this->validate();

    UserComponent::updateOrCreate(
        ['user_id' => auth()->id(), 'type' => $this->type],
        [
            'catalog_id' => $this->catalogId,
            'name'       => $this->name,
        ]
    );

    $this->dispatch('component-saved');
}
```

**2. Implementar `HardwareForm::mount()` para edição:**
```php
public function mount(?int $id = null): void
{
    if ($id) {
        $component = UserComponent::findOrFail($id);
        $this->componentId = $id;
        $this->type = $component->type;
        $this->name = $component->name;
        $this->catalogId = $component->catalog_id;
    }
}
```

**3. Implementar `HardwareForm::updatedCatalogSearch()`:**
```php
public function updatedCatalogSearch(): void
{
    if (strlen($this->catalogSearch) < 2) {
        $this->catalogResults = [];
        return;
    }
    $this->catalogResults = HardwareCatalog::query()
        ->when($this->type, fn($q) => $q->where('type', $this->type))
        ->where('name', 'like', "%{$this->catalogSearch}%")
        ->orderByDesc('benchmark_score')
        ->limit(8)
        ->get(['id', 'type', 'name', 'benchmark_score'])
        ->toArray();
}
```

**4. Implementar `HardwareManager::render()`:**
```php
public function render(): View
{
    return view('livewire.hardware.hardware-manager', [
        'components' => UserComponent::where('user_id', auth()->id())
            ->with('catalog')
            ->paginate(10),
    ]);
}
```

**5. Implementar `HardwareManager::deleteComponent()`:**
```php
public function deleteComponent(int $id): void
{
    UserComponent::where('id', $id)
        ->where('user_id', auth()->id())
        ->delete();
    $this->confirmingDeleteId = null;
    session()->flash('success', 'Componente removido.');
}
```

### Critérios de aceitação
- [ ] Usuário consegue adicionar um componente via busca no catálogo
- [ ] Usuário consegue adicionar um componente com nome manual
- [ ] Usuário consegue editar um componente existente
- [ ] Usuário consegue excluir um componente com confirmação
- [ ] Apenas 1 componente por tipo (unique constraint na tabela)
- [ ] Flash message de sucesso aparece após salvar/excluir
- [ ] Painel fecha após salvar

### Arquivos afetados
- `app/Livewire/Hardware/HardwareForm.php` — implementar lógica real
- `app/Livewire/Hardware/HardwareManager.php` — implementar render + delete
- `app/Models/UserComponent.php` — deve existir (Feature 2)

---

## FEATURE 5 — Catálogo Global (HardwareCatalog)

### Objetivo
Página pública (para usuários autenticados) com todos os componentes do catálogo, com busca, filtro por tipo e ordenação.

### Status
🔧 Frontend pronto, backend pendente (depende das Features 2 e 3)

### Passos

**Implementar `HardwareCatalog::render()`:**
```php
public function render(): View
{
    $query = HardwareCatalog::query()
        ->when($this->typeFilter !== 'all', fn($q) => $q->where('type', $this->typeFilter))
        ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"));

    $query->orderBy(
        match($this->sortBy) {
            'score_desc' => 'benchmark_score',
            'score_asc'  => 'benchmark_score',
            default      => 'name',
        },
        $this->sortBy === 'score_asc' ? 'asc' : 'desc'
    );

    $this->totalCount = $query->count();
    $components = $query->paginate(12);

    return view('livewire.catalog.hardware-catalog', compact('components'));
}
```

**Implementar `addToMySetup()`:**
```php
public function addToMySetup(int $catalogId): void
{
    $item = HardwareCatalog::findOrFail($catalogId);
    UserComponent::updateOrCreate(
        ['user_id' => auth()->id(), 'type' => $item->type],
        ['catalog_id' => $catalogId, 'name' => $item->name]
    );
    session()->flash('success', "{$item->name} adicionado ao seu setup!");
}
```

### Critérios de aceitação
- [ ] Lista exibe todos os componentes do catálogo
- [ ] Filtro por tipo funciona
- [ ] Busca por nome funciona (debounce 300ms)
- [ ] Ordenação por score (asc/desc) e nome funciona
- [ ] "Adicionar ao Setup" funciona e mostra flash de sucesso
- [ ] Paginação funciona (12 por página)

### Arquivos afetados
- `app/Livewire/Catalog/HardwareCatalog.php` — implementar render + addToMySetup

---

## FEATURE 6 — Calculadora de Bottleneck

### Objetivo
Calcular percentual de bottleneck entre CPU e GPU do usuário (ou seleção manual) para uma resolução específica.

### Status
🔧 Frontend pronto, backend pendente (depende das Features 2 e 3)

### Passos

**1. Criar `BottleneckEngine` service:**
```bash
# criar app/Services/BottleneckEngine.php
```

```php
// app/Services/BottleneckEngine.php
class BottleneckEngine
{
    public static function calculate(
        HardwareCatalog $cpu,
        HardwareCatalog $gpu,
        string $resolution = '1440p'
    ): array {
        // Peso da GPU aumenta com resolução mais alta
        $gpuWeight = match($resolution) {
            '1080p' => 0.6,
            '1440p' => 0.7,
            '4K'    => 0.85,
            default => 0.7,
        };
        $cpuWeight = 1 - $gpuWeight;

        $cpuScore = $cpu->benchmark_score;
        $gpuScore = $gpu->benchmark_score;

        // Normalizar para escala comparável
        $cpuEffective = $cpuScore * (1 / $cpuWeight);
        $gpuEffective = $gpuScore * (1 / $gpuWeight);

        $diff = abs($cpuEffective - $gpuEffective);
        $max  = max($cpuEffective, $gpuEffective);
        $pct  = (int) round(($diff / $max) * 100);
        $pct  = min($pct, 99); // nunca 100%

        $limitedBy = $cpuEffective < $gpuEffective ? 'cpu' : 'gpu';
        $cpuPct = (int) round(($cpuEffective / ($cpuEffective + $gpuEffective)) * 100);
        $gpuPct = 100 - $cpuPct;

        $severity = match(true) {
            $pct <= 10 => 'low',
            $pct <= 25 => 'medium',
            default    => 'high',
        };

        $fpsImpact = (int) round($pct * -0.4); // estimativa simplificada

        $explanation = self::buildExplanation($limitedBy, $pct, $resolution, $cpu->name, $gpu->name);

        return compact('pct', 'limitedBy', 'severity', 'cpuPct', 'gpuPct', 'fpsImpact', 'explanation') + [
            'bottleneck_pct' => $pct,
            'limited_by'     => $limitedBy,
            'fps_impact'     => $fpsImpact,
            'cpu_name'       => $cpu->name,
            'gpu_name'       => $gpu->name,
            'cpu_pct'        => $cpuPct,
            'gpu_pct'        => $gpuPct,
        ];
    }

    private static function buildExplanation(...): string { /* texto em PT-BR */ }
}
```

**2. Implementar `BottleneckCalculator::calculate()`:**
```php
public function calculate(): void
{
    $this->loading = true;
    $cpu = HardwareCatalog::findOrFail($this->cpuId);
    $gpu = HardwareCatalog::findOrFail($this->gpuId);
    $this->result = BottleneckEngine::calculate($cpu, $gpu, $this->resolution);
    $this->hasResult = true;
    $this->loading = false;
}
```

**3. Implementar `BottleneckCalculator::mount()` para pré-carregar setup:**
```php
public function mount(): void
{
    $this->cpuOptions = HardwareCatalog::ofType('cpu')->orderByDesc('benchmark_score')->get(['id', 'name'])->toArray();
    $this->gpuOptions = HardwareCatalog::ofType('gpu')->orderByDesc('benchmark_score')->get(['id', 'name'])->toArray();

    // Pré-carregar setup do usuário
    $userCpu = UserComponent::where('user_id', auth()->id())->where('type', 'cpu')->with('catalog')->first();
    $userGpu = UserComponent::where('user_id', auth()->id())->where('type', 'gpu')->with('catalog')->first();
    if ($userCpu?->catalog_id) $this->cpuId = $userCpu->catalog_id;
    if ($userGpu?->catalog_id) $this->gpuId = $userGpu->catalog_id;
}
```

### Critérios de aceitação
- [ ] Cálculo retorna resultado real (não mock)
- [ ] "Usar meu setup" pré-carrega CPU/GPU do usuário
- [ ] Seleção manual funciona com selects do catálogo
- [ ] Barra de bottleneck exibe percentuais corretos
- [ ] Resultado muda quando resolução é trocada
- [ ] Loading spinner durante o cálculo

### Arquivos afetados
- `app/Services/BottleneckEngine.php` — novo
- `app/Livewire/Bottleneck/BottleneckCalculator.php` — implementar lógica real

---

## FEATURE 7 — Comparador de Componentes

### Objetivo
Comparar dois componentes do catálogo lado a lado com tabela de diferenças e veredicto automático.

### Status
🔧 Frontend pronto, backend pendente (depende das Features 2 e 3)

### Passos

**Implementar `ComponentComparer::loadOptions()`, `loadLeft()`, `loadRight()`, `buildDiff()`:**

```php
private function loadOptions(): void
{
    $this->componentOptions = HardwareCatalog::ofType($this->typeFilter)
        ->orderByDesc('benchmark_score')
        ->get(['id', 'name', 'benchmark_score'])
        ->toArray();
}

private function loadLeft(): void
{
    $this->leftComponent = $this->leftId
        ? HardwareCatalog::find($this->leftId)?->toArray()
        : null;
}

private function buildDiff(): void
{
    if (!$this->leftComponent || !$this->rightComponent) {
        $this->diffTable = [];
        $this->verdict = null;
        return;
    }

    $l = $this->leftComponent['benchmark_score'];
    $r = $this->rightComponent['benchmark_score'];
    $delta = $r > 0 ? (int) round((($r - $l) / $l) * 100) : 0;

    $this->diffTable = [
        ['spec' => 'Benchmark Score', 'left' => $l, 'right' => $r, 'delta' => $delta],
        // specs adicionais do JSON specs, se existirem
    ];

    $winner = $l > $r ? $this->leftComponent['name'] : $this->rightComponent['name'];
    $this->verdict = "{$winner} tem melhor performance neste comparativo.";
}
```

### Critérios de aceitação
- [ ] Seleção de componente A e B popula a tabela de diferenças
- [ ] Delta exibe positivo (verde) ou negativo (vermelho)
- [ ] Veredicto gerado automaticamente
- [ ] Swap de componentes funciona
- [ ] Filtro por tipo recarrega opções
- [ ] Query params `?left=1&right=2&type=gpu` pré-carregam a comparação

### Arquivos afetados
- `app/Livewire/Compare/ComponentComparer.php` — implementar métodos privados

---

## FEATURE 8 — Sugestões de Upgrade

### Objetivo
Listar upgrades recomendados baseados no bottleneck atual do usuário, filtrados por orçamento e critério de prioridade.

### Status
🔧 Frontend pronto, backend pendente (depende das Features 2, 3 e 6)

### Passos

**1. Criar `UpgradeEngine` service:**
```php
// app/Services/UpgradeEngine.php
class UpgradeEngine
{
    public static function suggest(
        array $userComponents,  // indexado por type
        array $bottleneckResult,
        string $priority = 'bottleneck',
        ?float $budget = null
    ): array {
        $limitedBy = $bottleneckResult['limited_by']; // 'cpu' ou 'gpu'
        $currentScore = $userComponents[$limitedBy]['benchmark_score'] ?? 0;

        // Buscar upgrades que melhoram o componente gargalo
        $candidates = HardwareCatalog::where('type', $limitedBy)
            ->where('benchmark_score', '>', $currentScore)
            ->when($budget, fn($q) => $q->where('price', '<=', $budget))
            ->orderByDesc(
                match($priority) {
                    'fps'         => 'benchmark_score',
                    'cost_benefit'=> DB::raw('benchmark_score / NULLIF(price, 0)'),
                    default       => 'benchmark_score', // bottleneck
                }
            )
            ->limit(6)
            ->get();

        return $candidates->map(function ($candidate) use ($userComponents, $limitedBy, $bottleneckResult) {
            $currentComponent = $userComponents[$limitedBy] ?? null;
            $scoreDiff = $candidate->benchmark_score - ($currentComponent['benchmark_score'] ?? 0);
            $fpsGain = (int) round($scoreDiff / 500); // heurística
            return [
                'current'         => $currentComponent,
                'suggested'       => $candidate->toArray(),
                'fps_gain'        => $fpsGain,
                'is_best_value'   => false, // marcado depois
            ];
        })->toArray();
    }
}
```

**2. Implementar `UpgradeSuggestions::loadSuggestions()`:**
```php
private function loadSuggestions(): void
{
    $userComponents = UserComponent::where('user_id', auth()->id())
        ->with('catalog')
        ->get()
        ->keyBy('type')
        ->map(fn($uc) => array_merge($uc->toArray(), $uc->catalog?->toArray() ?? []))
        ->toArray();

    // Calcular bottleneck atual
    $cpu = $userComponents['cpu']['catalog'] ?? null;
    $gpu = $userComponents['gpu']['catalog'] ?? null;

    if (!$cpu || !$gpu) {
        $this->suggestions = [];
        return;
    }

    $bottleneck = BottleneckEngine::calculate(
        HardwareCatalog::find($cpu['id']),
        HardwareCatalog::find($gpu['id'])
    );

    $this->currentBottleneck = $bottleneck['limited_by'];
    $this->suggestions = UpgradeEngine::suggest(
        $userComponents,
        $bottleneck,
        $this->priorityFilter,
        $this->budget ? (float) $this->budget : null
    );
}
```

### Critérios de aceitação
- [ ] Lista exibe upgrades reais do catálogo
- [ ] Foco no componente gargalo (limitedBy do bottleneck)
- [ ] Filtro de orçamento funciona (debounce 500ms)
- [ ] Prioridade (gargalo / FPS / custo-benefício) muda a ordenação
- [ ] "Melhor Custo-Benefício" badge aparece no melhor candidato
- [ ] Empty state correto quando usuário não tem setup cadastrado

### Arquivos afetados
- `app/Services/UpgradeEngine.php` — novo
- `app/Livewire/Upgrade/UpgradeSuggestions.php` — implementar lógica real
- `app/Services/BottleneckEngine.php` — reutilizado (Feature 6)

---

## FEATURE 9 — Dashboard (PerformanceDashboard)

### Objetivo
Painel principal com resumo do setup do usuário: scores, bottleneck atual, FPS estimado por jogo e contagem de upgrades disponíveis.

### Status
🔧 Frontend pronto, backend pendente (depende de todas as features anteriores)

### Passos

**Implementar `PerformanceDashboard::render()`:**
```php
public function render(): View
{
    $userComponents = UserComponent::where('user_id', auth()->id())
        ->with('catalog')
        ->get()
        ->keyBy('type');

    $cpu = $userComponents->get('cpu')?->catalog;
    $gpu = $userComponents->get('gpu')?->catalog;

    $this->setup = $userComponents->map(fn($uc) => [
        'type'  => $uc->type,
        'name'  => $uc->name,
        'score' => $uc->catalog?->benchmark_score ?? 0,
    ])->values()->toArray();

    if ($cpu && $gpu) {
        $result = BottleneckEngine::calculate($cpu, $gpu, $this->resolution);
        $this->scores = ['cpu' => $cpu->benchmark_score, 'gpu' => $gpu->benchmark_score, 'bottleneck' => $result['pct']];
        $this->bottleneckSummary = ['limited_by' => $result['limited_by'], 'pct' => $result['pct'], 'severity' => $result['severity']];
        $this->fpsData = $this->buildFpsData($cpu, $gpu);
        $this->upgradeCount = UpgradeEngine::suggest($userComponents->toArray(), $result)->count();
    }

    return view('livewire.dashboard.performance-dashboard');
}
```

### Critérios de aceitação
- [ ] Cards de score exibem valores reais do setup
- [ ] Bottleneck % calculado em tempo real
- [ ] FPS estimado por resolução muda ao trocar a aba
- [ ] Contagem de upgrades disponíveis exibida
- [ ] Dashboard vazio (empty state) quando setup não configurado

### Arquivos afetados
- `app/Livewire/Dashboard/PerformanceDashboard.php` — implementar render
