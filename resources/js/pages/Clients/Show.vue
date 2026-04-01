<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps<{
  client: any
}>()

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

const getContributorTypeDescription = (type: number) => {
  const types = {
    1: 'Contribuinte ICMS',
    2: 'Isento',
    9: 'Não Contribuinte'
  }
  return types[type] || '-'
}
</script>

<template>
  <Head :title="`Cliente - ${client.name}`" />

  <div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <!-- Cabeçalho -->
          <div class="flex items-center justify-between mb-6">
            <div>
              <h1 class="text-2xl font-semibold text-gray-900">{{ client.name }}</h1>
              <p class="mt-1 text-sm text-gray-600">
                Detalhes do cadastro do cliente
              </p>
            </div>
            <div class="flex space-x-3">
              <Link
                :href="route('clients.edit', client.id)"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar
              </Link>
              <Link
                :href="route('clients.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Voltar
              </Link>
            </div>
          </div>

          <!-- Status -->
          <div class="mb-6">
            <span
              :class="[
                'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                client.is_active
                  ? 'bg-green-100 text-green-800'
                  : 'bg-red-100 text-red-800'
              ]"
            >
              <svg
                :class="[
                  'mr-2 h-4 w-4',
                  client.is_active ? 'text-green-600' : 'text-red-600'
                ]"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  v-if="client.is_active"
                  fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                  clip-rule="evenodd"
                />
                <path
                  v-else
                  fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                  clip-rule="evenodd"
                />
              </svg>
              {{ client.is_active ? 'Cliente Ativo' : 'Cliente Inativo' }}
            </span>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Coluna Principal -->
            <div class="lg:col-span-2 space-y-8">
              <!-- Dados Principais -->
              <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Dados Principais</h2>
                
                <dl class="grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-4 sm:gap-y-6">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Nome/Razão Social</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ client.name }}</dd>
                  </div>
                  
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Tipo de Documento</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ client.document_type }}</dd>
                  </div>
                  
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Documento</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ formatDocument(client.document_number, client.document_type) }}
                    </dd>
                  </div>
                  
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Data de Cadastro</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ new Date(client.created_at).toLocaleDateString('pt-BR') }}
                    </dd>
                  </div>
                </dl>
              </div>

              <!-- Dados Fiscais (se CNPJ) -->
              <div v-if="client.document_type === 'CNPJ'" class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Dados Fiscais</h2>
                
                <dl class="grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-4 sm:gap-y-6">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Inscrição Estadual</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ client.state_registration || '-' }}
                    </dd>
                  </div>
                  
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Inscrição Municipal</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ client.municipal_registration || '-' }}
                    </dd>
                  </div>
                  
                  <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Tipo de Contribuinte</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ getContributorTypeDescription(client.contributor_type) }}
                    </dd>
                  </div>
                </dl>
              </div>

              <!-- Contatos -->
              <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Contatos</h2>
                
                <dl class="space-y-4">
                  <div v-if="client.phone1 || client.contact1">
                    <dt class="text-sm font-medium text-gray-500">Contato Principal</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      <div v-if="client.phone1">{{ formatPhone(client.phone1) }}</div>
                      <div v-if="client.contact1">{{ client.contact1 }}</div>
                    </dd>
                  </div>
                  
                  <div v-if="client.phone2 || client.contact2">
                    <dt class="text-sm font-medium text-gray-500">Contato Secundário</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      <div v-if="client.phone2">{{ formatPhone(client.phone2) }}</div>
                      <div v-if="client.contact2">{{ client.contact2 }}</div>
                    </dd>
                  </div>
                  
                  <div v-if="client.phone">
                    <dt class="text-sm font-medium text-gray-500">Telefone Geral</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ formatPhone(client.phone) }}
                    </dd>
                  </div>
                </dl>
              </div>

              <!-- Endereços -->
              <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Endereços</h2>
                
                <div v-if="client.addresses && client.addresses.length > 0" class="space-y-4">
                  <div
                    v-for="address in client.addresses"
                    :key="address.id"
                    class="bg-white rounded-lg p-4 border border-gray-200"
                  >
                    <div class="flex items-start justify-between">
                      <div class="flex-1">
                        <div class="flex items-center space-x-2">
                          <span class="text-sm font-medium text-gray-900">
                            {{ address.street }}, {{ address.number }}
                          </span>
                          <span
                            v-if="address.is_delivery_address"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                          >
                            Endereço Principal
                          </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">
                          {{ address.neighborhood }}, {{ address.city }} - {{ address.state }}
                        </p>
                        <p class="text-sm text-gray-600">
                          CEP: {{ address.zip_code }}
                        </p>
                        <p v-if="address.complement" class="text-sm text-gray-600">
                          {{ address.complement }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div v-else class="text-center py-8">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <p class="mt-2 text-sm text-gray-600">Nenhum endereço cadastrado</p>
                </div>
              </div>
            </div>

            <!-- Coluna Lateral -->
            <div class="space-y-8">
              <!-- Acesso ao Sistema -->
              <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Acesso ao Sistema</h2>
                
                <div v-if="client.user">
                  <dl class="space-y-4">
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Nome do Usuário</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ client.user.name }}</dd>
                    </div>
                    
                    <div>
                      <dt class="text-sm font-medium text-gray-500">E-mail</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ client.user.email }}</dd>
                    </div>
                    
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Nível de Acesso</dt>
                      <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                          Cliente
                        </span>
                      </dd>
                    </div>
                    
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Status do Usuário</dt>
                      <dd class="mt-1">
                        <span
                          :class="[
                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                            client.user.is_active
                              ? 'bg-green-100 text-green-800'
                              : 'bg-red-100 text-red-800'
                          ]"
                        >
                          {{ client.user.is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                      </dd>
                    </div>
                  </dl>
                </div>
                
                <div v-else class="text-center py-4">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <p class="mt-2 text-sm text-gray-600">Usuário não associado</p>
                </div>
              </div>

              <!-- Informações do Sistema -->
              <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Informações do Sistema</h2>
                
                <dl class="space-y-4">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">ID do Cliente</dt>
                    <dd class="mt-1 text-sm text-gray-900">#{{ client.id }}</dd>
                  </div>
                  
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Data de Cadastro</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ new Date(client.created_at).toLocaleDateString('pt-BR') }}
                    </dd>
                  </div>
                  
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Última Atualização</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ new Date(client.updated_at).toLocaleDateString('pt-BR') }}
                    </dd>
                  </div>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
