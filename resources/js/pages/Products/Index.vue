<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { PackagePlus, Edit, Trash2, Globe, PackageSearch } from 'lucide-vue-next';
import { Link, router } from '@inertiajs/vue3';

// Recebe os produtos do Controller
defineProps({ 
    products: Array 
});

// Função para formatar moeda
const formatCurrency = (value) => {
    return new Number(value).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    });
};

// Função de exclusão com confirmação
const destroy = (id) => {
    if (confirm('Deseja realmente excluir este produto? Esta ação apagará também os dados de SEO vinculados.')) {
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
                
                <Link 
                    :href="route('products.create')" 
                    class="bg-black text-white px-6 py-3 rounded-2xl flex items-center gap-2 hover:bg-indigo-600 transition-all shadow-lg hover:shadow-indigo-200 font-bold uppercase text-xs tracking-widest"
                >
                    <PackagePlus class="w-5 h-5" />
                    Novo Produto
                </Link>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="p-5 text-[10px] font-black uppercase text-gray-400 tracking-wider">Produto</th>
                            <th class="p-5 text-[10px] font-black uppercase text-gray-400 tracking-wider">Financeiro</th>
                            <th class="p-5 text-[10px] font-black uppercase text-gray-400 tracking-wider">Estoque</th>
                            <th class="p-5 text-[10px] font-black uppercase text-gray-400 tracking-wider">Status SEO</th>
                            <th class="p-5 text-[10px] font-black uppercase text-gray-400 tracking-wider text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50/80 transition-colors group">
                            <td class="p-5">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition-colors">
                                        {{ product.description }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-medium uppercase tracking-tighter">
                                        {{ product.brand || 'Sem marca' }} • {{ product.model || 'Sem modelo' }}
                                    </span>
                                </div>
                            </td>

                            <td class="p-5">
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-gray-700">
                                        {{ formatCurrency(product.sale_price) }}
                                    </span>
                                    <span class="text-[9px] text-green-600 font-bold uppercase">
                                        Custo: {{ formatCurrency(product.cost_price) }}
                                    </span>
                                </div>
                            </td>

                            <td class="p-5">
                                <div class="flex items-center gap-2">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-[10px] font-black uppercase',
                                        product.stock_quantity > 10 ? 'bg-blue-50 text-blue-600' : 'bg-red-50 text-red-600'
                                    ]">
                                        {{ product.stock_quantity }} un
                                    </span>
                                </div>
                            </td>

                            <td class="p-5">
                                <div class="flex items-center gap-2">
                                    <span v-if="product.seo?.meta_title" class="flex items-center gap-1 text-[10px] font-black uppercase text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100">
                                        <Globe class="w-3 h-3" /> Otimizado
                                    </span>
                                    <span v-else class="text-[10px] font-black uppercase text-gray-400 bg-gray-100 px-3 py-1 rounded-full border border-gray-200">
                                        Padrão
                                    </span>
                                    
                                    <span v-if="product.is_featured" class="text-amber-500" title="Destaque na Vitrine">
                                        ★
                                    </span>
                                </div>
                            </td>

                            <td class="p-5">
                                <div class="flex justify-center items-center gap-2">
                                    <Link 
                                        :href="route('products.edit', product.id)" 
                                        class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all"
                                        title="Editar Produto"
                                    >
                                        <Edit class="w-5 h-5" />
                                    </Link>
                                    
                                    <button 
                                        @click="destroy(product.id)" 
                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all"
                                        title="Excluir Produto"
                                    >
                                        <Trash2 class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="products.length === 0">
                            <td colspan="5" class="p-20 text-center">
                                <div class="flex flex-col items-center opacity-20">
                                    <PackageSearch class="w-16 h-16 mb-4" />
                                    <p class="font-black uppercase text-xs tracking-[0.2em]">Nenhum produto encontrado</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>