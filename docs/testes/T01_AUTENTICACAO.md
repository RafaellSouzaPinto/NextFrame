# T01 — Testes: Autenticação

## Objetivo

Validar que login, register e logout funcionam corretamente, incluindo casos de erro, validação de campos e proteção de rotas autenticadas.

## Setup

```php
// Sem factories especiais — usar User::factory() do Laravel padrão
// tests/Feature/AuthTest.php
```

## Casos de Teste

### CT01 — Login com credenciais válidas
- **Dado**: usuário existe no banco com email `test@example.com` e senha `password123`
- **Quando**: POST `/login` com `email=test@example.com`, `password=password123`
- **Então**: redireciona para `/dashboard`, sessão autenticada
- **Código**:
```php
public function test_login_com_credenciais_validas(): void
{
    $user = User::factory()->create(['password' => Hash::make('password123')]);

    $response = $this->post('/login', [
        'email'    => $user->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
}
```

### CT02 — Login com senha incorreta
- **Dado**: usuário existe
- **Quando**: POST `/login` com senha errada
- **Então**: redireciona de volta com erro no campo `email`
- **Código**:
```php
public function test_login_com_senha_incorreta(): void
{
    $user = User::factory()->create(['password' => Hash::make('correta')]);

    $response = $this->post('/login', [
        'email'    => $user->email,
        'password' => 'errada',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
}
```

### CT03 — Login com email inexistente
- **Dado**: email não cadastrado
- **Quando**: POST `/login`
- **Então**: erro no campo `email`, não autentica
- **Código**:
```php
public function test_login_com_email_inexistente(): void
{
    $response = $this->post('/login', [
        'email'    => 'naoexiste@example.com',
        'password' => 'qualquer',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
}
```

### CT04 — Registro de novo usuário
- **Dado**: email único não cadastrado
- **Quando**: POST `/register` com dados válidos
- **Então**: usuário criado, autenticado, redireciona para `/dashboard`
- **Código**:
```php
public function test_registro_de_novo_usuario(): void
{
    $response = $this->post('/register', [
        'name'                  => 'João Gamer',
        'email'                 => 'joao@example.com',
        'password'              => 'senha12345',
        'password_confirmation' => 'senha12345',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertDatabaseHas('users', ['email' => 'joao@example.com']);
    $this->assertAuthenticated();
}
```

### CT05 — Registro com email duplicado
- **Dado**: email já cadastrado
- **Quando**: POST `/register` com mesmo email
- **Então**: erro de validação `email`
- **Código**:
```php
public function test_registro_com_email_duplicado(): void
{
    User::factory()->create(['email' => 'existente@example.com']);

    $response = $this->post('/register', [
        'name'                  => 'Outro',
        'email'                 => 'existente@example.com',
        'password'              => 'senha12345',
        'password_confirmation' => 'senha12345',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
}
```

### CT06 — Logout
- **Dado**: usuário autenticado
- **Quando**: POST `/logout`
- **Então**: sessão destruída, redireciona para `/`
- **Código**:
```php
public function test_logout(): void
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
}
```

### CT07 — Acesso a rota autenticada sem login
- **Dado**: usuário não autenticado
- **Quando**: GET `/dashboard`
- **Então**: redireciona para `/login`
- **Código**:
```php
public function test_rota_autenticada_sem_login_redireciona(): void
{
    $routes = ['/dashboard', '/hardware', '/catalog', '/bottleneck', '/compare', '/upgrade'];

    foreach ($routes as $route) {
        $this->get($route)->assertRedirect('/login');
    }
}
```

### CT08 — Senha hash não armazenada em plain text
- **Dado**: usuário criado via register
- **Quando**: consultar senha no banco
- **Então**: senha não é igual ao texto digitado (está hasheada)
- **Código**:
```php
public function test_senha_armazenada_como_hash(): void
{
    $this->post('/register', [
        'name'                  => 'Teste',
        'email'                 => 'hash@example.com',
        'password'              => 'minhasenha',
        'password_confirmation' => 'minhasenha',
    ]);

    $user = User::where('email', 'hash@example.com')->first();
    $this->assertNotEquals('minhasenha', $user->password);
    $this->assertTrue(Hash::check('minhasenha', $user->password));
}
```

## Comando para Rodar

```bash
php artisan test --filter=AuthTest
# ou
php artisan test tests/Feature/AuthTest.php
```
