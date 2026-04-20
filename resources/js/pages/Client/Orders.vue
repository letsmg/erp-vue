<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import ClientLayout from '@/Layouts/ClientLayout.vue'

defineOptions({ layout: ClientLayout })

const orders = ref<any[]>([])
const loading = ref(true)

const fetchOrders = async () => {
  try {
    const response = await axios.get(route('client.orders.index'))
    orders.value = response.data.data
  } catch (error) {
    console.error('Erro ao carregar pedidos:', error)
  } finally {
    loading.value = false
  }
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'pending': return 'bg-yellow-100 text-yellow-800'
    case 'paid': return 'bg-green-100 text-green-800'
    case 'canceled': return 'bg-red-100 text-red-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const getStatusLabel = (status: string) => {
  switch (status) {
    case 'pending': return 'Pendente'
    case 'paid': return 'Pago'
    case 'canceled': return 'Cancelado'
    default: return status
  }
}

onMounted(() => {
  fetchOrders()
})
</script>

<template>
  <Head title="Meus Pedidos" />

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <h1 class="text-2xl font-semibold text-gray-900 mb-6">Meus Pedidos</h1>

          <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
            <p class="mt-2 text-gray-600">Carregando pedidos...</p>
          </div>

          <div v-else-if="orders.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <p class="mt-2 text-gray-600">Você ainda não tem pedidos.</p>
            <Link :href="route('store.index')" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
              Ir para a loja
            </Link>
          </div>

          <div v-else class="space-y-4">
            <div v-for="order in orders" :key="order.id" class="border border-gray-200 rounded-lg p-4">
              <div class="flex justify-between items-start mb-4">
                <div>
                  <p class="text-sm text-gray-600">Pedido #{{ order.id }}</p>
                  <p class="text-sm text-gray-500">{{ new Date(order.created_at).toLocaleDateString('pt-BR') }}</p>
                </div>
                <span :class="['px-3 py-1 rounded-full text-xs font-medium', getStatusColor(order.status)]">
                  {{ getStatusLabel(order.status) }}
                </span>
              </div>

              <div class="space-y-2 mb-4">
                <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
                  <span class="text-gray-600">{{ item.product_description }} x{{ item.quantity }}</span>
                  <span class="text-gray-900">R$ {{ item.subtotal.toFixed(2).replace('.', ',') }}</span>
                </div>
              </div>

              <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                <span class="text-lg font-semibold text-gray-900">Total: R$ {{ order.total_amount.toFixed(2).replace('.', ',') }}</span>
                <Link :href="route('client.orders.show', order.id)" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                  Ver detalhes
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
