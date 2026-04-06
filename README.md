# NextFrame

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com/)
[![Livewire](https://img.shields.io/badge/Livewire-3-FB70A9?style=flat-square&logo=livewire&logoColor=white)](https://livewire.laravel.com/)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3-8BC0D0?style=flat-square&logo=alpinedotjs&logoColor=white)](https://alpinejs.dev/)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.11-003545?style=flat-square&logo=mariadb&logoColor=white)](https://mariadb.org/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.2-7952B3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)

---

## Sobre o Projeto

NextFrame é uma **plataforma de benchmark e análise de upgrades** voltada para gamers e entusiastas de hardware. O sistema resolve um problema recorrente na comunidade: a dificuldade de identificar qual componente está limitando a performance do PC e para onde o investimento em upgrade faz mais sentido.

Com o NextFrame, o usuário cadastra as peças do seu setup atual, recebe uma análise de gargalo (bottleneck) entre CPU e GPU e obtém sugestões inteligentes de upgrade baseadas em custo-benefício e compatibilidade.

---

## Funcionalidades Principais (MVP)

- **Cadastro de Hardware** — Registro das peças do PC atual do usuário (CPU, GPU, RAM, armazenamento).
- **Calculadora de Gargalo** — Análise automatizada de bottleneck entre CPU e GPU, com percentual de limitação e impacto estimado em FPS.
- **Banco de Dados de Hardware** — Catálogo de componentes com especificações técnicas e benchmarks de referência.
- **Comparador de Componentes** — Comparação lado a lado entre o hardware atual e as alternativas de upgrade.
- **Sugestões de Upgrade** — Recomendações personalizadas ordenadas por custo-benefício, levando em conta o hardware existente do usuário.
- **Dashboard de Performance** — Visão consolidada do perfil do setup com indicadores de desempenho por categoria de game (1080p, 1440p, 4K).

---

## Stack Tecnológica

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.3 + Laravel 11 |
| Frontend Reativo | Livewire 3 + Alpine.js 3 |
| Banco de Dados | MariaDB 10.11 |
| Estilização | Bootstrap 5.3.2 (CDN) + custom.css |
| Gerenciador de Deps. | Composer |

---

## Pré-requisitos

Antes de começar, certifique-se de ter as seguintes ferramentas instaladas na sua máquina:

- [PHP 8.3+](https://www.php.net/downloads) com as extensões `pdo`, `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`
- [Composer](https://getcomposer.org/) (versão mais recente)
- [MariaDB 10.11+](https://mariadb.org/download/)
- [Node.js](https://nodejs.org/) (opcional, para compilação de assets futuros)

---

## Instalação e Execução

Siga os passos abaixo para rodar o projeto localmente.

**1. Clone o repositório**

```bash
git clone https://github.com/[seu-usuario]/nextframe.git
cd nextframe
```

**2. Instale as dependências PHP**

```bash
composer install
```

**3. Configure o arquivo de ambiente**

```bash
cp .env.example .env
```

Edite o arquivo `.env` com as credenciais do seu banco de dados:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nextframe
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

**4. Gere a chave da aplicação**

```bash
php artisan key:generate
```

**5. Crie o banco de dados**

```sql
CREATE DATABASE nextframe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**6. Execute as migrations**

```bash
php artisan migrate
```

**7. (Opcional) Popule o banco com dados iniciais**

```bash
php artisan db:seed
```

**8. Inicie o servidor de desenvolvimento**

```bash
php artisan serve
```

Acesse [http://localhost:8000](http://localhost:8000) no seu navegador.

---

## Como Contribuir

Contribuições são bem-vindas! Siga o fluxo abaixo:

1. **Fork** o repositório
2. Crie uma branch para sua feature:
   ```bash
   git checkout -b feature/nome-da-feature
   ```
3. Faça o commit das suas alterações:
   ```bash
   git commit -m "feat: adiciona nome-da-feature"
   ```
4. Faça o push para a sua branch:
   ```bash
   git push origin feature/nome-da-feature
   ```
5. Abra um **Pull Request** descrevendo as mudanças realizadas.

> Utilize o padrão [Conventional Commits](https://www.conventionalcommits.org/pt-br/v1.0.0/) para as mensagens de commit.

---

## Licença

Distribuído sob a licença MIT. Consulte o arquivo [LICENSE](LICENSE) para mais informações.
