<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineOptions({ layout: AuthLayout })

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  document_number: '',
  phone: '',
})

const formatDocument = (event: Event) => {
  const input = event.target as HTMLInputElement
  let value = input.value.replace(/\D/g, '')
  
  if (value.length <= 11) {
    // CPF: 000.000.000-00
    value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
  } else {
    // CNPJ: 00.000.000/0000-00
    value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5')
  }
  
  input.value = value
  form.document_number = value
}

const formatPhone = (event: Event) => {
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
  form.phone = value
}

const submit = () => {
  form.post(route('client.register.post'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <Head title="Cadastro Cliente" />

  <div class="mx-auto max-w-md">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Criar conta</h1>
      <p class="mt-2 text-sm text-gray-600">
        Preencha os dados abaixo para se cadastrar
      </p>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
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
            autocomplete="name"
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
            autocomplete="email"
          />
        </div>
        <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
          {{ form.errors.email }}
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
          />
        </div>
        <p v-if="form.errors.document_number" class="mt-2 text-sm text-red-600">
          {{ form.errors.document_number }}
        </p>
      </div>

      <div>
        <label for="phone" class="block text-sm font-medium text-gray-700">
          Telefone
        </label>
        <div class="mt-1">
          <input
            id="phone"
            v-model="form.phone"
            type="tel"
            class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
            maxlength="15"
            placeholder="(00) 00000-0000"
            @input="formatPhone"
          />
        </div>
        <p v-if="form.errors.phone" class="mt-2 text-sm text-red-600">
          {{ form.errors.phone }}
        </p>
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">
          Senha *
        </label>
        <div class="mt-1">
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
            required
            autocomplete="new-password"
          />
        </div>
        <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
          {{ form.errors.password }}
        </p>
      </div>

      <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
          Confirmar senha *
        </label>
        <div class="mt-1">
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
            required
            autocomplete="new-password"
          />
        </div>
        <p v-if="form.errors.password_confirmation" class="mt-2 text-sm text-red-600">
          {{ form.errors.password_confirmation }}
        </p>
      </div>

      <div>
        <button
          type="submit"
          class="flex w-full justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
          :disabled="form.processing"
        >
          <span v-if="form.processing">Cadastrando...</span>
          <span v-else>Cadastrar</span>
        </button>
      </div>

      <div class="text-center">
        <p class="text-sm text-gray-600">
          Já tem uma conta?
          <Link
            :href="route('client.login')"
            class="font-medium text-indigo-600 hover:text-indigo-500"
          >
            Faça login
          </Link>
        </p>
      </div>
    </form>

    <div class="mt-6 text-center">
      <Link
        :href="route('login')"
        class="text-sm text-gray-500 hover:text-gray-700"
      >
        ← Voltar para área administrativa
      </Link>
    </div>
  </div>
</template>
