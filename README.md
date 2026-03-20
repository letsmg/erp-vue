<img src="https://raw.githubusercontent.com/letsmg/erp-vue-laravel/main/pacman-contribution-graph.svg" />

# 🌌 ERP Vue Laravel — Smart Business Management

> Sistema moderno de gestão empresarial (ERP) construído com **Laravel + Vue**, focado em **performance, segurança e experiência do desenvolvedor (DX)**.

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge\&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge\&logo=vue.js)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=for-the-badge\&logo=postgresql)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC?style=for-the-badge\&logo=tailwind-css)

---

# 🌎 Language / Idioma

* 🇧🇷 [Ver em Português](#-português)
* 🇺🇸 [Read in English](#-english)

---

# 🇧🇷 Português

# 📦 Visão Geral

ERP Vue Laravel é um ERP moderno projetado para entregar:

* ⚡ Alta performance
* 🔒 Segurança robusta
* 🧠 Excelente experiência para desenvolvedores
* 🧩 Arquitetura modular
* 🚀 Desenvolvimento rápido usando Inertia.js

O projeto foca em **simplicidade sem perder escalabilidade**.

---

# 🧰 Tecnologias

| Camada      | Tecnologia              |
| ----------- | ----------------------- |
| Backend     | Laravel 11 (PHP 8.2+)   |
| Frontend    | Vue 3 (Composition API) |
| Build Tool  | Vite                    |
| Comunicação | Inertia.js              |
| Estilização | Tailwind CSS            |
| Icons       | Lucide Vue              |

---

# ⚡ Experiência do Desenvolvedor (DX)

Para acelerar desenvolvimento e testes, o sistema possui utilitários globais de formulário.

## Atalhos de Teclado

| Atalho           | Ação                                    |
| ---------------- | --------------------------------------- |
| CTRL + SHIFT + P | Preenche formulário com dados fictícios |
| CTRL + SHIFT + L | Limpa campos e erros de validação       |

TIP
Esses atalhos utilizam **Custom Events** disparados dentro do `AuthenticatedLayout.vue`, mantendo a lógica das páginas limpa.

---

# 🚀 Instalação

## 1. Clonar o repositório

```bash
git clone https://github.com/letsmg/erp-vue-laravel.git
cd erp-vue-laravel
```

---

## 2. Instalar dependências

### PHP

```bash
composer install
```

### JavaScript

```bash
npm install
npm run dev
```

---

## 3. Configurar ambiente

```bash
cp .env.example .env
```

Configure o banco:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=erp_vue_laravel
DB_USERNAME=postgres
DB_PASSWORD=123456
```

NOTE
Certifique-se de que as extensões **pdo_pgsql** e **pgsql** estão ativas no `php.ini`.

---

## 4. Rodar migrações

```bash
php artisan migrate --seed
```

Isso criará a estrutura do banco e o **usuário administrador inicial**.

---

# ⚙️ Arquitetura

## Abordagem: Inertia.js vs API REST

Este projeto utiliza uma arquitetura baseada em **Inertia.js**, evitando a necessidade de uma API REST separada.

### Motivações da escolha

* Elimina a duplicação de lógica entre frontend e backend
* Reduz complexidade de autenticação (CSRF nativo)
* Permite desenvolvimento mais rápido
* Compartilhamento direto de estado entre backend e frontend

---

## Escalabilidade para API

A arquitetura foi pensada para permitir evolução futura para API REST sem retrabalho significativo:

* Regras de negócio centralizadas em **Services**
* Controllers podem ser adaptados para retornar JSON
* Autenticação pode ser feita via **Laravel Sanctum**
* Alto reaproveitamento de código

---

# 🔒 Segurança e Performance

## Autenticação

* Hash utilizando Argon2id
* Memory cost: 64MB
* Threads: 2

Configuração focada em maior resistência a ataques de força bruta.

---

## Banco de Dados

* Utilização de PostgreSQL
* Uso de paginação com filtros
* Estrutura preparada para indexação em campos críticos

---

# 🔍 SEO

A entidade de produtos possui suporte completo a SEO:

- `slug`
- `meta_title`
- `meta_description`
- `meta_keywords`
- `canonical_url`
- `h1`
- `text1`
- `h2`
- `text2`
- `schema_markup`
- `google_tag_manager`
- `ads`

Com geração automática de URLs amigáveis e estrutura preparada para otimização avançada em mecanismos de busca, incluindo controle de conteúdo, metadados e integrações externas.

---

# ⚡ Experiência do Usuário

Funcionalidades implementadas:

* Busca em tempo real com debounce
* Filtros dinâmicos no módulo de fornecedores
* Interface reativa via Inertia.js

---

# 🤖 Moderação de Imagens (Opcional)

Suporte à integração com Google Cloud Vision para análise automática de imagens durante o upload.

* Detecção de conteúdo impróprio
* Bloqueio automático de uploads inválidos

---

# 📦 Módulos do Sistema

## Implementados

* CRUD de Usuários com controle de acesso
* CRUD de Fornecedores
* CRUD de Produtos
* Paginação com filtros
* Upload e ordenação de imagens (drag and drop)
* SEO básico
* Relatórios de produtos
* Testes com PHPUnit

## Em desenvolvimento

* CRUD de Clientes
* CRUD de Vendas
* Relatório de Curva ABC
* Geração de Sintegra

---

# 🇺🇸 English

# 📦 Overview

ERP Vue Laravel is a modern ERP designed to deliver:

* ⚡ High performance
* 🔒 Robust security
* 🧠 Excellent developer experience
* 🧩 Modular architecture
* 🚀 Rapid development using Inertia.js

The project focuses on **simplicity without sacrificing scalability**.

---

# 🧰 Tech Stack

| Layer         | Technology              |
| ------------- | ----------------------- |
| Backend       | Laravel 11 (PHP 8.2+)   |
| Frontend      | Vue 3 (Composition API) |
| Build Tool    | Vite                    |
| Communication | Inertia.js              |
| Styling       | Tailwind CSS            |
| Icons         | Lucide Vue              |

---

# ⚡ Developer Experience (DX)

To accelerate development and testing, the system includes global form utilities.

## Keyboard Shortcuts

| Shortcut         | Action                                  |
| ---------------- | --------------------------------------- |
| CTRL + SHIFT + P | Populate form with fake data            |
| CTRL + SHIFT + L | Clear form fields and validation errors |

NOTE
These shortcuts use **Custom Events** triggered inside `AuthenticatedLayout.vue`, keeping page logic clean.

---

# 🚀 Installation

## 1. Clone repository

```bash
git clone https://github.com/letsmg/erp-vue-laravel.git
cd erp-vue-laravel
```

---

## 2. Install dependencies

### PHP

```bash
composer install
```

### JavaScript

```bash
npm install
npm run dev
```

---

## 3. Configure environment

```bash
cp .env.example .env
```

Database example:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=erp_vue_laravel
DB_USERNAME=postgres
DB_PASSWORD=123456
```

NOTE
Make sure **pdo_pgsql** and **pgsql** extensions are enabled in `php.ini`.

---

## 4. Run migrations

```bash
php artisan migrate --seed
```

This will create the database structure and generate the **initial admin user**.

---

# ⚙️ Architecture

## Approach: Inertia.js vs REST API

This project uses an architecture based on **Inertia.js**, avoiding the need for a separate REST API.

### Why this approach?

* Eliminates duplicated logic between frontend and backend
* Reduces authentication complexity (native CSRF protection)
* Enables faster development
* Allows direct state sharing between backend and frontend

---

## API Scalability

The architecture is designed to support future API exposure with minimal refactoring:

* Business logic centralized in **Services**
* Controllers can be adapted to return JSON responses
* Authentication can be handled via Laravel Sanctum
* High code reuse

---

# 🔒 Security and Performance

## Authentication

* Argon2id hashing
* Memory cost: 64MB
* Threads: 2

Configuration focused on resistance against brute-force attacks.

---

## Database

* Uses PostgreSQL
* Filter-based pagination
* Structure prepared for indexing on critical fields

---

# 🔍 SEO

The product entity provides full SEO support:

- `slug`
- `meta_title`
- `meta_description`
- `meta_keywords`
- `canonical_url`
- `h1`
- `text1`
- `h2`
- `text2`
- `schema_markup`
- `google_tag_manager`
- `ads`

Includes automatic generation of SEO-friendly URLs and a structure designed for advanced search engine optimization, covering content management, metadata, and external integrations.

---

# ⚡ User Experience

Implemented features:

* Real-time search with debounce
* Dynamic filters in supplier module
* Reactive interface powered by Inertia.js

---

# 🤖 Image Moderation (Optional)

Supports integration with Google Cloud Vision for automatic image analysis during uploads.

* Detects inappropriate content
* Automatically blocks invalid uploads

---

# 📦 System Modules

## Implemented

* User CRUD with role-based access control
* Supplier CRUD
* Product CRUD
* Filtered pagination
* Drag-and-drop image ordering
* Basic SEO
* Product reports
* Tests with PHPUnit

## In Progress

* Customer CRUD
* Sales CRUD
* ABC Curve report
* Sintegra generation

---

# 📄 License

MIT License

---

<p align="center">
<strong>ERP Vue Laravel — Technology for Smart Business</strong>
</p>

<p align="center">
© 2026 — Built with scalability in mind
</p>

<img src="https://raw.githubusercontent.com/letsmg/erp-vue-laravel/main/snake-dark.svg?palette=github-dark" />
