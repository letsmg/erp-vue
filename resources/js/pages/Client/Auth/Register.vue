<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import StoreLayout from '@/Layouts/StoreLayout.vue'
import { onMounted, onUnmounted, ref } from 'vue'
import { Sparkles, Trash2, Eye, EyeOff } from 'lucide-vue-next'
import { fillFormData, clearFormData } from '@/lib/utils'
import { isValidDocument } from '@/lib/validation'

defineOptions({ layout: StoreLayout })

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const documentError = ref('')
const successMessage = ref('')

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  document_number: '',
  phone1: '',
  phone2: '',
  contact1: '',
  contact2: '',
  state_registration: '',
  municipal_registration: '',
  contributor_type: 2, // Default to "Isento" for individual clients (integer)
})

// Event listeners for auto-fill shortcuts
onMounted(() => {
  window.addEventListener('magic-fill', fillTestForm)
  window.addEventListener('magic-clear', clearCurrentForm)
})

onUnmounted(() => {
  window.removeEventListener('magic-fill', fillTestForm)
  window.removeEventListener('magic-clear', clearCurrentForm)
})

const fillTestForm = () => fillFormData(form)
const clearCurrentForm = () => clearFormData(form)

const validateDocument = () => {
  const cleanDocument = form.document_number.replace(/\D/g, '')
  if (cleanDocument.length > 0) {
    const validation = isValidDocument(form.document_number)
    if (!validation.valid) {
      documentError.value = validation.message
    } else {
      documentError.value = ''
    }
  } else {
    documentError.value = ''
  }
}

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
  validateDocument()
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
  form.phone1 = value
}

const submit = () => {
  console.log('Submitting registration form...', form.data())
  form.post(route('client.register.post'), {
    onSuccess: () => {
      console.log('Registration successful')
      successMessage.value = 'Cadastro realizado! Um link de confirmação foi enviado para seu e-mail. Clique no link para ativar sua conta e poder fazer login.'
      form.reset('password', 'password_confirmation')
    },
    onError: (errors) => {
      console.log('Registration failed with errors:', errors)
      successMessage.value = ''
      // Errors are automatically preserved by Inertia
    }
  })
}
</script>

<template>
  <Head title="Cadastro Cliente">
    <meta name="robots" content="follow" />
  </Head>

  <div class="min-h-[60vh] flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-xl">
      <div class="bg-white py-8 px-4 shadow-2xl rounded-3xl sm:px-10 border border-slate-100">
        <div class="text-center mb-8">
          <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tighter">Criar conta</h1>
          <p class="mt-2 text-sm font-bold text-slate-500 uppercase tracking-widest">
            Preencha os dados abaixo para se cadastrar
          </p>
        </div>

        <div class="flex gap-2 mb-6 justify-center">
          <button type="button" @click="fillTestForm" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-xs font-black uppercase rounded-xl hover:bg-indigo-700 transition">
            <Sparkles class="w-4 h-4" />
            <span>Preencher</span>
            <span class="text-[10px] opacity-70">ALT+1</span>
          </button>
          <button type="button" @click="clearCurrentForm" class="flex items-center gap-2 px-4 py-2 bg-gray-600 text-white text-xs font-black uppercase rounded-xl hover:bg-gray-700 transition">
            <Trash2 class="w-4 h-4" />
            <span>Limpar</span>
            <span class="text-[10px] opacity-70">ALT+2</span>
          </button>
        </div>

        <!-- General Error Display -->
        <div v-if="Object.keys(form.errors).length > 0" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
          <p class="text-xs font-bold text-red-600 uppercase mb-2">Erros encontrados:</p>
          <ul class="text-xs text-red-600 list-disc list-inside space-y-1">
            <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
          </ul>
        </div>

        <!-- Success Message Display -->
        <div v-if="successMessage" class="mb-6 p-6 bg-emerald-600 border-2 border-emerald-500 rounded-2xl shadow-lg shadow-emerald-500/30">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <p class="text-sm font-bold text-white uppercase tracking-widest">Sucesso!</p>
          </div>
          <p class="text-sm text-white/95 leading-relaxed">{{ successMessage }}</p>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="md:col-span-2">
            <label for="name" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              Nome completo *
            </label>
            <div class="mt-1">
              <input
                id="name"
                v-model="form.name"
                type="text"
                class="block w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm"
                required
                autocomplete="name"
                placeholder="Seu Nome Completo"
              />
            </div>
            <p v-if="form.errors.name" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ form.errors.name }}
            </p>
          </div>

          <div>
            <label for="email" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              E-mail *
            </label>
            <div class="mt-1">
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="block w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm"
                required
                autocomplete="email"
                placeholder="seu@email.com"
              />
            </div>
            <p v-if="form.errors.email" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ form.errors.email }}
            </p>
          </div>

          <div>
            <label for="document_number" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              CPF/CNPJ *
            </label>
            <div class="mt-1">
              <input
                id="document_number"
                v-model="form.document_number"
                type="text"
                class="block w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm"
                required
                maxlength="18"
                placeholder="000.000.000-00"
                @input="formatDocument"
              />
            </div>
            <p v-if="form.errors.document_number" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ form.errors.document_number }}
            </p>
            <p v-if="documentError" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ documentError }}
            </p>
          </div>

          <div>
            <label for="phone1" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              Telefone Principal *
            </label>
            <div class="mt-1">
              <input
                id="phone1"
                v-model="form.phone1"
                type="tel"
                class="block w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm"
                maxlength="15"
                placeholder="(00) 00000-0000"
                @input="formatPhone"
                required
              />
            </div>
            <p v-if="form.errors.phone1" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ form.errors.phone1 }}
            </p>
          </div>

          <div>
            <label for="contact1" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              Nome do Contato Principal
            </label>
            <div class="mt-1">
              <input
                id="contact1"
                v-model="form.contact1"
                type="text"
                class="block w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm"
                placeholder="Nome completo"
              />
            </div>
            <p v-if="form.errors.contact1" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ form.errors.contact1 }}
            </p>
          </div>

          <div>
            <label for="phone2" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              Telefone Secundário
            </label>
            <div class="mt-1">
              <input
                id="phone2"
                v-model="form.phone2"
                type="tel"
                class="block w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm"
                maxlength="15"
                placeholder="(00) 00000-0000"
                @input="formatPhone"
              />
            </div>
            <p v-if="form.errors.phone2" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ form.errors.phone2 }}
            </p>
          </div>

          <div>
            <label for="contact2" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              Nome do Contato Secundário
            </label>
            <div class="mt-1">
              <input
                id="contact2"
                v-model="form.contact2"
                type="text"
                class="block w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm"
                placeholder="Nome completo"
              />
            </div>
            <p v-if="form.errors.contact2" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ form.errors.contact2 }}
            </p>
          </div>

          <div class="md:col-span-1">
            <label for="password" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              Senha *
            </label>
            <div class="mt-1 relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="block w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm pr-12"
                required
                autocomplete="new-password"
                placeholder="••••••••"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-primary transition-colors"
              >
                <component :is="showPassword ? EyeOff : Eye" class="w-5 h-5" />
              </button>
            </div>
            <p v-if="form.errors.password" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ form.errors.password }}
            </p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              Confirmar senha *
            </label>
            <div class="mt-1 relative">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showPasswordConfirmation ? 'text' : 'password'"
                class="block w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm pr-12"
                required
                autocomplete="new-password"
                placeholder="••••••••"
              />
              <button
                type="button"
                @click="showPasswordConfirmation = !showPasswordConfirmation"
                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-primary transition-colors"
              >
                <component :is="showPasswordConfirmation ? EyeOff : Eye" class="w-5 h-5" />
              </button>
            </div>
            <p v-if="form.errors.password_confirmation" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ form.errors.password_confirmation }}
            </p>
          </div>

          <div class="md:col-span-2 mt-4">
            <button
              type="submit"
              class="btn-primary w-full py-4 rounded-2xl shadow-lg shadow-primary/20 text-sm uppercase tracking-widest font-black"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
            >
              Criar minha conta
            </button>
          </div>
        </form>

        <div class="mt-8 pt-8 border-t border-slate-100 text-center">
          <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">
            Já tem uma conta?
            <Link
              :href="route('client.login')"
              class="ml-1 font-black text-primary hover:text-primary-hover transition"
            >
              Fazer login
            </Link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
