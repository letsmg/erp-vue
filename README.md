<img src="https://raw.githubusercontent.com/letsmg/erp-vue-laravel/main/pacman-contribution-graph.svg" />



# 🌌 ERP Vue Laravel — Gestão Inteligente
> Sistema de gestão empresarial (ERP) moderno, focado em alta performance, segurança robusta e experiência do desenvolvedor (DX).

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=for-the-badge&logo=postgresql)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC?style=for-the-badge&logo=tailwind-css)

---

## 🚀 Tecnologias Core
| Camada | Tecnologia |
| :--- | :--- |
| **Backend** | Laravel 11 (PHP 8.2+) |
| **Frontend** | Vue 3 (Composition API) |
| **Build Tool** | Vite |
| **Comunicação** | Inertia.js (Padrão SPA com SSR-ready) |
| **Estilização** | Tailwind CSS |
| **Icons** | Lucide-Vue-Next |

---

## ⚡ Developer Experience (DX) — Magic Shortcuts
Para acelerar o ciclo de testes e desenvolvimento, implementamos utilitários globais de manipulação de formulários:

- `CTRL` + `SHIFT` + `P` (**Populate**)  
  *Preenche automaticamente todos os campos com dados fictícios (Fakers) coerentes.*
- `CTRL` + `SHIFT` + `L` (**Clear**)  
  *Limpa instantaneamente os campos e remove erros de validação.*

> [!TIP]
> Esses atalhos utilizam **Custom Events** disparados no `AuthenticatedLayout.vue`, mantendo a reatividade do Inertia sem poluir a lógica de negócio das páginas.

---

## 🛠️ Instalação e Configuração

### 1. Banco de Dados (PostgreSQL)
Certifique-se de que as extensões `pdo_pgsql` e `pgsql` estão ativas no seu `php.ini`. No seu `.env`, configure:


DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=erp_vue_laravel
DB_USERNAME=postgres
DB_PASSWORD=123456

# Instalar dependências do PHP
composer install

# Instalar dependências do JS e compilar assets
npm install && npm run dev

# Rodar migrações e popular banco (Usuário Admin inicial)
php artisan migrate --seed

Inertia vs API Pura
Optamos pelo uso de Controllers Híbridos via Inertia.js em vez de uma pasta /Api separada.

Vantagem: O Inertia.js elimina a necessidade de rotas de API externas para o funcionamento do Frontend, utilizando o estado compartilhado e garantindo maior segurança via CSRF nativo do Laravel.

Agilidade: Desenvolvimento 2x mais rápido para sistemas de gestão (ERP).

Escalabilidade para Terceiros
Caso haja necessidade de uma API pública no futuro, a lógica de negócio está preparada para ser extraída para Services, permitindo que Controllers/Api exponham dados em JSON puro via Laravel Sanctum, mantendo o reaproveitamento total do código.

Segurança e Performance
Autenticação: Sistema manual (sem pacotes prontos) com Hashing Argon2id (64MB/2 Threads).

SEO & Vitrine: Tabela de produtos inclui campos de slug, seo_title e seo_keywords com geração automática de URL amigável.

UX Reativa: Busca em tempo real (Debounce Search) no módulo de fornecedores.

________________________________

🚀 Funcionalidades Extra:

Moderação de Imagens via IA: O sistema está preparado para integrar com Google Cloud Vision. Se detectar conteúdo impróprio, o upload é bloqueado automaticamente.

Caso você decida usar deve seguir esses passos:

1. Você precisa criar um projeto no Google Cloud Console:

2. Acesse o Google Cloud Console.

3. Crie um novo projeto (ex: "ERP-Sistema").

4. No menu lateral, vá em APIs e Serviços > Biblioteca.

5. Procure por "Cloud Vision API" e clique em Ativar.

6. Depois de ativa, vá em APIs e Serviços > Credenciais.

7. Clique em Criar Credenciais > Chave de conta de serviço.

8. Escolha um nome, e no papel (role), coloque Proprietário ou Cloud Vision AI > Usuário.

9. Ao final, ele vai baixar automaticamente um arquivo JSON para o seu computador.

10. SALVE O ARQUIVO COM A API NA RAIZ DO PROJETO COM O NOME

**google-credentials.json**

______________________________________



📦 Módulos Implementados


<ul>
   <li>
      [x] Gestão de Usuários: Controle de acesso (Admin/Operador) e visibilidade de senha.
   </li>
   <li>
[x] Fornecedores: Cadastro completo com máscaras dinâmicas de CNPJ e CEP.
   </li>
   <li>
[ ] Produtos: Catálogo com suporte a SEO e controle de estoque.
   </li>
   <li>
   [ ] Vendas: Fluxo operacional e relatórios.
   </li>
<ul>


<p align="center">
   <strong> ERP Vue Laravel — Tecnologia em Gestão </strong>   
</p>

<p align="center">
   &copy; 2026 — Desenvolvido com foco em escalabilidade.
</p>



<img src="https://raw.githubusercontent.com/letsmg/erp-vue-laravel/main/snake-dark.svg?palette=github-dark" />
