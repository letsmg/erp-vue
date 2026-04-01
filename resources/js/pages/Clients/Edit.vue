<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps<{
  client: any
}>()

const form = useForm({
  name: props.client.name,
  document_number: props.client.document_number,
  document_type: props.client.document_type,
  state_registration: props.client.state_registration || '',
  municipal_registration: props.client.municipal_registration || '',
  contributor_type: props.client.contributor_type?.toString() || '9',
  phone1: props.client.phone1 || '',
  contact1: props.client.contact1 || '',
  phone2: props.client.phone2 || '',
  contact2: props.client.contact2 || '',
  phone: props.client.phone || '',
  is_active: props.client.is_active,
  user_id: props.client.user_id,
  user_name: props.client.user?.name || '',
  user_email: props.client.user?.email || '',
})

const formatDocument = (event: Event) => {
  const input = event.target as HTMLInputElement
  let value = input.value.replace(/\D/g, '')
  
  if (value.length <= 11) {
    // CPF: 000.000.000-00
    value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
    form.document_type = 'CPF'
    form.contributor_type = '9' // Não Contribuinte para PF
    if (form.document_type !== props.client.document_type) {
      form.state_registration = ''
      form.municipal_registration = ''
    }
  } else {
    // CNPJ: 00.000.000/0000-00
    value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5')
    form.document_type = 'CNPJ'
    form.contributor_type = '1' // Contribuinte para PJ
  }
  
  input.value = value
  form.document_number = value
}

const formatPhone = (event: Event, field: string) => {
  const input = event.target as HTMLInputElement
  let value = input.value.replace(/\D/g, '')
  
  if (value.length <= 10) {
    // Telefone fixo: (00) 0000-0000
    value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3')
  } else {
    // Celular: (00) 00000-0000
    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3')
  }
  
  input.value = value
  if (field === 'phone1') {
    form.phone1 = value
  } else if (field === 'phone2') {
    form.phone2 = value
  } else if (field === 'phone') {
    form.phone = value
  }
}

const validateDocument = async () => {
  if (!form.document_number) return
  
  try {
    const response = await fetch(route('clients.validate.document'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        document: form.document_number.replace(/\D/g, ''),
        exclude_id: props.client.id
      })
    })
    
    const data = await response.json()
    
    if (!data.success) {
      form.setError('document_number', data.message)
    } else {
      form.clearErrors('document_number')
    }
  } catch (error) {
    console.error('Erro ao validar documento:', error)
  }
}

const submit = () => {
  form.put(route('clients.update', props.client.id))
}
</script>

<template>
  <Head :title="`Editar Cliente - ${client.name}`" />

  <div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Editar Cliente</h1>
            <p class="mt-1 text-sm text-gray-600">
              Atualize os dados do cliente
            </p>
          </div>

          <form @submit.prevent="submit" class="space-y-8">
            <!-- Dados Principais -->
            <div class="bg-gray-50 rounded-lg p-6">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Dados Principais</h2>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">
                    Nome/Razão Social *
                  </label>
                  <div class="mt-1">
                    <input
                      id="name"
                      v-model="form.name"
                      type="text"
                      class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                      required
                    />
                  </div>
                  <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                    {{ form.errors.name }}
                  </p>
                </div>

                <div>
                  <label for="document_number" class="block text-sm font-medium text-gray-700">
                    CPF/CNPJ *
                  </label>
                  <div class="mt-1">
                    <input
                      id="document_number"
                      v-model="form.document_number"
                      type="text"
                      class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                      required
                      maxlength="18"
                      placeholder="000.000.000-00 ou 00.000.000/0000-00"
                      @input="formatDocument"
                      @blur="validateDocument"
                    />
                  </div>
                  <p v-if="form.errors.document_number" class="mt-2 text-sm text-red-600">
                    {{ form.errors.document_number }}
                  </p>
                </div>
              </div>

              <!-- Dados Fiscais (apenas para CNPJ) -->
              <div v-if="form.document_type === 'CNPJ'" class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <div>
                  <label for="state_registration" class="block text-sm font-medium text-gray-700">
                    Inscrição Estadual
                  </label>
                  <div class="mt-1">
                    <input
                      id="state_registration"
                      v-model="form.state_registration"
                      type="text"
                      class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                    />
                  </div>
                  <p v-if="form.errors.state_registration" class="mt-2 text-sm text-red-600">
                    {{ form.errors.state_registration }}
                  </p>
                </div>

                <div>
                  <label for="municipal_registration" class="block text-sm font-medium text-gray-700">
                    Inscrição Municipal
                  </label>
                  <div class="mt-1">
                    <input
                      id="municipal_registration"
                      v-model="form.municipal_registration"
                      type="text"
                      class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                    />
                  </div>
                  <p v-if="form.errors.municipal_registration" class="mt-2 text-sm text-red-600">
                    {{ form.errors.municipal_registration }}
                  </p>
                </div>

                <div>
                  <label for="contributor_type" class="block text-sm font-medium text-gray-700">
                    Tipo Contribuinte *
                  </label>
                  <div class="mt-1">
                    <select
                      id="contributor_type"
                      v-model="form.contributor_type"
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      required
                    >
                      <option value="1">Contribuinte ICMS</option>
                      <option value="2">Isento</option>
                      <option value="9">Não Contribuinte</option>
                    </select>
                  </div>
                  <p v-if="form.errors.contributor_type" class="mt-2 text-sm text-red-600">
                    {{ form.errors.contributor_type }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Contatos -->
            <div class="bg-gray-50 rounded-lg p-6">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Contatos</h2>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="phone1" class="block text-sm font-medium text-gray-700">
                    Telefone Principal
                  </label>
                  <div class="mt-1">
                    <input
                      id="phone1"
                      v-model="form.phone1"
                      type="tel"
                      class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                      placeholder="(00) 00000-0000"
                      maxlength="15"
                      @input="formatPhone($event, 'phone1')"
                    />
                  </div>
                  <p v-if="form.errors.phone1" class="mt-2 text-sm text-red-600">
                    {{ form.errors.phone1 }}
                  </p>
                </div>

                <div>
                  <label for="contact1" class="block text-sm font-medium text-gray-700">
                    Contato Principal
                  </label>
                  <div class="mt-1">
                    <input
                      id="contact1"
                      v-model="form.contact1"
                      type="text"
                      class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                    />
                  </div>
                  <p v-if="form.errors.contact1" class="mt-2 text-sm text-red-600">
                    {{ form.errors.contact1 }}
                  </p>
                </div>

                <div>
                  <label for="phone2" class="block text-sm font-medium text-gray-700">
                    Telefone Secundário
                  </label>
                  <div class="mt-1">
                    <input
                      id="phone2"
                      v-model="form.phone2"
                      type="tel"
                      class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                      placeholder="(00) 00000-0000"
                      maxlength="15"
                      @input="formatPhone($event, 'phone2')"
                    />
                  </div>
                  <p v-if="form.errors.phone2" class="mt-2 text-sm text-red-600">
                    {{ form.errors.phone2 }}
                  </p>
                </div>

                <div>
                  <label for="contact2" class="block text-sm font-medium text-gray-700">
                    Contato Secundário
                  </label>
                  <div class="mt-1">
                    <input
                      id="contact2"
                      v-model="form.contact2"
                      type="text"
                      class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                    />
                  </div>
                  <p v-if="form.errors.contact2" class="mt-2 text-sm text-red-600">
                    {{ form.errors.contact2 }}
                  </p>
                </div>
              </div>

              <!-- Telefone Geral (legado) -->
              <div class="mt-6">
                <label for="phone" class="block text-sm font-medium text-gray-700">
                  Telefone Geral (opcional)
                </label>
                <div class="mt-1">
                  <input
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                    placeholder="(00) 00000-0000"
                    maxlength="15"
                    @input="formatPhone($event, 'phone')"
                  />
                </div>
                <p v-if="form.errors.phone" class="mt-2 text-sm text-red-600">
                  {{ form.errors.phone }}
                </p>
              </div>
            </div>

            <!-- Usuário Associado -->
            <div v-if="client.user" class="bg-gray-50 rounded-lg p-6">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Usuário Associado</h2>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="user_name" class="block text-sm font-medium text-gray-700">
                    Nome do Usuário
                  </label>
                  <div class="mt-1">
                    <input
                      id="user_name"
                      v-model="form.user_name"
                      type="text"
                      class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                    />
                  </div>
                  <p v-if="form.errors.user_name" class="mt-2 text-sm text-red-600">
                    {{ form.errors.user_name }}
                  </p>
                </div>

                <div>
                  <label for="user_email" class="block text-sm font-medium text-gray-700">
                    E-mail de Acesso
                  </label>
                  <div class="mt-1">
                    <input
                      id="user_email"
                      v-model="form.user_email"
                      type="email"
                      class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                    />
                  </div>
                  <p v-if="form.errors.user_email" class="mt-2 text-sm text-red-600">
                    {{ form.errors.user_email }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Status -->
            <div class="bg-gray-50 rounded-lg p-6">
              <div class="flex items-center">
                <input
                  id="is_active"
                  v-model="form.is_active"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                />
                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                  Cliente ativo
                </label>
              </div>
              <p v-if="form.errors.is_active" class="mt-2 text-sm text-red-600">
                {{ form.errors.is_active }}
              </p>
            </div>

            <!-- Botões -->
            <div class="flex justify-end space-x-3">
              <Link
                :href="route('clients.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                :disabled="form.processing"
              >
                <span v-if="form.processing">Salvando...</span>
                <span v-else>Salvar Alterações</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
