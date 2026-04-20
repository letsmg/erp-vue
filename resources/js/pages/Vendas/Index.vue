<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const sales = ref<any[]>([])
const loading = ref(true)
const filters = ref({
  status: '',
  date_from: '',
  date_to: '',
  client_id: '',
})

const fetchSales = async () => {
  try {
    const response = await axios.get(route('vendas.index'), { params: filters.value })
    sales.value = response.data.data
  } catch (error) {
    console.error('Erro ao carregar vendas:', error)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  fetchSales()
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
  fetchSales()
})
</script>

<template>
  <Head title="Vendas" />

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <h1 class="text-2xl font-semibold text-gray-900 mb-6">Vendas</h1>

          <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select v-model="filters.status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                  <option value="">Todos</option>
                  <option value="pending">Pendente</option>
                  <option value="paid">Pago</option>
                  <option value="canceled">Cancelado</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                <input v-model="filters.date_from" type="date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                <input v-model="filters.date_to" type="date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              </div>
              <div class="flex items-end">
                <button @click="applyFilters" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                  Filtrar
                </button>
              </div>
            </div>
          </div>

          <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
            <p class="mt-2 text-gray-600">Carregando vendas...</p>
          </div>

          <div v-else-if="sales.length === 0" class="text-center py-12">
            <p class="text-gray-600">Nenhuma venda encontrada.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="sale in sales" :key="sale.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ sale.id }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ sale.client?.name || '-' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(sale.created_at).toLocaleDateString('pt-BR') }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">R$ {{ sale.total_amount.toFixed(2).replace('.', ',') }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusColor(sale.status)]">
                      {{ getStatusLabel(sale.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <Link :href="route('vendas.show', sale.id)" class="text-blue-600 hover:text-blue-900 mr-3">Ver</Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
