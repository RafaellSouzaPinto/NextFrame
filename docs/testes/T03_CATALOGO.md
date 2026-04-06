# T03 — Testes: Catálogo de Hardware

## Objetivo

Validar filtros, busca, ordenação, paginação e a funcionalidade "adicionar ao setup" do catálogo global de hardware.

## Setup

```php
// Factories necessárias:
// User::factory() — padrão Laravel
// HardwareCatalog::factory() — criar após Model existir
// tests/Feature/HardwareCatalogTest.php
```

## Casos de Teste

### CT01 — Filtro por tipo retorna apenas itens do tipo selecionado
- **Dado**: catálogo com 3 CPUs e 2 GPUs
- **Quando**: `setType('cpu')`
- **Então**: render retorna apenas CPUs
- **Código**:
```php
public function test_filtro_por_tipo_cpu(): void
{
    $user = User::factory()->create();
    HardwareCatalog::factory()->count(3)->create(['type' => 'cpu']);
    HardwareCatalog::factory()->count(2)->create(['type' => 'gpu']);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Catalog\HardwareCatalog::class)
        ->call('setType', 'cpu');

    $this->assertEquals('cpu', $component->get('typeFilter'));
    // Verificar que os itens retornados são CPUs (via view ou $totalCount)
    $component->assertSet('totalCount', 3);
}
```

### CT02 — Busca por nome filtra corretamente
- **Dado**: catálogo com 'RTX 4090' e 'RX 7900 XTX'
- **Quando**: `search = 'RTX'`
- **Então**: `totalCount = 1` (apenas o RTX)
- **Código**:
```php
public function test_busca_por_nome(): void
{
    $user = User::factory()->create();
    HardwareCatalog::factory()->create(['name' => 'RTX 4090', 'type' => 'gpu']);
    HardwareCatalog::factory()->create(['name' => 'RX 7900 XTX', 'type' => 'gpu']);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Catalog\HardwareCatalog::class)
        ->set('search', 'RTX')
        ->assertSet('totalCount', 1);
}
```

### CT03 — Ordenação por score descendente
- **Dado**: 3 itens com benchmark_score 1000, 5000, 3000
- **Quando**: `setSort('score_desc')`
- **Então**: primeiro item tem score 5000
- **Código**:
```php
public function test_ordenacao_score_descendente(): void
{
    $user = User::factory()->create();
    HardwareCatalog::factory()->create(['name' => 'Fraco',  'type' => 'gpu', 'benchmark_score' => 1000]);
    HardwareCatalog::factory()->create(['name' => 'Forte',  'type' => 'gpu', 'benchmark_score' => 5000]);
    HardwareCatalog::factory()->create(['name' => 'Médio',  'type' => 'gpu', 'benchmark_score' => 3000]);

    $livewire = Livewire::actingAs($user)
        ->test(\App\Livewire\Catalog\HardwareCatalog::class)
        ->call('setSort', 'score_desc');

    // Via view — verificar que "Forte" aparece primeiro
    $livewire->assertSeeInOrder(['Forte', 'Médio', 'Fraco']);
}
```

### CT04 — Adicionar ao setup cria UserComponent
- **Dado**: usuário autenticado, catálogo com 1 GPU
- **Quando**: `addToMySetup($catalogId)`
- **Então**: `user_components` tem registro com `user_id` e `catalog_id`
- **Código**:
```php
public function test_adicionar_ao_setup(): void
{
    $user    = User::factory()->create();
    $catalog = HardwareCatalog::factory()->create(['type' => 'gpu', 'name' => 'RTX 4080']);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Catalog\HardwareCatalog::class)
        ->call('addToMySetup', $catalog->id);

    $this->assertDatabaseHas('user_components', [
        'user_id'    => $user->id,
        'catalog_id' => $catalog->id,
        'type'       => 'gpu',
    ]);
}
```

### CT05 — Adicionar mesmo tipo substitui (não duplica)
- **Dado**: usuário já tem GPU cadastrada
- **Quando**: `addToMySetup()` com outra GPU
- **Então**: `user_components` tem apenas 1 GPU (substituição)
- **Código**:
```php
public function test_adicionar_mesmo_tipo_substitui(): void
{
    $user   = User::factory()->create();
    $gpu1   = HardwareCatalog::factory()->create(['type' => 'gpu', 'name' => 'GPU antiga']);
    $gpu2   = HardwareCatalog::factory()->create(['type' => 'gpu', 'name' => 'GPU nova']);
    UserComponent::factory()->create(['user_id' => $user->id, 'type' => 'gpu', 'catalog_id' => $gpu1->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Catalog\HardwareCatalog::class)
        ->call('addToMySetup', $gpu2->id);

    $this->assertDatabaseCount('user_components', 1);
    $this->assertDatabaseHas('user_components', ['catalog_id' => $gpu2->id]);
}
```

### CT06 — Paginação reseta ao mudar filtro
- **Dado**: 15 itens do tipo 'gpu' (mais de 12 por página), usuário na página 2
- **Quando**: `setType('cpu')`
- **Então**: paginação volta para página 1
- **Código**:
```php
public function test_paginacao_reseta_ao_mudar_filtro(): void
{
    $user = User::factory()->create();
    HardwareCatalog::factory()->count(15)->create(['type' => 'gpu']);

    $livewire = Livewire::actingAs($user)
        ->test(\App\Livewire\Catalog\HardwareCatalog::class)
        ->set('page', 2)
        ->call('setType', 'gpu');

    $livewire->assertSet('page', 1);
}
```

## Comando para Rodar

```bash
php artisan test --filter=HardwareCatalogTest
php artisan test tests/Feature/HardwareCatalogTest.php
```
