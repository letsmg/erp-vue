<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    PackagePlus, Edit, Trash2, Globe, PackageSearch, 
    Eye, EyeOff, Lock, Search, Star 
} from 'lucide-vue-next';
import { Link, router, Head, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import debounce from 'lodash/debounce';

const page = usePage();
const user = page.props.auth.user;

const props = defineProps({ 
    products: Object, // Objeto de paginação do Laravel
    filters: Object   // Filtros vindos do Controller (ex: filters.search, filters.blocked)
});

// Inicializamos as refs com os valores que vêm do servidor
const search = ref(props.filters.search || '');
const showOnlyBlocked = ref(props.filters.blocked == 1);

// 🔄 Função única para atualizar os filtros no servidor
const updateFilters = debounce(() => {
    router.get(route('products.index'), 
        { 
            search: search.value, 
            blocked: showOnlyBlocked.value ? 1 : 0 
        }, 
        { 
            preserveState: true, 
            replace: true,
            preserveScroll: true 
        }
    );
}, 300);

// 🔍 Monitora a busca (respeitando a trava de 3 caracteres)
watch(search, (value) => {
    if (value.length > 2 || value.length === 0) {
        updateFilters();
    }
});

// 🔒 Monitora o checkbox de bloqueados
watch(showOnlyBlocked, () => {
    updateFilters();
});

// Agora os produtos filtrados vêm direto da prop, já que o servidor faz o trabalho pesado
const filteredProducts = computed(() => {
    const products = props.products.data || [];
    // Ordena produtos alfabeticamente pelo description (nome)
    return products.sort((a, b) => a.description.localeCompare(b.description));
});

const formatCurrency = (value) => {
    return new Number(value || 0).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    });
};

const toggleFeatured = (id) => {
    router.patch(route('products.toggle-featured', id), {}, {
        preserveScroll: true,
    });
};

const destroy = (id) => {
    if (confirm('Deseja realmente excluir este produto?')) {
        router.delete(route('products.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Estoque de Produtos" />

        <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tighter uppercase">Gerenciamento de Estoque</h2>
                    <p class="text-gray-500 text-sm font-medium">Visualize e gerencie os produtos do seu ERP.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                    <div class="relative flex-1 md:w-64 bg-white rounded-2xl">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input 
                            v-model="search"
                            type="text" 
                            placeholder="Buscar produto, marca..."
                            class="w-full pl-10 pr-4 py-3 rounded-2xl border-gray-100 focus:ring-black focus:border-black text-sm transition-all shadow-sm"
                        >
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer group bg-white border border-red-100 px-4 py-3 rounded-2xl hover:bg-red-50 transition-all shadow-sm">
                        <input 
                            type="checkbox" 
                            v-model="showOnlyBlocked"
                            class="rounded border-gray-300 text-red-600 focus:ring-red-500 w-4 h-4"
                        >
                        <div class="flex items-center gap-1.5">
                            <Lock class="w-3.5 h-3.5 text-red-500" />
                            <span class="text-xs font-bold uppercase text-red-600 tracking-tight">Bloqueados</span>
                        </div>
                    </label>

                    <Link 
                        :href="route('products.create')" 
                        class="bg-indigo-600 hover:bg-indigo-700 active:scale-95 active:shadow-lg text-white px-6 py-3 rounded-2xl flex items-center gap-2 transition-all duration-200 shadow-lg shadow-indigo-500/20 hover:shadow-xl font-bold uppercase text-xs tracking-widest cursor-pointer"
                    >
                        <PackagePlus class="w-5 h-5" />
                        Novo Produto
                    </Link>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse shadow-2xl">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="p-5 text-[10px] font-black uppercase text-gray-400 tracking-wider">Produto</th>
                            <th class="p-5 text-[10px] font-black uppercase text-gray-400 tracking-wider text-center">Status</th>
                            <th class="p-5 text-[10px] font-black uppercase text-gray-400 tracking-wider text-center">Preview</th>
                            <th class="p-5 text-[10px] font-black uppercase text-gray-400 tracking-wider">Financeiro</th>
                            <th class="p-5 text-[10px] font-black uppercase text-gray-400 tracking-wider text-center">Estoque</th>
                            <th class="p-5 text-[10px] font-black uppercase text-gray-400 tracking-wider text-center">Destaque</th>
                            <th class="p-5 text-[10px] font-black uppercase text-gray-400 tracking-wider text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50/80 transition-colors group">
                            <td class="p-5">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-800">{{ product.description }}</span>
                                    <span class="text-[10px] text-gray-400 font-medium uppercase">
                                        {{ product.brand || 'Sem marca' }} • {{ product.model || 'Sem modelo' }}
                                    </span>
                                </div>
                            </td>

                            <td class="p-5 text-center">
                                <div class="flex justify-center">
                                    <span v-if="product.is_active" class="flex items-center gap-1 text-[9px] font-black uppercase text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-100">
                                        <Eye class="w-3 h-3" /> Ativo
                                    </span>
                                    <span v-else class="flex items-center gap-1 text-[9px] font-black uppercase text-red-600 bg-red-50 px-2 py-1 rounded-lg border border-red-100">
                                        <EyeOff class="w-3 h-3" /> Bloqueado
                                    </span>
                                </div>
                            </td>

                            <td class="p-5 text-center">
                                <Link :href="route('products.preview', product.id)" class="inline-flex p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all">
                                    <Eye class="w-5 h-5" />
                                </Link>
                            </td>

                            <td class="p-5">
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-gray-700">{{ formatCurrency(product.sale_price) }}</span>
                                    <span class="text-[9px] text-green-600 font-bold uppercase">Custo: {{ formatCurrency(product.cost_price) }}</span>
                                </div>
                            </td>

                            <td class="p-5 text-center">
                                <span :class="[
                                    'px-3 py-1 rounded-full text-[10px] font-black uppercase inline-block min-w-[60px]',
                                    product.stock_quantity > 10 ? 'bg-blue-50 text-blue-600' : 'bg-red-50 text-red-600'
                                ]">
                                    {{ product.stock_quantity }} un
                                </span>
                            </td>

                            <td class="p-5 text-center">
                                <button 
                                    @click="toggleFeatured(product.id)"
                                    :class="product.is_featured ? 'text-amber-400' : 'text-gray-200 hover:text-amber-200'"
                                    class="transition-colors"
                                >
                                    <Star class="w-6 h-6 fill-current" />
                                </button>
                            </td>

                            <td class="p-5 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <Link :href="route('products.edit', product.id)" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                        <Edit class="w-5 h-5" />
                                    </Link>
                                    <button v-if="user.access_level == 1" @click="destroy(product.id)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all">
                                        <Trash2 class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="filteredProducts.length === 0">
                            <td colspan="7" class="p-20 text-center">
                                <div class="flex flex-col items-center opacity-40">
                                    <PackageSearch class="w-16 h-16 mb-4 text-gray-300" />
                                    <p class="font-black uppercase text-xs tracking-widest text-gray-400">
                                        {{ showOnlyBlocked ? 'Nenhum produto bloqueado' : 'Nenhum produto encontrado' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="products.links.length > 3" class="p-5 bg-gray-50 border-t border-gray-100 flex justify-center gap-2">
                    <Link 
                        v-for="(link, k) in products.links" 
                        :key="k"
                        :href="link.url || '#'"
                        v-html="link.label"
                        :class="[
                            'px-4 py-2 text-xs font-bold rounded-lg transition-all cursor-pointer',
                            link.active ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-white text-gray-500 hover:bg-emerald-50 hover:text-emerald-600',
                            !link.url ? 'opacity-50 cursor-not-allowed' : 'active:scale-95 active:shadow-lg'
                        ]"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>