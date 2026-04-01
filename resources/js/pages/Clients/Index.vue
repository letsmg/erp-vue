<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, computed } from 'vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps<{
  clients: any
  filters: any
}>()

const search = ref(props.filters.search || '')
const documentType = ref(props.filters.document_type || '')
const isActive = ref(props.filters.is_active !== undefined ? props.filters.is_active : '')
const contributorType = ref(props.filters.contributor_type || '')

const showFilters = ref(false)

const filteredClients = computed(() => props.clients.data)

const applyFilters = () => {
  const params: any = {}
  
  if (search.value) params.search = search.value
  if (documentType.value) params.document_type = documentType.value
  if (isActive.value !== '') params.is_active = isActive.value
  if (contributorType.value) params.contributor_type = contributorType.value
  
  router.get(route('clients.index'), params, {
    preserveState: true,
    replace: true
  })
}

const clearFilters = () => {
  search.value = ''
  documentType.value = ''
  isActive.value = ''
  contributorType.value = ''
  applyFilters()
}

const deleteForm = useForm({})
const deleteClient = (client: any) => {
  if (confirm(`Tem certeza que deseja excluir o cliente "${client.name}"?`)) {
    deleteForm.delete(route('clients.destroy', client.id))
  }
}

const toggleStatus = (client: any) => {
  router.get(route('clients.toggle.status', client.id), {}, {
    preserveScroll: true
  })
}

const formatDocument = (doc: string, type: string) => {
  if (!doc) return '-'
  
  if (type === 'CPF') {
    return doc.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
  } else {
    return doc.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5')
  }
}

const formatPhone = (phone: string) => {
  if (!phone) return '-'
  
  const cleaned = phone.replace(/\D/g, '')
  if (cleaned.length <= 10) {
    return cleaned.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3')
  } else {
    return cleaned.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3')
  }
}
</script>

<template>
  <Head title="Clientes" />

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h1 class="text-2xl font-semibold text-gray-900">Clientes</h1>
              <p class="mt-1 text-sm text-gray-600">
                Gerencie o cadastro de clientes do sistema
              </p>
            </div>
            <Link
              :href="route('clients.create')"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Novo Cliente
            </Link>
          </div>

          <!-- Filtros -->
          <div class="mb-6">
            <button
              @click="showFilters = !showFilters"
              class="flex items-center text-sm text-gray-600 hover:text-gray-900"
            >
              <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
              </svg>
              Filtros
              <svg
                :class="showFilters ? 'rotate-180' : ''"
                class="ml-2 h-4 w-4 transform transition-transform"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <div v-show="showFilters" class="mt-4 bg-gray-50 rounded-lg p-4">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Buscar
                  </label>
                  <input
                    v-model="search"
                    type="text"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Nome, documento ou e-mail"
                    @keyup.enter="applyFilters"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tipo de Documento
                  </label>
                  <select
                    v-model="documentType"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    @change="applyFilters"
                  >
                    <option value="">Todos</option>
                    <option value="CPF">CPF</option>
                    <option value="CNPJ">CNPJ</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Status
                  </label>
                  <select
                    v-model="isActive"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    @change="applyFilters"
                  >
                    <option value="">Todos</option>
                    <option value="1">Ativo</option>
                    <option value="0">Inativo</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tipo Contribuinte
                  </label>
                  <select
                    v-model="contributorType"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    @change="applyFilters"
                  >
                    <option value="">Todos</option>
                    <option value="1">Contribuinte ICMS</option>
                    <option value="2">Isento</option>
                    <option value="9">Não Contribuinte</option>
                  </select>
                </div>
              </div>

              <div class="mt-4 flex space-x-2">
                <button
                  @click="applyFilters"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  Aplicar Filtros
                </button>
                <button
                  @click="clearFilters"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  Limpar
                </button>
              </div>
            </div>
          </div>

          <!-- Tabela -->
          <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Cliente
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Documento
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contato
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Cadastrado em
                  </th>
                  <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Ações</span>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="client in filteredClients" :key="client.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">
                        {{ client.name }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ client.user?.email }}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">
                      {{ formatDocument(client.document_number, client.document_type) }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ client.document_type }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">
                      {{ formatPhone(client.phone1) }}
                    </div>
                    <div v-if="client.contact1" class="text-sm text-gray-500">
                      {{ client.contact1 }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        client.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      ]"
                    >
                      {{ client.is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ new Date(client.created_at).toLocaleDateString('pt-BR') }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                      <Link
                        :href="route('clients.show', client.id)"
                        class="text-indigo-600 hover:text-indigo-900"
                        title="Visualizar"
                      >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </Link>
                      <Link
                        :href="route('clients.edit', client.id)"
                        class="text-indigo-600 hover:text-indigo-900"
                        title="Editar"
                      >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </Link>
                      <button
                        @click="toggleStatus(client)"
                        :class="[
                          'hover:text-gray-900',
                          client.is_active ? 'text-yellow-600' : 'text-green-600'
                        ]"
                        :title="client.is_active ? 'Desativar' : 'Ativar'"
                      >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                      </button>
                      <button
                        @click="deleteClient(client)"
                        class="text-red-600 hover:text-red-900"
                        title="Excluir"
                      >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginação -->
          <div v-if="clients.links && clients.links.length > 3" class="mt-6">
            <nav class="flex items-center justify-between">
              <div class="flex-1 flex justify-between sm:hidden">
                <Link
                  v-if="clients.prev_page_url"
                  :href="clients.prev_page_url"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                  Anterior
                </Link>
                <Link
                  v-if="clients.next_page_url"
                  :href="clients.next_page_url"
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                  Próximo
                </Link>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    Mostrando
                    <span class="font-medium">{{ clients.from || 0 }}</span>
                    até
                    <span class="font-medium">{{ clients.to || 0 }}</span>
                    de
                    <span class="font-medium">{{ clients.total }}</span>
                    resultados
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <template v-for="(link, index) in clients.links" :key="index">
                      <span
                        v-if="!link.url"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-gray-50 text-sm font-medium text-gray-700"
                        v-html="link.label"
                      />
                      <Link
                        v-else
                        :href="link.url"
                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                        :class="[
                          link.active
                            ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                        ]"
                        v-html="link.label"
                      />
                    </template>
                  </nav>
                </div>
              </div>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
