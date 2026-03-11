<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { clearFormData } from '@/lib/utils';
import { 
    Save, ArrowLeft, DollarSign, 
    Star, Percent, Keyboard
} from 'lucide-vue-next';

const props = defineProps({
    product: Object,
    suppliers: Array
});

const activeTab = ref('geral');

// Inicializa o formulário com os dados existentes
const form = useForm({
    supplier_id: props.product.supplier_id,
    description: props.product.description,
    brand: props.product.brand,
    model: props.product.model,
    size: props.product.size,
    collection: props.product.collection,
    gender: props.product.gender || 'Unissex',
    barcode: props.product.barcode,
    stock_quantity: props.product.stock_quantity,
    is_active: Boolean(props.product.is_active),
    is_featured: Boolean(props.product.is_featured),
    
    cost_price: props.product.cost_price,
    sale_price: props.product.sale_price,
    promo_price: props.product.promo_price,
    promo_start_at: props.product.promo_start_at ? props.product.promo_start_at.slice(0, 16) : '',
    promo_end_at: props.product.promo_end_at ? props.product.promo_end_at.slice(0, 16) : '',

    meta_title: props.product.seo?.meta_title || '',
    meta_description: props.product.seo?.meta_description || '',
    meta_keywords: props.product.seo?.meta_keywords || '',
});

const handleKeydown = (e) => {
    if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'l') {
        e.preventDefault();
        if(confirm('Limpar campos?')) clearFormData(form);
    }
};

onMounted(() => window.addEventListener('keydown', handleKeydown, true));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown, true));

const profitData = computed(() => {
    const cost = parseFloat(form.cost_price) || 0;
    const sale = parseFloat(form.sale_price) || 0;
    const profit = sale - cost;
    const margin = cost > 0 ? (profit / cost) * 100 : 0;
    return {
        value: profit.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }),
        percentage: margin.toFixed(2)
    };
});

const submit = () => {
    form.put(route('products.update', props.product.id));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="'Editar: ' + product.description" />

        <div class="max-w-5xl mx-auto pb-20">
            <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 pt-10">
                <div>
                    <Link :href="route('products.index')" class="flex items-center text-[10px] font-black uppercase text-gray-400 hover:text-indigo-600 transition mb-2 tracking-widest">
                        <ArrowLeft class="w-3 h-3 mr-1" /> Voltar ao estoque
                    </Link>
                    <h2 class="text-3xl font-black text-gray-800 tracking-tighter uppercase">Editar Produto</h2>
                </div>
                
                <div class="flex bg-gray-100 p-1 rounded-xl border border-gray-200 shadow-inner">
                    <button type="button" @click="activeTab = 'geral'" :class="['px-4 py-2 text-[10px] font-black uppercase rounded-lg transition-all', activeTab === 'geral' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700']">Geral</button>
                    <button type="button" @click="activeTab = 'precos'" :class="['px-4 py-2 text-[10px] font-black uppercase rounded-lg transition-all', activeTab === 'precos' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700']">Financeiro</button>
                    <button type="button" @click="activeTab = 'seo'" :class="['px-4 py-2 text-[10px] font-black uppercase rounded-lg transition-all', activeTab === 'seo' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700']">SEO & Marketing</button>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                
                <div v-show="activeTab === 'geral'" class="animate-in fade-in slide-in-from-bottom-2 duration-500">
                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Descrição do Produto</label>
                            <input v-model="form.description" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl focus:ring-indigo-500 font-bold" required />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Fornecedor</label>
                            <select v-model="form.supplier_id" class="w-full border-gray-100 bg-gray-50 rounded-2xl focus:ring-indigo-500 text-sm font-bold" required>
                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.company_name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Código de Barras</label>
                            <input v-model="form.barcode" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <input v-model="form.brand" type="text" placeholder="Marca" class="w-full border-gray-100 bg-gray-50 rounded-2xl" />
                            <input v-model="form.model" type="text" placeholder="Modelo" class="w-full border-gray-100 bg-gray-50 rounded-2xl" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <input v-model="form.collection" type="text" placeholder="Coleção" class="w-full border-gray-100 bg-gray-50 rounded-2xl" />
                            <input v-model="form.size" type="text" placeholder="Tamanho" class="w-full border-gray-100 bg-gray-50 rounded-2xl" />
                        </div>
                    </div>
                </div>

                <div v-show="activeTab === 'precos'" class="animate-in fade-in slide-in-from-bottom-2 duration-500">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2 bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                            <h3 class="flex items-center text-xs font-black uppercase text-gray-400 mb-6 italic"><DollarSign class="w-4 h-4 mr-2" /> Preços</h3>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Custo (R$)</label>
                                    <input v-model="form.cost_price" type="number" step="0.01" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-black text-lg" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Venda (R$)</label>
                                    <input v-model="form.sale_price" type="number" step="0.01" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-black text-lg text-indigo-600" />
                                </div>
                            </div>
                            <div class="mt-8 p-6 bg-green-50 rounded-2xl flex justify-between items-center shadow-inner">
                                <div>
                                    <p class="text-[10px] font-black text-green-700 uppercase mb-1">Lucro Bruto</p>
                                    <p class="text-3xl font-black text-green-600 tracking-tighter">{{ profitData.value }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-green-700 uppercase mb-1">Markup %</p>
                                    <p class="text-3xl font-black text-green-600 tracking-tighter">{{ profitData.percentage }}%</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-900 p-8 rounded-3xl shadow-xl text-white">
                            <h3 class="flex items-center text-xs font-black uppercase opacity-60 mb-6 italic">Promoção</h3>
                            <div class="space-y-4">
                                <input v-model="form.promo_price" type="number" step="0.01" class="w-full bg-indigo-800 border-none rounded-2xl font-black text-white" placeholder="R$ 0,00" />
                                <div>
                                    <label class="text-[9px] font-black uppercase opacity-40">Início</label>
                                    <input v-model="form.promo_start_at" type="datetime-local" class="w-full bg-indigo-800 border-none rounded-xl text-[10px] text-white" />
                                </div>
                                <div>
                                    <label class="text-[9px] font-black uppercase opacity-40">Fim</label>
                                    <input v-model="form.promo_end_at" type="datetime-local" class="w-full bg-indigo-800 border-none rounded-xl text-[10px] text-white" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-show="activeTab === 'seo'" class="animate-in fade-in slide-in-from-bottom-2 duration-500">
                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-8">
                        <div class="flex items-center justify-between p-6 bg-amber-50 rounded-3xl border border-amber-100 cursor-pointer" @click="form.is_featured = !form.is_featured">
                            <div class="flex items-center">
                                <div :class="['p-3 rounded-2xl transition-all', form.is_featured ? 'bg-amber-500 text-white shadow-lg' : 'bg-amber-100 text-amber-400']">
                                    <Star :class="['w-6 h-6', form.is_featured ? 'fill-white' : '']" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-black text-amber-900 uppercase">Destaque de Vitrine</p>
                                </div>
                            </div>
                            <div :class="['w-12 h-6 rounded-full transition-colors relative border border-amber-200', form.is_featured ? 'bg-amber-500' : 'bg-white']">
                                <div :class="['w-4 h-4 bg-white rounded-full absolute top-1 transition-all', form.is_featured ? 'left-7' : 'left-1']"></div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Meta Title (SEO)</label>
                                <input v-model="form.meta_title" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Meta Description</label>
                                <textarea v-model="form.meta_description" rows="3" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-medium"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row items-center justify-end gap-6 border-t border-gray-100 pt-8">
                    <Link :href="route('products.index')" class="text-[10px] font-black uppercase text-gray-400 hover:text-gray-600 transition tracking-[0.2em]">Descartar</Link>
                    <button type="submit" :disabled="form.processing" class="bg-black text-white px-12 py-5 rounded-3xl font-black uppercase text-[10px] tracking-[0.3em] shadow-2xl hover:bg-indigo-600 transition-all flex items-center gap-3">
                        <Save v-if="!form.processing" class="w-4 h-4" />
                        {{ form.processing ? 'Processando' : 'Atualizar Dados' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>