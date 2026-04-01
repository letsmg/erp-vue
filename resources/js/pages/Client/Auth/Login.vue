<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineOptions({ layout: AuthLayout })

const props = defineProps<{
  status?: string
  userIp?: string
}>()

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post(route('client.login.post'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Login Cliente" />

  <div class="mx-auto max-w-sm">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Bem-vindo!</h1>
      <p class="mt-2 text-sm text-gray-600">
        Faça login para acessar sua conta
      </p>
    </div>

    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
      {{ status }}
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
        <label for="password" class="block text-sm font-medium text-gray-700">
          Senha
        </label>
        <div class="mt-1">
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
            required
            autocomplete="current-password"
          />
        </div>
        <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
          {{ form.errors.password }}
        </p>
      </div>

      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <input
            id="remember"
            v-model="form.remember"
            type="checkbox"
            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
          />
          <label for="remember" class="ml-2 block text-sm text-gray-900">
            Lembrar de mim
          </label>
        </div>

        <div class="text-sm">
          <Link
            :href="route('client.forgot.password')"
            class="font-medium text-indigo-600 hover:text-indigo-500"
          >
            Esqueceu sua senha?
          </Link>
        </div>
      </div>

      <div>
        <button
          type="submit"
          class="flex w-full justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
          :disabled="form.processing"
        >
          <span v-if="form.processing">Entrando...</span>
          <span v-else>Entrar</span>
        </button>
      </div>

      <div class="text-center">
        <p class="text-sm text-gray-600">
          Ainda não tem uma conta?
          <Link
            :href="route('client.register')"
            class="font-medium text-indigo-600 hover:text-indigo-500"
          >
            Cadastre-se gratuitamente
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
