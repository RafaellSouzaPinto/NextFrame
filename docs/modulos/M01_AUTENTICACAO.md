# M01 — Autenticação

## Propósito

Controla o acesso ao sistema. Usuários criam conta, fazem login com email+senha e podem se manter logados com "lembrar-me". Toda a área autenticada exige sessão ativa.

## Arquivos

| Arquivo | Papel |
|---------|-------|
| `routes/web.php` | Lógica de login/register/logout inline nas rotas |
| `app/Models/User.php` | Model do usuário — fillable, casts, traits |
| `resources/views/auth/login.blade.php` | Formulário de login |
| `resources/views/auth/register.blade.php` | Formulário de cadastro |
| `resources/views/layouts/guest.blade.php` | Layout das páginas de auth |

## Tabela Envolvida

**`users`** (migration: `0001_01_01_000000_create_users_table.php`)

| Coluna | Tipo | Obs |
|--------|------|-----|
| `id` | bigint unsigned | PK autoincrement |
| `name` | varchar(255) | Nome do usuário |
| `email` | varchar(255) | Unique |
| `email_verified_at` | timestamp | Nullable |
| `password` | varchar(255) | Bcrypt via `hashed` cast |
| `remember_token` | varchar(100) | Nullable |
| `created_at` / `updated_at` | timestamp | — |

## Model

```php
// app/Models/User.php
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden   = ['password', 'remember_token'];
    protected $casts    = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];
}
```

## Rotas (Controller inline)

```php
// routes/web.php

// LOGIN
Route::post('/login', function (Request $request) {
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);
    if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }
    return back()->withErrors(['email' => 'Credenciais inválidas.']);
})->name('login.post');

// REGISTER
Route::post('/register', function (Request $request) {
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ]);
    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => Hash::make($request->password),
    ]);
    Auth::login($user);
    return redirect('/dashboard');
})->name('register.post');

// LOGOUT
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');
```

## View Principal (Login)

```html
<!-- resources/views/auth/login.blade.php -->
@extends('layouts.guest')
@section('content')
<div class="nf-auth-wrap">
    <div class="nf-auth-card">
        <!-- Logo -->
        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="nf-form-group">
                <label class="nf-label">E-mail</label>
                <input type="email" name="email" class="nf-input" value="{{ old('email') }}">
                @error('email') <span class="nf-field-error">{{ $message }}</span> @enderror
            </div>
            <div class="nf-form-group">
                <label class="nf-label">Senha</label>
                <input type="password" name="password" class="nf-input">
            </div>
            <label class="nf-toggle-wrap">
                <input type="checkbox" name="remember"> Lembrar de mim
            </label>
            <button type="submit" class="nf-btn w-100">Entrar</button>
        </form>
    </div>
</div>
@endsection
```

## Fluxo Completo

```
[Login]
1. GET /login → view auth/login
2. Usuário preenche email + senha → POST /login
3. validate() → se falhar, back() com errors
4. Auth::attempt() → se falhar, back()->withErrors(['email' => '...'])
5. Se sucesso: session()->regenerate() → redirect()->intended('/dashboard')

[Register]
1. GET /register → view auth/register
2. Usuário preenche name, email, password, password_confirmation → POST /register
3. validate() → unique:users verifica email duplicado
4. User::create() com Hash::make(password)
5. Auth::login($user) → redirect('/dashboard')

[Logout]
1. POST /logout (botão no navbar)
2. Auth::logout() + session()->invalidate() + session()->regenerateToken()
3. redirect('/')

[Proteção de rotas]
- Middleware `auth` em todas as rotas autenticadas
- Se não logado: redirect para /login (via authenticatable padrão Laravel)
```

## Regras Críticas

- Nunca usar `md5()` ou `sha1()` para senha — sempre `Hash::make()` (já no cast `hashed`)
- `session()->regenerate()` obrigatório após login bem-sucedido (CSRF rotation)
- `session()->invalidate()` + `regenerateToken()` obrigatório no logout
- Mensagens de erro em PT-BR
- Email único: validado com `unique:users` no register
