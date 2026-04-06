# T02 — Testes: Hardware Manager

## Objetivo

Validar CRUD de componentes do setup do usuário: criar, editar, excluir, e garantir a constraint de 1 componente por tipo por usuário. Também testa a busca no catálogo via HardwareForm.

## Setup

```php
// Factories necessárias:
// User::factory() — padrão Laravel
// HardwareCatalog::factory() — criar após Model existir
// UserComponent::factory() — criar após Model existir

// tests/Feature/HardwareManagerTest.php
```

## Casos de Teste

### CT01 — Adicionar componente via catálogo
- **Dado**: usuário autenticado, catálogo com 1 GPU
- **Quando**: `HardwareForm::save()` com `type='gpu'`, `catalogId=1`, `name='RTX 4080'`
- **Então**: `user_components` tem 1 registro com `user_id`, `type='gpu'`, `catalog_id=1`
- **Código**:
```php
public function test_adicionar_componente_via_catalogo(): void
{
    $user    = User::factory()->create();
    $catalog = HardwareCatalog::factory()->create(['type' => 'gpu', 'name' => 'RTX 4080']);

    Livewire::actingAs($user)
        ->test(HardwareForm::class)
        ->set('type', 'gpu')
        ->set('catalogId', $catalog->id)
        ->set('name', 'RTX 4080')
        ->call('save');

    $this->assertDatabaseHas('user_components', [
        'user_id'    => $user->id,
        'type'       => 'gpu',
        'catalog_id' => $catalog->id,
    ]);
}
```

### CT02 — Adicionar componente manualmente (sem catálogo)
- **Dado**: usuário autenticado
- **Quando**: `save()` com `type='cpu'`, `catalogId=null`, `name='Intel Core i5 custom'`
- **Então**: registro criado com `catalog_id = null`
- **Código**:
```php
public function test_adicionar_componente_manual(): void
{
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(HardwareForm::class)
        ->set('type', 'cpu')
        ->set('name', 'Intel Core i5 custom')
        ->call('save');

    $this->assertDatabaseHas('user_components', [
        'user_id'    => $user->id,
        'type'       => 'cpu',
        'catalog_id' => null,
        'name'       => 'Intel Core i5 custom',
    ]);
}
```

### CT03 — Unique constraint: substituir componente do mesmo tipo
- **Dado**: usuário já tem CPU cadastrada
- **Quando**: `save()` com `type='cpu'` (mesmo tipo) com novo nome
- **Então**: apenas 1 registro de CPU existe (substituiu o antigo)
- **Código**:
```php
public function test_substituicao_componente_mesmo_tipo(): void
{
    $user = User::factory()->create();
    UserComponent::factory()->create(['user_id' => $user->id, 'type' => 'cpu', 'name' => 'CPU antiga']);

    Livewire::actingAs($user)
        ->test(HardwareForm::class)
        ->set('type', 'cpu')
        ->set('name', 'CPU nova')
        ->call('save');

    $this->assertDatabaseCount('user_components', 1);
    $this->assertDatabaseHas('user_components', ['user_id' => $user->id, 'name' => 'CPU nova']);
}
```

### CT04 — Validação: nome obrigatório
- **Dado**: formulário com `name = ''`
- **Quando**: `save()` chamado
- **Então**: erro de validação em `name`, nada salvo
- **Código**:
```php
public function test_nome_obrigatorio(): void
{
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(HardwareForm::class)
        ->set('type', 'cpu')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);

    $this->assertDatabaseCount('user_components', 0);
}
```

### CT05 — Excluir componente
- **Dado**: usuário tem 1 componente
- **Quando**: `HardwareManager::deleteComponent($id)` chamado pelo mesmo usuário
- **Então**: registro removido do banco
- **Código**:
```php
public function test_excluir_componente(): void
{
    $user      = User::factory()->create();
    $component = UserComponent::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(HardwareManager::class)
        ->call('deleteComponent', $component->id);

    $this->assertDatabaseMissing('user_components', ['id' => $component->id]);
}
```

### CT06 — Usuário não pode excluir componente de outro usuário
- **Dado**: dois usuários, cada um com 1 componente
- **Quando**: usuário A tenta `deleteComponent($id_do_usuario_B)`
- **Então**: componente do usuário B permanece no banco
- **Código**:
```php
public function test_nao_pode_excluir_componente_de_outro_usuario(): void
{
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $componentB = UserComponent::factory()->create(['user_id' => $userB->id]);

    Livewire::actingAs($userA)
        ->test(HardwareManager::class)
        ->call('deleteComponent', $componentB->id);

    $this->assertDatabaseHas('user_components', ['id' => $componentB->id]);
}
```

### CT07 — Busca no catálogo retorna resultados
- **Dado**: catálogo com 3 GPUs com nomes distintos
- **Quando**: `updatedCatalogSearch()` com `catalogSearch = 'RTX'`
- **Então**: `catalogResults` contém apenas itens com 'RTX' no nome
- **Código**:
```php
public function test_busca_catalogo_retorna_resultados(): void
{
    $user = User::factory()->create();
    HardwareCatalog::factory()->create(['type' => 'gpu', 'name' => 'RTX 4090']);
    HardwareCatalog::factory()->create(['type' => 'gpu', 'name' => 'RTX 4080']);
    HardwareCatalog::factory()->create(['type' => 'gpu', 'name' => 'RX 7900 XTX']);

    $component = Livewire::actingAs($user)
        ->test(HardwareForm::class)
        ->set('type', 'gpu')
        ->set('catalogSearch', 'RTX')
        ->get('catalogResults');

    $this->assertCount(2, $component);
}
```

## Comando para Rodar

```bash
php artisan test --filter=HardwareManagerTest
php artisan test tests/Feature/HardwareManagerTest.php
```
