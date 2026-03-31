<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Save, ArrowLeft, Shield, Mail, Lock, User, Eye, EyeOff, UserCheck, XCircle } from 'lucide-vue-next';

const props = defineProps({ user: Object });
const showPassword = ref(false);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    access_level: props.user.access_level,
    is_active: props.user.is_active == 1, // Garante que seja booleano
});

const submit = () => {
    form.put(route('users.update', props.user.id), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Editar Usuário" />

        <div class="max-w-3xl mx-auto pb-12">
            <div class="mb-6 flex items-center justify-between">
                <Link :href="route('users.index')" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center transition">
                    <ArrowLeft class="w-4 h-4 mr-1" /> Voltar
                </Link>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest bg-gray-100 px-3 py-1 rounded-full">
                    ID: #{{ user.id }}
                </span>
            </div>

            <Transition
                enter-active-class="transform ease-out duration-300 transition"
                enter-from-class="-translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
            >
                <div v-if="form.hasErrors" class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm">
                    <div class="flex items-center mb-2">
                        <XCircle class="w-5 h-5 text-red-500 mr-2" />
                        <span class="text-sm font-black text-red-800 uppercase tracking-tighter">Erro ao atualizar:</span>
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
                                />
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <div class="p-4 bg-amber-50 rounded-lg border border-amber-100 flex items-start gap-3">
                                <Lock class="w-4 h-4 text-amber-500 mt-0.5" />
                                <p class="text-xs text-amber-700 font-medium">
                                    Deixe os campos de senha em branco caso não deseje alterá-la. O sistema manterá a senha atual.
                                </p>
                            </div>
                        </div>

                        <div class="relative">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">Nova Senha</label>
                            <div class="relative flex items-center">
                                <Lock class="absolute left-3 w-4 h-4 text-gray-400" />
                                <input 
                                    :type="showPassword ? 'text' : 'password'" 
                                    v-model="form.password" 
                                    :class="{'border-red-500 bg-red-50': form.errors.password}"
                                    class="w-full pl-10 pr-10 rounded-lg border-gray-200 focus:ring-indigo-500" 
                                />
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 text-gray-400 hover:text-indigo-600 transition">
                                    <Eye v-if="!showPassword" class="w-4 h-4" />
                                    <EyeOff v-else class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">Confirmar Nova Senha</label>
                            <div class="relative flex items-center">
                                <Lock class="absolute left-3 w-4 h-4 text-gray-400" />
                                <input 
                                    :type="showPassword ? 'text' : 'password'" 
                                    v-model="form.password_confirmation" 
                                    class="w-full pl-10 rounded-lg border-gray-200 focus:ring-indigo-500" 
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">Nível de Acesso</label>
                            <div class="relative flex items-center">
                                <Shield class="absolute left-3 w-4 h-4 text-gray-400" />
                                <select v-model="form.access_level" class="w-full pl-10 rounded-lg border-gray-200 text-sm focus:ring-indigo-500">
                                    <option :value="0">Usuário Padrão</option>
                                    <option :value="1">Administrador</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 tracking-wider">Status da Conta</label>
                            <div class="relative flex items-center">
                                <UserCheck class="absolute left-3 w-4 h-4 text-gray-400" />
                                <select v-model="form.is_active" class="w-full pl-10 rounded-lg border-gray-200 text-sm focus:ring-indigo-500">
                                    <option :value="true">Ativo</option>
                                    <option :value="false">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 items-center">
                    <span v-if="form.recentlySuccessful" class="text-sm text-green-600 font-bold animate-pulse">
                        Dados atualizados com sucesso!
                    </span>

                    <button 
                        type="submit" 
                        :disabled="form.processing" 
                        class="bg-emerald-600 hover:bg-emerald-700 active:scale-95 active:shadow-lg text-white px-10 py-3 rounded-xl font-bold flex items-center gap-2 transition-all duration-200 shadow-lg shadow-emerald-500/20 hover:shadow-xl disabled:opacity-50 disabled:scale-100 transform cursor-pointer"
                    >
                        <Save class="w-5 h-5" /> {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>