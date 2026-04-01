<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const page = usePage()
const user = page.props.auth.user
</script>

<template>
  <Head title="Dashboard Cliente" />

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">
              Bem-vindo, {{ user.name }}!
            </h1>
            <p class="mt-1 text-sm text-gray-600">
              Este é o seu painel de cliente. Aqui você pode gerenciar seus dados e acompanhar seus pedidos.
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Card Meus Dados -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium">Meus Dados</h3>
                  <p class="text-blue-100">Atualize suas informações</p>
                </div>
              </div>
              <div class="mt-4">
                <Link
                  :href="route('client.profile')"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Gerenciar
                </Link>
              </div>
            </div>

            <!-- Card Pedidos -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium">Meus Pedidos</h3>
                  <p class="text-green-100">Acompanhe suas compras</p>
                </div>
              </div>
              <div class="mt-4">
                <button
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-green-600 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                  disabled
                >
                  Em breve
                </button>
              </div>
            </div>

            <!-- Card Carrinho -->
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-6 text-white">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium">Carrinho</h3>
                  <p class="text-purple-100">Continue comprando</p>
                </div>
              </div>
              <div class="mt-4">
                <Link
                  :href="route('store.index')"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-purple-600 bg-white hover:bg-purple-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                >
                  Comprar
                </Link>
              </div>
            </div>

            <!-- Card Suporte -->
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-6 text-white">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium">Suporte</h3>
                  <p class="text-orange-100">Precisa de ajuda?</p>
                </div>
              </div>
              <div class="mt-4">
                <button
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-orange-600 bg-white hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                  disabled
                >
                  Em breve
                </button>
              </div>
            </div>
          </div>

          <!-- Informações da conta -->
          <div class="bg-gray-50 rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Informações da conta</h2>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
              <div>
                <dt class="text-sm font-medium text-gray-500">Nome</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ user.name }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">E-mail</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ user.email }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Tipo de conta</dt>
                <dd class="mt-1 text-sm text-gray-900">Cliente</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Ativo
                  </span>
                </dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
