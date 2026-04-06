# Skill: Laravel 11 — Referência para Claude Code

## Versão usada

Laravel **11.31** com PHP **8.3**

## Padrões deste projeto

### Rotas — inline, sem controllers
```php
// routes/web.php — toda lógica de auth está inline nas rotas
Route::post('/login', function (Request $request) {
    $request->validate(['email' => 'required|email', 'password' => 'required']);
    if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }
    return back()->withErrors(['email' => 'Credenciais inválidas.']);
})->name('login.post');

// Rotas autenticadas
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('dashboard.index'))->name('dashboard');
});
```

Não há controllers separados. Se precisar criar um controller, justifique antes.

### Models — Eloquent padrão
```php
// Fillable explícito, casts definidos, relationships em métodos
class UserComponent extends Model
{
    protected $fillable = ['user_id', 'catalog_id', 'type', 'name'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function catalog(): BelongsTo { return $this->belongsTo(HardwareCatalog::class, 'catalog_id'); }
}
```

### Migrations — nunca alterar as existentes
```bash
# Para alterar schema: SEMPRE criar nova migration
php artisan make:migration add_specs_to_hardware_catalogs_table
# NUNCA editar arquivo de migration já aplicado
```

### Flash messages — via session()->flash()
```php
// Em Livewire components (não em routes diretas)
session()->flash('success', 'Componente salvo com sucesso!');
session()->flash('error', 'Erro ao salvar.');
```

### Services — stateless, métodos estáticos
```php
// app/Services/BottleneckEngine.php
class BottleneckEngine
{
    public static function calculate(HardwareCatalog $cpu, HardwareCatalog $gpu, string $resolution): array
    {
        // lógica pura, sem estado
    }
}

// Uso:
$result = BottleneckEngine::calculate($cpu, $gpu, '1440p');
```

### Banco — SQLite padrão local
```env
DB_CONNECTION=sqlite
# Arquivo: database/database.sqlite
```

## Armadilhas Comuns

- **Nunca alterar migrations aplicadas** — cria nova migration em vez de editar
- **`php artisan migrate:fresh` apaga tudo** — usar apenas em dev, nunca em prod
- **`unique(['user_id', 'type'])` em `user_components`** — sem isso o `updateOrCreate` não funciona corretamente
- **`updateOrCreate` precisa da constraint certa** — o array de "keys" deve corresponder ao índice único da tabela
- **Soft deletes não estão no projeto** — usar `delete()` direto quando necessário
- **Eager loading** — sempre `->with('catalog')` ao listar `user_components` para evitar N+1

## Exemplos de Código Correto

```php
// updateOrCreate correto
UserComponent::updateOrCreate(
    ['user_id' => auth()->id(), 'type' => $this->type],  // chaves únicas
    ['catalog_id' => $this->catalogId, 'name' => $this->name]  // valores a atualizar
);

// Query com scope
HardwareCatalog::ofType('cpu')->orderByDesc('benchmark_score')->get();

// Eager loading em collection
UserComponent::where('user_id', auth()->id())->with('catalog')->get()->keyBy('type');
```

## O Que NUNCA Fazer

- Nunca alterar migrations já aplicadas
- Nunca usar `DB::statement()` para schema em código de produção (usar migrations)
- Nunca usar `Auth::id()` sem garantir que o usuário está autenticado (middleware `auth`)
- Nunca armazenar senha sem hash (o cast `hashed` já cobre — usar `Hash::make()` apenas onde o cast não aplica)
- Nunca criar controllers separados para rotas que já têm lógica inline — manter padrão do projeto
