# Hook: Pre-commit — NextFrame

## O que verificar antes de cada commit

### Segurança
- [ ] Sem chaves de API, tokens ou senhas hardcoded nos arquivos
- [ ] `.env` não está sendo commitado (deve estar no `.gitignore`)
- [ ] Nenhum dado de usuário real em seeders (usar dados fictícios via Faker)

### Código PHP / Laravel
- [ ] Migrations não foram alteradas — apenas novas migrations criadas
- [ ] Sem `dd()`, `dump()`, `var_dump()`, `print_r()` de debug esquecidos
- [ ] Sem `//TODO` ou `//FIXME` introduzidos sem intenção
- [ ] Sem senhas ou hashes em plain text no código

### JavaScript / Frontend
- [ ] Sem `console.log()` de debug esquecidos
- [ ] Sem classes Tailwind adicionadas nos templates Blade
- [ ] Sem componentes Bootstrap (`.btn`, `.card`, `.modal`) adicionados nos templates

### CSS
- [ ] Novos estilos adicionados em `public/css/custom.css`, não em `resources/css/app.css`
- [ ] Novas classes usam prefixo `nf-`
- [ ] Cores usam variáveis CSS `var(--nf-*)`, não valores hex diretos

### Qualidade
- [ ] Testes passando: `php artisan test`
- [ ] Todo texto visível ao usuário em PT-BR (labels, mensagens de erro, flash messages)
- [ ] Nenhum texto em inglês exposto na UI

### Laravel específico
- [ ] `php artisan route:list` sem erros
- [ ] Novas migrations rodam sem erro: `php artisan migrate`
- [ ] Se novos Models criados: têm `$fillable` definido (nunca `$guarded = []`)

## Comandos de verificação rápida

```bash
# Rodar todos os testes
php artisan test

# Verificar rotas
php artisan route:list

# Verificar sintaxe PHP
php artisan lint  # ou: ./vendor/bin/pint --test

# Buscar debug esquecidos
grep -r "dd\(\|dump\(\|var_dump\(\|console\.log" app/ resources/ --include="*.php" --include="*.js" --include="*.blade.php"

# Buscar Tailwind nos Blades
grep -r "class=\".*text-\|class=\".*bg-\|class=\".*flex-\|class=\".*p-[0-9]" resources/views/ --include="*.blade.php"
```
