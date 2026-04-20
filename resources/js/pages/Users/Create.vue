<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { Save, ArrowLeft, Shield, Mail, Lock, User, Eye, EyeOff, XCircle, Sparkles, X } from 'lucide-vue-next';
import { fillFormData, clearFormData } from '@/lib/utils';

const showPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    access_level: 0,
    is_active: true,
});

const filler = () => fillFormData(form);
const clearer = () => clearFormData(form);

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => form.reset(),
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

onMounted(() => {
    window.addEventListener('magic-fill', filler);
    window.addEventListener('magic-clear', clearer);
});

onUnmounted(() => {
    window.removeEventListener('magic-fill', filler);
    window.removeEventListener('magic-clear', clearer);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Novo Usuário" />

        <div class="max-w-3xl mx-auto pb-12">
            <div class="mb-6">
                <Link :href="route('users.index')" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center transition">
                    <ArrowLeft class="w-4 h-4 mr-1" /> Voltar
                </Link>
                <h2 class="text-3xl font-black text-gray-800 tracking-tighter uppercase">Cadastrar Novo Usuário</h2>
            </div>

            <!-- Atalhos -->
            <div class="mb-6 flex justify-center">
                <div class="inline-flex items-center gap-4 bg-slate-50 px-6 py-3 rounded-2xl border border-gray-200 shadow-sm">
                    <div class="flex items-center gap-2">
                        <Sparkles class="w-4 h-4 text-indigo-500" />
                        <span class="text-[11px] font-bold text-indigo-600">ALT+1</span>
                        <span class="text-[11px] text-gray-600">Popular</span>
                    </div>
                    <div class="w-px h-4 bg-gray-300"></div>
                    <div class="flex items-center gap-2">
                        <X class="w-4 h-4 text-red-500" />
                        <span class="text-[11px] font-bold text-red-600">ALT+2</span>
                        <span class="text-[11px] text-gray-600">Limpar</span>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="mb-6 flex justify-center gap-4">
                <button type="button" @click="filler" class="bg-slate-600 hover:bg-slate-700 active:scale-95 active:shadow-lg text-white px-6 py-3 rounded-xl font-bold text-sm uppercase tracking-wider shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2 transform cursor-pointer">
                    <Sparkles class="w-4 h-4" />
                    Popular Formulário
                </button>
                <button type="button" @click="clearer" class="bg-slate-600 hover:bg-slate-700 active:scale-95 active:shadow-lg text-white px-6 py-3 rounded-xl font-bold text-sm uppercase tracking-wider shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2 transform cursor-pointer">
                    <X class="w-4 h-4" />
                    Limpar Formulário
                </button>
            </div>

            <Transition
                enter-active-class="transform ease-out duration-300 transition"
                enter-from-class="-translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
            >
                <div v-if="form.hasErrors" class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm">
                    <div class="flex items-center mb-2">
                        <XCircle class="w-5 h-5 text-red-500 mr-2" />
                        <span class="text-sm font-black text-red-800 uppercase tracking-tighter">Ops! Verifique os dados:</span>
                    </div>
                    <ul class="list-disc list-inside">
                        <li v-for="(error, field) in form.errors" :key="field" class="text-xs text-red-600 font-bold uppercase tracking-tight">
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </Transition>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white shadow-sm border border-gray-100 rounded-xl p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">Nome Completo</label>
                            <div class="relative flex items-center">
                                <User class="absolute left-3 w-4 h-4 text-gray-400" />
                                <input 
                                    v-model="form.name" 
                                    type="text" 
                                    :class="{'border-red-500 bg-red-50': form.errors.name}"
                                    class="w-full pl-10 rounded-lg border-gray-200 focus:ring-indigo-500 transition" 
                                    placeholder="Ex: João Silva" 
                                    required 
                                />
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">E-mail</label>
                            <div class="relative flex items-center">
                                <Mail class="absolute left-3 w-4 h-4 text-gray-400" />
                                <input 
                                    v-model="form.email" 
                                    type="email" 
                                    :class="{'border-red-500 bg-red-50': form.errors.email}"
                                    class="w-full pl-10 rounded-lg border-gray-200 focus:ring-indigo-500" 
                                    placeholder="joao@empresa.com"
                                    required 
                                />
                            </div>
                        </div>

                        <div class="relative">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">Senha</label>
                            <div class="relative flex items-center">
                                <Lock class="absolute left-3 w-4 h-4 text-gray-400" />
                                <input 
                                    :type="showPassword ? 'text' : 'password'" 
                                    v-model="form.password" 
                                    :class="{'border-red-500 bg-red-50': form.errors.password}"
                                    class="w-full pl-10 pr-10 rounded-lg border-gray-200 focus:ring-indigo-500" 
                                    required 
                                />
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 text-gray-400 hover:text-indigo-600 transition">
                                    <Eye v-if="!showPassword" class="w-4 h-4" />
                                    <EyeOff v-else class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">Confirmar Senha</label>
                            <div class="relative flex items-center">
                                <Lock class="absolute left-3 w-4 h-4 text-gray-400" />
                                <input 
                                    :type="showPassword ? 'text' : 'password'" 
                                    v-model="form.password_confirmation" 
                                    class="w-full pl-10 rounded-lg border-gray-200 focus:ring-indigo-500" 
                                    required 
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">Nível de Acesso</label>
                            <div class="relative flex items-center">
                                <Shield class="absolute left-3 w-4 h-4 text-gray-400" />
                                <select 
                                    v-model="form.access_level" 
                                    :class="{'border-red-500 bg-red-50': form.errors.access_level}"
                                    class="w-full pl-10 rounded-lg border-gray-200 text-sm focus:ring-indigo-500"
                                >
                                    <option :value="0">Usuário Padrão</option>
                                    <option :value="1">Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 items-center">
                    <span v-if="form.recentlySuccessful" class="text-sm text-green-600 font-bold animate-pulse">
                        Salvo com sucesso!
                    </span>
                    
                    <button 
                        type="submit" 
                        :disabled="form.processing" 
                        class="bg-emerald-600 hover:bg-emerald-700 active:scale-95 active:shadow-lg text-white px-10 py-3 rounded-xl font-bold flex items-center gap-2 transition-all duration-200 shadow-lg shadow-emerald-500/20 hover:shadow-xl disabled:opacity-50 disabled:scale-100 transform cursor-pointer"
                    >
                        <Save class="w-5 h-5" /> {{ form.processing ? 'Salvando...' : 'Salvar Usuário' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>