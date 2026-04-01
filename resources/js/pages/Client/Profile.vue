<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'

defineOptions({ layout: AuthenticatedLayout })

const page = usePage()
const user = page.props.auth.user
const client = ref<any>(null)
const loading = ref(true)
const showPasswordForm = ref(false)

// Carregar dados do cliente
const loadClientData = async () => {
  try {
    const response = await fetch(route('client.profile'))
    const data = await response.json()
    if (data.success) {
      client.value = data.data
    }
  } catch (error) {
    console.error('Erro ao carregar dados:', error)
  } finally {
    loading.value = false
  }
}

loadClientData()

const form = useForm({
  name: '',
  email: '',
  phone1: '',
  contact1: '',
  phone2: '',
  contact2: '',
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const passwordForm = useForm({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

// Preencher formulário com dados do cliente
const fillForm = () => {
  if (client.value) {
    form.name = client.value.name
    form.email = client.value.user?.email || ''
    form.phone1 = client.value.phone1 || ''
    form.contact1 = client.value.contact1 || ''
    form.phone2 = client.value.phone2 || ''
    form.contact2 = client.value.contact2 || ''
  }
}

// Observar mudanças nos dados do cliente
watch(client, fillForm, { immediate: true })

const formatPhone = (event: Event, field: string) => {
  const input = event.target as HTMLInputElement
  let value = input.value.replace(/\D/g, '')
  
  if (value.length <= 10) {
    value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3')
  } else {
    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3')
  }
  
  input.value = value
  if (field === 'phone1') {
    form.phone1 = value
  } else if (field === 'phone2') {
    form.phone2 = value
  }
}

const submitProfile = () => {
  form.put(route('client.profile.update'), {
    onSuccess: () => {
      loadClientData()
    },
  })
}

const submitPassword = () => {
  passwordForm.put(route('client.profile.update'), {
    onSuccess: () => {
      passwordForm.reset()
      showPasswordForm.value = false
    },
  })
}
</script>

<template>
  <Head title="Meus Dados" />

  <div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Meus Dados</h1>
            <p class="mt-1 text-sm text-gray-600">
              Atualize suas informações pessoais e de contato
            </p>
          </div>

          <div v-if="loading" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-sm text-gray-600">Carregando...</p>
          </div>

          <div v-else-if="client" class="space-y-8">
            <!-- Dados Pessoais -->
            <div class="bg-gray-50 rounded-lg p-6">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Dados Pessoais</h2>
              
              <form @submit.prevent="submitProfile" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                      Nome completo *
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
                    <label for="email" class="block text-sm font-medium text-gray-700">
                      E-mail *
                    </label>
                    <div class="mt-1">
                      <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                        required
                      />
                    </div>
                    <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                      {{ form.errors.email }}
                    </p>
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ client.document_type }}
                    </label>
                    <div class="mt-1">
                      <input
                        type="text"
                        :value="client.document_number"
                        class="block w-full appearance-none rounded-md border border-gray-200 bg-gray-100 px-3 py-2 text-gray-600 sm:text-sm"
                        readonly
                      />
                    </div>
                  </div>

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
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                <div class="flex justify-end">
                  <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                    :disabled="form.processing"
                  >
                    <span v-if="form.processing">Salvando...</span>
                    <span v-else>Salvar Dados</span>
                  </button>
                </div>
              </form>
            </div>

            <!-- Alterar Senha -->
            <div class="bg-gray-50 rounded-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-medium text-gray-900">Alterar Senha</h2>
                <button
                  type="button"
                  @click="showPasswordForm = !showPasswordForm"
                  class="text-sm text-indigo-600 hover:text-indigo-500"
                >
                  {{ showPasswordForm ? 'Cancelar' : 'Alterar senha' }}
                </button>
              </div>

              <form v-if="showPasswordForm" @submit.prevent="submitPassword" class="space-y-6">
                <div>
                  <label for="current_password" class="block text-sm font-medium text-gray-700">
                    Senha atual *
                  </label>
                  <div class="mt-1">
                    <input
                      id="current_password"
                      v-model="passwordForm.current_password"
                      type="password"
                      class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                      required
                    />
                  </div>
                  <p v-if="passwordForm.errors.current_password" class="mt-2 text-sm text-red-600">
                    {{ passwordForm.errors.current_password }}
                  </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700">
                      Nova senha *
                    </label>
                    <div class="mt-1">
                      <input
                        id="new_password"
                        v-model="passwordForm.new_password"
                        type="password"
                        class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                        required
                      />
                    </div>
                    <p v-if="passwordForm.errors.new_password" class="mt-2 text-sm text-red-600">
                      {{ passwordForm.errors.new_password }}
                    </p>
                  </div>

                  <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">
                      Confirmar nova senha *
                    </label>
                    <div class="mt-1">
                      <input
                        id="new_password_confirmation"
                        v-model="passwordForm.new_password_confirmation"
                        type="password"
                        class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                        required
                      />
                    </div>
                    <p v-if="passwordForm.errors.new_password_confirmation" class="mt-2 text-sm text-red-600">
                      {{ passwordForm.errors.new_password_confirmation }}
                    </p>
                  </div>
                </div>

                <div class="flex justify-end">
                  <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                    :disabled="passwordForm.processing"
                  >
                    <span v-if="passwordForm.processing">Alterando...</span>
                    <span v-else>Alterar Senha</span>
                  </button>
                </div>
              </form>
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
                          Principal
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
        </div>
      </div>
    </div>
  </div>
</template>
