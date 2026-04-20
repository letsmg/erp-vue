<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import ClientLayout from '@/Layouts/ClientLayout.vue'

defineOptions({ layout: ClientLayout })

const props = defineProps<{
  sale_id: number
}>()

const order = ref<any>(null)
const loading = ref(true)

const fetchOrder = async () => {
  try {
    const response = await axios.get(route('client.orders.show', props.sale_id))
    order.value = response.data.data
  } catch (error) {
    console.error('Erro ao carregar pedido:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchOrder()
})
</script>

<template>
  <Head title="Pagamento Aprovado" />

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200 text-center">
          <div v-if="loading" class="py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
          </div>

          <div v-else>
            <div class="mb-6">
              <svg class="mx-auto h-16 w-16 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-2">Pagamento Aprovado!</h1>
            <p class="text-gray-600 mb-6">Seu pedido #{{ props.sale_id }} foi confirmado com sucesso.</p>

            <div v-if="order" class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Resumo do Pedido</h2>
              <div class="space-y-2">
                <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
                  <span class="text-gray-600">{{ item.product_description }} x{{ item.quantity }}</span>
                  <span class="text-gray-900">R$ {{ item.subtotal.toFixed(2).replace('.', ',') }}</span>
                </div>
              </div>
              <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex justify-between">
                  <span class="font-semibold">Total</span>
                  <span class="font-bold">R$ {{ order.total_amount.toFixed(2).replace('.', ',') }}</span>
                </div>
              </div>
            </div>

            <div class="space-x-4">
              <Link :href="route('client.orders.show', props.sale_id)" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                Ver Detalhes do Pedido
              </Link>
              <Link :href="route('client.orders.index')" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Meus Pedidos
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
