<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { 
    UserPlus, Search, Filter, MoreHorizontal, 
    UserCheck, UserMinus, UserCog, Trash2, 
    Mail, Phone, ShieldCheck, ShieldAlert
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({ 
    clients: Object,
    filters: Object,
    auth: Object 
});

const search = ref(props.filters.search || '');
const showOnlyActive = ref(props.filters.active == 1);
const showOnlyBlocked = ref(props.filters.blocked == 1);

const updateFilters = debounce(() => {
    router.get(route('clients.index'), 
        { 
            search: search.value, 
            active: showOnlyActive.value ? 1 : 0,
            blocked: showOnlyBlocked.value ? 1 : 0
        }, 
        { 
            preserveState: true, 
            replace: true,
            preserveScroll: true 
        }
    );
}, 300);

watch(search, (value) => {
    if (value.length >= 4 || value.length === 0) {
        updateFilters();
    }
});

watch(showOnlyActive, (value) => {
    if (value) showOnlyBlocked.value = false;
    updateFilters();
});

watch(showOnlyBlocked, (value) => {
    if (value) showOnlyActive.value = false;
    updateFilters();
});

const handleToggleStatus = (client) => {
    const acao = client.is_active ? 'bloquear' : 'ativar';
    if (confirm(`Deseja realmente ${acao} o cliente ${client.name}?`)) {
        router.get(route('clients.toggle.status', client.id));
    }
};

const handleDelete = (client) => {
    if (confirm(`EXCLUIR PERMANENTEMENTE o cliente ${client.name}? Esta ação é irreversível e só é permitida se não houver compras nos últimos 5 anos.`)) {
        router.delete(route('clients.destroy', client.id));
    }
};

const getStatusColor = (isActive) => isActive ? 'text-emerald-700 bg-emerald-50 border-emerald-100' : 'text-rose-700 bg-rose-50 border-rose-100';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Gestão de Clientes" />

        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase italic">Clientes</h2>
                <p class="mt-1 text-sm text-slate-500 font-medium">Gerencie sua base de consumidores e acessos ao portal.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <Link :href="route('clients.create')" class="btn-primary flex items-center gap-2">
                    <UserPlus class="w-4 h-4" />
                    Novo Cliente
                </Link>
            </div>
        </div>

        <!-- Filtros Rápidos -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4 items-center">
            <div class="relative flex-1 w-full">
                <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input 
                    v-model="search"
                    type="text" 
                    placeholder="Buscar por nome, e-mail ou documento..."
                    class="w-full bg-white border-slate-200 rounded-2xl pl-12 pr-4 py-3 text-sm focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm"
                />
            </div>

            <!-- Checkboxes de Status -->
            <div class="flex items-center gap-6 px-4 py-2 bg-white rounded-2xl border border-slate-100 shadow-sm">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" v-model="showOnlyActive" class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary transition-all">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 group-hover:text-primary transition-colors">Ativos</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" v-model="showOnlyBlocked" class="w-4 h-4 rounded border-slate-300 text-rose-500 focus:ring-rose-500 transition-all">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 group-hover:text-rose-500 transition-colors">Bloqueados</span>
                </label>
            </div>

            <button class="bg-white border border-slate-200 text-slate-600 px-6 py-3 rounded-2xl flex items-center gap-2 font-bold uppercase text-[10px] tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                <Filter class="w-4 h-4" />
                Filtros Avançados
            </button>
        </div>

        <!-- Tabela -->
        <div class="bg-white shadow-2xl shadow-slate-200/50 sm:rounded-[2rem] overflow-hidden border border-slate-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Cliente / Documento</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Contato</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                            <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="client in clients.data" :key="client.id" class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-12 w-12 flex-shrink-0 bg-slate-900 rounded-2xl flex items-center justify-center font-black text-white shadow-lg group-hover:scale-110 transition-transform italic text-lg">
                                        {{ client.name[0] }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-black text-slate-900 uppercase tracking-tighter">{{ client.name }}</div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1 mt-0.5">
                                            <ShieldCheck v-if="client.document_type === 'CNPJ'" class="w-3 h-3 text-primary" />
                                            <UserCog v-else class="w-3 h-3 text-slate-400" />
                                            {{ client.formatted_document }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2 text-xs font-bold text-slate-600">
                                        <Mail class="w-3 h-3 text-slate-400" />
                                        {{ client.user?.email || 'N/A' }}
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-tight">
                                        <Phone class="w-3 h-3" />
                                        {{ client.phone1 || 'Sem telefone' }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <span :class="getStatusColor(client.is_active)" class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border shadow-sm flex items-center w-fit gap-2">
                                    <div :class="client.is_active ? 'bg-emerald-500' : 'bg-rose-500'" class="h-1.5 w-1.5 rounded-full animate-pulse"></div>
                                    {{ client.is_active ? 'Ativo' : 'Bloqueado' }}
                                </span>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('clients.edit', client.id)" class="p-2.5 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-all" title="Editar">
                                        <UserCog class="w-5 h-5" />
                                    </Link>
                                    
                                    <button @click="handleToggleStatus(client)" class="p-2.5 rounded-xl transition-all" 
                                        :class="client.is_active ? 'text-slate-400 hover:text-rose-600 hover:bg-rose-50' : 'text-slate-400 hover:text-emerald-600 hover:bg-emerald-50'"
                                        :title="client.is_active ? 'Bloquear' : 'Ativar'">
                                        <UserMinus v-if="client.is_active" class="w-5 h-5" />
                                        <UserCheck v-else class="w-5 h-5" />
                                    </button>

                                    <button v-if="auth.user.access_level === 1" @click="handleDelete(client)" 
                                        class="p-2.5 text-slate-300 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all" title="Excluir">
                                        <Trash2 class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação Simples (Inertia) -->
            <div v-if="clients.links.length > 3" class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    Mostrando {{ clients.from }} até {{ clients.to }} de {{ clients.total }} clientes
                </div>
                <div class="flex gap-2">
                    <Component 
                        :is="link.url ? Link : 'span'"
                        v-for="(link, k) in clients.links" 
                        :key="k"
                        :href="link.url"
                        v-html="link.label"
                        class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border shadow-sm"
                        :class="[
                            link.active ? 'bg-primary text-white border-primary shadow-primary/20' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50',
                            !link.url ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
