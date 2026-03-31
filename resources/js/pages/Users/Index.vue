<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { UserPlus, UserCog, UserMinus, Key, Trash2, UserCheck } from 'lucide-vue-next';

const props = defineProps({ 
    users: Array,
    auth: Object 
});

const handleToggleStatus = (user) => {
    const acao = user.is_active ? 'desativar' : 'ativar';
    if (confirm(`Deseja realmente ${acao} o usuário ${user.name}?`)) {
        router.patch(route('users.toggle', user.id));
    }
};

const handleResetPassword = (user) => {
    if (confirm(`Resetar senha de ${user.name} para "Mudar@123"?`)) {
        router.patch(route('users.reset', user.id));
    }
};

const handleDelete = (user) => {
    if (confirm(`EXCLUIR PERMANENTEMENTE o usuário ${user.name}? Esta ação é irreversível.`)) {
        router.delete(route('users.destroy', user.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Gestão de Usuários" />

        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tighter uppercase">Usuários</h2>
                <p class="mt-1 text-sm text-gray-500">Lista de colaboradores com acesso ao ERP Vue Laravel.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <Link v-if="auth.user.access_level === 1" :href="route('users.create')" class="bg-indigo-600 hover:bg-indigo-700 active:scale-95 active:shadow-lg text-white px-6 py-3 rounded-2xl flex items-center gap-2 transition-all duration-200 shadow-lg shadow-indigo-500/20 hover:shadow-xl font-bold uppercase text-xs tracking-widest cursor-pointer">
                    <UserPlus class="w-4 h-4 mr-2" />
                    Novo Usuário
                </Link>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden border border-gray-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Usuário</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Nível</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-gray-400 uppercase tracking-widest">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center font-bold text-white shadow-sm text-sm">
                                        {{ user.name[0] }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ user.name }}</div>
                                        <div class="text-xs text-gray-400">{{ user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="user.access_level === 1 ? 'bg-purple-50 text-purple-700 border-purple-100' : 'bg-blue-50 text-blue-700 border-blue-100'" class="px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border">
                                    {{ user.access_level === 1 ? 'Admin' : 'Padrão' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div :class="user.is_active ? 'bg-green-500' : 'bg-red-500'" class="h-1.5 w-1.5 rounded-full mr-2"></div>
                                    <span class="text-sm font-medium" :class="user.is_active ? 'text-green-700' : 'text-red-700'">
                                        {{ user.is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-3">                                    
                                    <Link v-if="auth.user.access_level === 1 || user.id == auth.user.id" :href="route('users.edit', user.id)" class="text-indigo-600 hover:text-indigo-900 flex items-center transition cursor-pointer group" title="Editar Usuário">
                                        <UserCog class="w-4 h-4 mr-1 group-hover:scale-110 transition" />
                                        <span>Editar</span>
                                    </Link>

                                    <template v-if="user.id !== auth.user.id">
                                        <button @click="handleToggleStatus(user)" 
                                            :class="user.is_active ? 'text-amber-600 hover:text-amber-800' : 'text-emerald-600 hover:text-emerald-800'" 
                                            class="font-bold flex items-center cursor-pointer transition group">
                                            <UserMinus v-if="user.is_active" class="w-4 h-4 mr-1 group-hover:rotate-12 transition" />
                                            <UserCheck v-else class="w-4 h-4 mr-1 group-hover:scale-110 transition" />
                                            {{ user.is_active ? 'Suspensão' : 'Ativar' }}
                                        </button>

                                        <button @click="handleResetPassword(user)" class="text-gray-400 hover:text-amber-600 flex items-center cursor-pointer transition group" title="Resetar Senha">
                                            <Key class="w-4 h-4 mr-1 group-hover:rotate-45 transition" />
                                            <span>Reset</span>
                                        </button>

                                        <button v-if="auth.user.id == 0" @click="handleDelete(user)" class="text-gray-300 hover:text-red-600 flex items-center cursor-pointer transition group" title="Excluir Usuário">
                                            <Trash2 class="w-4 h-4 group-hover:scale-110 transition" />
                                        </button>
                                    </template>
                                    
                                    <span v-else class="text-[10px] font-black text-gray-300 uppercase tracking-tighter bg-gray-50 px-2 py-1 rounded">Você</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>