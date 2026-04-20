<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import ClientLayout from '@/Layouts/ClientLayout.vue'

defineOptions({ layout: ClientLayout })

const props = defineProps<{
  sale_id: number
}>()
</script>

<template>
  <Head title="Pagamento Falhou" />

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200 text-center">
          <div class="mb-6">
            <svg class="mx-auto h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>

          <h1 class="text-3xl font-bold text-gray-900 mb-2">Pagamento Falhou</h1>
          <p class="text-gray-600 mb-6">Não foi possível processar o pagamento do pedido #{{ props.sale_id }}.</p>

          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-yellow-800 text-sm">O pedido ainda está pendente. Você pode tentar o pagamento novamente ou entrar em contato com o suporte.</p>
          </div>

          <div class="space-x-4">
            <Link :href="route('store.index')" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
              Continuar Comprando
            </Link>
            <Link :href="route('client.orders.show', props.sale_id)" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
              Ver Pedido
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
