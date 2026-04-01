<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineOptions({ layout: AuthLayout })

const form = useForm({
  email: '',
})

const submit = () => {
  form.post(route('client.forgot.password.post'))
}
</script>

<template>
  <Head title="Recuperar Senha" />

  <div class="mx-auto max-w-sm">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Esqueceu a senha?</h1>
      <p class="mt-2 text-sm text-gray-600">
        Digite seu e-mail e enviaremos um link para redefinir sua senha
      </p>
    </div>

    <div v-if="form.recentlySuccessful" class="mb-4 rounded-md bg-green-50 p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm font-medium text-green-800">
            Link de redefinição enviado com sucesso!
          </p>
        </div>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">
          E-mail
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
        <button
          type="submit"
          class="flex w-full justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
          :disabled="form.processing"
        >
          <span v-if="form.processing">Enviando...</span>
          <span v-else>Enviar link de redefinição</span>
        </button>
      </div>

      <div class="text-center space-y-2">
        <div>
          <Link
            :href="route('client.login')"
            class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
          >
            ← Voltar para o login
          </Link>
        </div>
        <div>
          <Link
            :href="route('client.register')"
            class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
          >
            Criar nova conta
          </Link>
        </div>
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
