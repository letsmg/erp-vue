<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import ClientLayout from '@/Layouts/ClientLayout.vue'

defineOptions({ layout: ClientLayout })

const props = defineProps<{
  id: number
}>()

const order = ref<any>(null)
const loading = ref(true)

const fetchOrder = async () => {
  try {
    const response = await axios.get(route('client.orders.show', props.id))
    order.value = response.data.data
  } catch (error) {
    console.error('Erro ao carregar pedido:', error)
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

const cancelOrder = async () => {
  if (!confirm('Tem certeza que deseja cancelar este pedido?')) return

  try {
    await axios.post(route('client.orders.cancel', props.id))
    await fetchOrder()
  } catch (error) {
    console.error('Erro ao cancelar pedido:', error)
  }
}

onMounted(() => {
  fetchOrder()
})
</script>

<template>
  <Head title="Detalhes do Pedido" />

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <div class="mb-6">
            <Link :href="route('client.orders.index')" class="text-blue-600 hover:text-blue-800 text-sm">
              ← Voltar para meus pedidos
            </Link>
          </div>

          <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
            <p class="mt-2 text-gray-600">Carregando pedido...</p>
          </div>

          <div v-else-if="order">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">Pedido #{{ order.id }}</h1>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div class="space-y-4">
                <div class="bg-gray-50 rounded-lg p-4">
                  <h2 class="text-lg font-medium text-gray-900 mb-2">Status</h2>
                  <span :class="['px-3 py-1 rounded-full text-sm font-medium', getStatusColor(order.status)]">
                    {{ getStatusLabel(order.status) }}
                  </span>
                </div>

                <div v-if="order.address" class="bg-gray-50 rounded-lg p-4">
                  <h2 class="text-lg font-medium text-gray-900 mb-2">Endereço de Entrega</h2>
                  <p class="text-gray-600">{{ order.address.street }}, {{ order.address.number }}</p>
                  <p class="text-gray-600">{{ order.address.neighborhood }}</p>
                  <p class="text-gray-600">{{ order.address.city }} - {{ order.address.state }}</p>
                  <p class="text-gray-600">CEP: {{ order.address.zip_code }}</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                  <h2 class="text-lg font-medium text-gray-900 mb-2">Data do Pedido</h2>
                  <p class="text-gray-600">{{ new Date(order.created_at).toLocaleString('pt-BR') }}</p>
                </div>

                <div v-if="order.isPending()" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                  <p class="text-yellow-800 text-sm">Este pedido ainda está pendente de pagamento.</p>
                  <button
                    @click="cancelOrder"
                    class="mt-2 text-sm text-red-600 hover:text-red-800 font-medium"
                  >
                    Cancelar pedido
                  </button>
                </div>
              </div>

              <div>
                <h2 class="text-lg font-medium text-gray-900 mb-4">Itens do Pedido</h2>
                <div class="space-y-3">
                  <div v-for="item in order.items" :key="item.id" class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                      <p class="text-gray-900 font-medium">{{ item.product_description }}</p>
                      <p class="text-sm text-gray-600">Quantidade: {{ item.quantity }}</p>
                    </div>
                    <span class="text-gray-900 font-medium">R$ {{ item.subtotal.toFixed(2).replace('.', ',') }}</span>
                  </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                  <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-gray-900">Total</span>
                    <span class="text-2xl font-bold text-gray-900">R$ {{ order.total_amount.toFixed(2).replace('.', ',') }}</span>
                  </div>
                </div>

                <div v-if="order.payment" class="mt-6 pt-6 border-t border-gray-200">
                  <h2 class="text-lg font-medium text-gray-900 mb-2">Informações de Pagamento</h2>
                  <p class="text-sm text-gray-600">Tipo: {{ order.payment.payment_type }}</p>
                  <p class="text-sm text-gray-600">Status: {{ getStatusLabel(order.payment.status) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
