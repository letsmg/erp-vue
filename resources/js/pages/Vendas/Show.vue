<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps<{
  id: number
}>()

const sale = ref<any>(null)
const loading = ref(true)
const newStatus = ref('')

const fetchSale = async () => {
  try {
    const response = await axios.get(route('vendas.show', props.id))
    sale.value = response.data.data
    newStatus.value = sale.value.status
  } catch (error) {
    console.error('Erro ao carregar venda:', error)
  } finally {
    loading.value = false
  }
}

const updateStatus = async () => {
  try {
    await axios.patch(route('vendas.update.status', props.id), { status: newStatus.value })
    await fetchSale()
  } catch (error) {
    console.error('Erro ao atualizar status:', error)
  }
}

const cancelSale = async () => {
  if (!confirm('Tem certeza que deseja cancelar esta venda?')) return

  try {
    await axios.post(route('vendas.cancel', props.id))
    await fetchSale()
  } catch (error) {
    console.error('Erro ao cancelar venda:', error)
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
  fetchSale()
})
</script>

<template>
  <Head title="Detalhes da Venda" />

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <div class="mb-6">
            <Link :href="route('vendas.index')" class="text-blue-600 hover:text-blue-800 text-sm">
              ← Voltar para vendas
            </Link>
          </div>

          <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
            <p class="mt-2 text-gray-600">Carregando venda...</p>
          </div>

          <div v-else-if="sale">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">Venda #{{ sale.id }}</h1>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div class="space-y-4">
                <div class="bg-gray-50 rounded-lg p-4">
                  <h2 class="text-lg font-medium text-gray-900 mb-2">Status</h2>
                  <div class="flex items-center gap-4">
                    <span :class="['px-3 py-1 rounded-full text-sm font-medium', getStatusColor(sale.status)]">
                      {{ getStatusLabel(sale.status) }}
                    </span>
                    <select v-model="newStatus" @change="updateStatus" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                      <option value="pending">Pendente</option>
                      <option value="paid">Pago</option>
                      <option value="canceled">Cancelado</option>
                    </select>
                  </div>
                  <button
                    v-if="sale.status !== 'canceled'"
                    @click="cancelSale"
                    class="mt-2 text-sm text-red-600 hover:text-red-800 font-medium"
                  >
                    Cancelar venda
                  </button>
                </div>

                <div v-if="sale.client" class="bg-gray-50 rounded-lg p-4">
                  <h2 class="text-lg font-medium text-gray-900 mb-2">Cliente</h2>
                  <p class="text-gray-600">{{ sale.client.name }}</p>
                  <p class="text-gray-600">{{ sale.client.email }}</p>
                  <p class="text-gray-600">{{ sale.client.phone1 }}</p>
                </div>

                <div v-if="sale.address" class="bg-gray-50 rounded-lg p-4">
                  <h2 class="text-lg font-medium text-gray-900 mb-2">Endereço de Entrega</h2>
                  <p class="text-gray-600">{{ sale.address.street }}, {{ sale.address.number }}</p>
                  <p class="text-gray-600">{{ sale.address.neighborhood }}</p>
                  <p class="text-gray-600">{{ sale.address.city }} - {{ sale.address.state }}</p>
                  <p class="text-gray-600">CEP: {{ sale.address.zip_code }}</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                  <h2 class="text-lg font-medium text-gray-900 mb-2">Data da Venda</h2>
                  <p class="text-gray-600">{{ new Date(sale.created_at).toLocaleString('pt-BR') }}</p>
                </div>
              </div>

              <div>
                <h2 class="text-lg font-medium text-gray-900 mb-4">Itens da Venda</h2>
                <div class="space-y-3">
                  <div v-for="item in sale.items" :key="item.id" class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                      <p class="text-gray-900 font-medium">{{ item.product_description }}</p>
                      <p class="text-sm text-gray-600">Quantidade: {{ item.quantity }}</p>
                      <p class="text-sm text-gray-600">Preço unitário: R$ {{ item.unit_price.toFixed(2).replace('.', ',') }}</p>
                    </div>
                    <span class="text-gray-900 font-medium">R$ {{ item.subtotal.toFixed(2).replace('.', ',') }}</span>
                  </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                  <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-gray-900">Total</span>
                    <span class="text-2xl font-bold text-gray-900">R$ {{ sale.total_amount.toFixed(2).replace('.', ',') }}</span>
                  </div>
                </div>

                <div v-if="sale.payment" class="mt-6 pt-6 border-t border-gray-200">
                  <h2 class="text-lg font-medium text-gray-900 mb-2">Informações de Pagamento</h2>
                  <p class="text-sm text-gray-600">Tipo: {{ sale.payment.payment_type }}</p>
                  <p class="text-sm text-gray-600">Status: {{ getStatusLabel(sale.payment.status) }}</p>
                  <p v-if="sale.payment.payment_id" class="text-sm text-gray-600">ID Mercado Pago: {{ sale.payment.payment_id }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
