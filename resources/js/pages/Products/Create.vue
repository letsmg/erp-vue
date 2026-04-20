<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import draggable from 'vuedraggable';
import { useProductForm } from './useProductForm'; 
import { 
    Save, ArrowLeft, DollarSign, Camera, X, Move,
    Search, Code, FileText, Truck, Sparkles, X as ClearIcon
} from 'lucide-vue-next';

const props = defineProps({
    suppliers: Array,
    categories: Array
});

const { 
    form, activeTab, imagePreviews, tagInput,
    addTag, removeTag, handleImageUpload, removeImage, 
    onDragEnd, profitData, submit, fillTestForm, clearCurrentForm 
} = useProductForm(props);

const handleShortcut = (e) => {
    // Verifica se é teclado numérico (location 3)
    const isNumpad = e.location === 3 || e.code.startsWith('Numpad');
    
    // Para teclado numérico, usa apenas Alt+1/2
    if (isNumpad) {
        if (e.altKey && (e.key === '1' || e.code === 'Numpad1' || e.keyCode === 97 || e.keyCode === 49)) {
            e.preventDefault();
            fillTestForm();
            return;
        }
        if (e.altKey && (e.key === '2' || e.code === 'Numpad2' || e.keyCode === 98 || e.keyCode === 50)) {
            e.preventDefault();
            clearCurrentForm();
            return;
        }
    }

    // Para teclado QWERTY, Alt+1 ou Alt+2
    if (e.altKey && (
        e.key === '1' || 
        e.code === 'Digit1' ||
        e.keyCode === 49 ||
        e.which === 49
    )) {
        e.preventDefault();
        fillTestForm();
    }

    // Alt+2 para limpar
    if (e.altKey && (
        e.key === '2' || 
        e.code === 'Digit2' ||
        e.keyCode === 50 ||
        e.which === 50
    )) {
        e.preventDefault();
        clearCurrentForm();
    }
};

onMounted(() => window.addEventListener('keydown', handleShortcut));
onUnmounted(() => window.removeEventListener('keydown', handleShortcut));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Novo Produto" />

        <div class="max-w-5xl mx-auto pb-20">
            <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <Link :href="route('products.index')" class="flex items-center text-[10px] font-black uppercase text-gray-400 hover:text-indigo-600 transition mb-2 tracking-widest">
                        <ArrowLeft class="w-3 h-3 mr-1" /> Voltar ao estoque
                    </Link>
                    <div class="flex items-center gap-4">
                        <h2 class="text-3xl font-black text-gray-800 tracking-tighter uppercase">Novo Produto</h2>
                    </div>
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
                            <ClearIcon class="w-4 h-4 text-red-500" />
                            <span class="text-[11px] font-bold text-red-600">ALT+2</span>
                            <span class="text-[11px] text-gray-600">Limpar</span>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="mb-6 flex justify-center gap-4">
                    <button type="button" @click="fillTestForm" class="bg-slate-600 hover:bg-slate-700 active:scale-95 active:shadow-lg text-white px-6 py-3 rounded-xl font-bold text-sm uppercase tracking-wider shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2 transform cursor-pointer">
                        <Sparkles class="w-4 h-4" />
                        Popular Formulário
                    </button>
                    <button type="button" @click="clearCurrentForm" class="bg-slate-600 hover:bg-slate-700 active:scale-95 active:shadow-lg text-white px-6 py-3 rounded-xl font-bold text-sm uppercase tracking-wider shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2 transform cursor-pointer">
                        <ClearIcon class="w-4 h-4" />
                        Limpar Formulário
                    </button>
                </div>
                
                <div class="flex bg-gray-100 p-1 rounded-xl border border-gray-200 shadow-inner">
                    <button type="button" @click="activeTab = 'geral'" :class="['px-4 py-2 text-[10px] font-black uppercase rounded-lg transition-all', activeTab === 'geral' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700']">Geral</button>
                    <button type="button" @click="activeTab = 'precos'" :class="['px-4 py-2 text-[10px] font-black uppercase rounded-lg transition-all', activeTab === 'precos' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700']">Financeiro</button>
                    <button type="button" @click="activeTab = 'seo'" :class="['px-4 py-2 text-[10px] font-black uppercase rounded-lg transition-all', activeTab === 'seo' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700']">Marketing & SEO</button>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div v-show="activeTab === 'geral'" class="animate-in fade-in slide-in-from-bottom-2 duration-500 space-y-6">
                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-4 tracking-widest">Fotos do Produto (Arraste para ordenar)</label>
                        <draggable v-model="form.images" item-key="name" class="grid grid-cols-2 md:grid-cols-6 gap-4" ghost-class="opacity-50" @end="onDragEnd">
                            <template #item="{ element, index }">
                                <div class="relative group aspect-square rounded-2xl overflow-hidden border border-gray-100 bg-gray-50 cursor-move shadow-sm">
                                    <img :src="imagePreviews[index]" class="w-full h-full object-cover" />
                                    <div class="absolute inset-0 bg-indigo-600/20 opacity-0 group-hover:opacity-100 transition flex items-center justify-center"><Move class="text-white w-6 h-6 opacity-50" /></div>
                                    <button type="button" @click="removeImage(index)" class="absolute top-2 right-2 bg-white text-red-500 p-1.5 rounded-full shadow-lg hover:bg-red-50 transition"><X class="w-3 h-3" /></button>
                                    <div v-if="index === 0" class="absolute bottom-2 left-2 bg-black/60 text-[8px] text-white px-2 py-0.5 rounded-full font-black uppercase tracking-tighter">Capa</div>
                                </div>
                            </template>
                            <template #footer>
                                <label v-if="form.images.length < 6" class="aspect-square border-2 border-dashed border-gray-100 rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:bg-gray-50 transition group">
                                    <Camera class="w-6 h-6 text-gray-300 group-hover:text-indigo-500 transition" />
                                    <input type="file" class="hidden" multiple accept="image/*" @change="handleImageUpload" />
                                </label>
                            </template>
                        </draggable>
                    </div>

                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Nome Completo do Produto</label>
                            <input v-model="form.title" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl focus:ring-indigo-500 font-bold" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Fornecedor Origem</label>
                            <select v-model="form.supplier_id" class="w-full border-gray-100 bg-gray-50 rounded-2xl focus:ring-indigo-500 text-sm font-bold" required>
                                <option :value="null">Selecione o Fornecedor...</option>
                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.company_name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Código EAN / Barcode</label>
                            <input v-model="form.barcode" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Marca</label>
                            <input v-model="form.brand" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">
                                Categoria
                            </label>

                            <select 
                                v-model="form.category_id"                                
                                class="w-full border-gray-100 bg-gray-50 rounded-2xl text-sm font-bold focus:ring-indigo-500"
                            >
                                <option :value="null">Selecione uma categoria...</option>

                                <option 
                                    v-for="c in categories" 
                                    :key="c.id" 
                                    :value="c.id"
                                >
                                    {{ c.name }}
                                </option>
                            </select>

                            <p v-if="form.errors.category_id" class="text-red-500 text-xs mt-1">
                                {{ form.errors.category_id }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Modelo</label>
                            <input v-model="form.model" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Coleção</label>
                            <input v-model="form.collection" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Tamanho / Grade</label>
                            <input v-model="form.size" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Gênero Público</label>
                            <select v-model="form.gender" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold uppercase text-[10px]">
                                <option>Masculino</option><option>Feminino</option><option>Unissex</option><option>Infantil</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Quantidade Inicial em Estoque</label>
                            <input v-model="form.stock_quantity" type="number" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-black text-indigo-600" />
                        </div>
                    </div>
                </div>

                <div v-show="activeTab === 'precos'" class="animate-in fade-in slide-in-from-bottom-2 duration-500 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2 space-y-6">
                            <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                                <h3 class="flex items-center text-xs font-black uppercase text-gray-400 mb-6 italic"><DollarSign class="w-4 h-4 mr-2" /> Preços de Venda</h3>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Preço de Custo (R$)</label>
                                        <input v-model="form.cost_price" type="number" step="0.01" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-black text-xl" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Preço de Venda (R$)</label>
                                        <input v-model="form.sale_price" type="number" step="0.01" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-black text-xl text-indigo-600" />
                                    </div>
                                </div>
                                <div class="mt-8 p-6 bg-green-50 rounded-2xl border border-green-100 flex justify-between items-center">
                                    <div><p class="text-[10px] font-black text-green-700 uppercase mb-1">Lucro Líquido Estimado</p><p class="text-3xl font-black text-green-600 tracking-tighter">{{ profitData.value }}</p></div>
                                    <div class="text-right"><p class="text-[10px] font-black text-green-700 uppercase mb-1">Margem Real</p><p class="text-3xl font-black text-green-600 tracking-tighter">{{ profitData.percentage }}%</p></div>
                                </div>
                            </div>

                            <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="flex items-center text-xs font-black uppercase text-gray-400 italic"><Truck class="w-4 h-4 mr-2" /> Logística de Envio</h3>
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <span class="text-[10px] font-black uppercase text-gray-400 group-hover:text-indigo-600 transition">Ativar Frete Grátis</span>
                                        <input type="checkbox" v-model="form.free_shipping" class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    </label>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-[9px] font-black uppercase text-gray-400 mb-1">Peso (kg)</label>
                                        <input v-model="form.weight" type="number" step="0.001" min="0"
                                            class="w-full border-gray-100 bg-gray-50 rounded-xl font-bold text-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                            placeholder="0.000" />
                                    </div>

                                    <div>
                                        <label class="block text-[9px] font-black uppercase text-gray-400 mb-1">Largura (cm)</label>
                                        <input v-model="form.width" type="number" step="0.01" min="0"
                                            class="w-full border-gray-100 bg-gray-50 rounded-xl font-bold text-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                            placeholder="0.00" />
                                    </div>

                                    <div>
                                        <label class="block text-[9px] font-black uppercase text-gray-400 mb-1">Altura (cm)</label>
                                        <input v-model="form.height" type="number" step="0.01" min="0"
                                            class="w-full border-gray-100 bg-gray-50 rounded-xl font-bold text-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                            placeholder="0.00" />
                                    </div>

                                    <div>
                                        <label class="block text-[9px] font-black uppercase text-gray-400 mb-1">Comp. (cm)</label>
                                        <input v-model="form.length" type="number" step="0.01" min="0"
                                            class="w-full border-gray-100 bg-gray-50 rounded-xl font-bold text-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                            placeholder="0.00" />
                                    </div>
                                </div>                              
                            </div>
                        </div>

                        <div class="bg-indigo-900 p-8 rounded-3xl shadow-xl text-white h-fit">
                            <h3 class="text-xs font-black uppercase opacity-60 mb-6 italic">Agendar Promoção</h3>
                            <div class="space-y-4">
                                <div><label class="text-[9px] font-black uppercase opacity-40 mb-1 block">Preço Promocional (R$)</label><input v-model="form.promo_price" type="number" step="0.01" class="w-full bg-indigo-800 border-none rounded-2xl font-black text-white" /></div>
                                <div><label class="text-[9px] font-black uppercase opacity-40 mb-1 block">Data de Início</label><input v-model="form.promo_start_at" type="datetime-local" class="w-full bg-indigo-800 border-none rounded-xl text-[10px] text-white" /></div>
                                <div><label class="text-[9px] font-black uppercase opacity-40 mb-1 block">Data de Término</label><input v-model="form.promo_end_at" type="datetime-local" class="w-full bg-indigo-800 border-none rounded-xl text-[10px] text-white" /></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-show="activeTab === 'seo'" class="animate-in fade-in slide-in-from-bottom-2 duration-500 space-y-6">
                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                        <div class="flex items-center gap-3"><Code class="w-5 h-5 text-indigo-600" /><h3 class="text-xs font-black uppercase tracking-widest text-gray-500">Rastreamento e Tags</h3></div>
                        <div><label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Google Tag Manager Script</label><textarea v-model="form.google_tag_manager" rows="3" class="w-full border-gray-100 bg-gray-50 rounded-2xl text-[11px] font-mono"></textarea></div>                        
                        <div class="grid grid-cols-2 gap-4">                            
                            <div><label class="block text-[10px] font-black uppercase text-gray-400 mb-2">URL Canônica (Canonical)</label><input v-model="form.slug" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" /></div>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                        <div class="flex items-center gap-3"><Search class="w-5 h-5 text-amber-600" /><h3 class="text-xs font-black uppercase tracking-widest text-gray-500">SEO Meta Tags</h3></div>
                        <div class="text-xs text-gray-500 bg-amber-50 p-3 rounded-xl">
                            <strong>SEO Title e H1</strong> são derivados do campo <strong>Descrição</strong> do produto (limitado a 70 caracteres para SEO Title).
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Palavras-Chave (Pressione Enter para adicionar)</label>
                            <div class="flex flex-wrap gap-2 p-3 border border-gray-100 bg-gray-50 rounded-2xl min-h-[50px]">
                                <div v-for="(tag, index) in form.meta_keywords" :key="index" class="flex items-center bg-indigo-600 text-white text-[10px] font-black uppercase px-3 py-1.5 rounded-xl shadow-sm">
                                    {{ tag }} <button type="button" @click="removeTag(index)" class="ml-2 hover:bg-indigo-700 rounded-full p-0.5"><X class="w-3 h-3" /></button>
                                </div>
                                <input v-model="tagInput" @keydown.enter.prevent="addTag" type="text" class="flex-1 bg-transparent border-none focus:ring-0 text-sm font-bold p-0" />
                            </div>
                        </div>
                        <div><label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Meta Descrição (Snippet do Google)</label><textarea v-model="form.meta_description" rows="2" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold"></textarea></div>
                    </div>

                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                        <div class="flex items-center gap-3"><FileText class="w-5 h-5 text-green-600" /><h3 class="text-xs font-black uppercase tracking-widest text-gray-500">Conteúdo On-Page</h3></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Descrição Curta de Apresentação (Text 1)</label><textarea v-model="form.text1" rows="3" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold"></textarea></div>    
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Subítulo (H2)</label><input v-model="form.h2" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" /></div>
                            <div><label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Descrição Secundária / Detalhes (Text 2)</label><textarea v-model="form.text2" rows="3" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold"></textarea></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-6 border-t border-gray-100 pt-8">
                    <button type="submit" :disabled="form.processing" class="bg-emerald-600 hover:bg-emerald-700 active:scale-95 active:shadow-lg text-white px-12 py-5 rounded-3xl font-black uppercase text-[10px] tracking-[0.3em] shadow-2xl shadow-emerald-500/20 hover:shadow-xl transition-all duration-200 flex items-center gap-3 disabled:opacity-50 disabled:scale-100 transform cursor-pointer">
                        <Save class="w-4 h-4" />
                        {{ form.processing ? 'Salvando...' : 'Salvar Produto' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>