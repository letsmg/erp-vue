<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import { useProductForm } from './useProductForm'; 
import { 
    Save, ArrowLeft, DollarSign, Camera, X, Move,
    Search, Code, FileText
} from 'lucide-vue-next';

const props = defineProps({
    suppliers: Array
});

// AQUI ESTÁ A CORREÇÃO: Importando tagInput, addTag e removeTag
const { 
    form, 
    activeTab, 
    imagePreviews, 
    tagInput,    // <--- ADICIONADO
    addTag,      // <--- ADICIONADO
    removeTag,   // <--- ADICIONADO
    handleImageUpload, 
    removeImage, 
    onDragEnd, 
    profitData, 
    submit 
} = useProductForm(props);
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
                    <h2 class="text-3xl font-black text-gray-800 tracking-tighter uppercase">Novo Produto</h2>
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
                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-4 tracking-widest">
                            Fotos do Produto (Arraste para ordenar)
                        </label>
                        
                        <draggable 
                            v-model="form.images" 
                            item-key="tempId"
                            class="grid grid-cols-2 md:grid-cols-6 gap-4"
                            ghost-class="opacity-50"
                            @end="onDragEnd"
                        >
                            <template #item="{ element, index }">
                                <div class="relative group aspect-square rounded-2xl overflow-hidden border border-gray-100 bg-gray-50 cursor-move shadow-sm hover:shadow-md transition-all">
                                    <img :src="imagePreviews[index]" class="w-full h-full object-cover" />
                                    
                                    <div class="absolute inset-0 bg-indigo-600/20 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                        <Move class="text-white w-6 h-6 opacity-50" />
                                    </div>

                                    <button type="button" @click="removeImage(index)" class="absolute top-2 right-2 bg-white text-red-500 p-1.5 rounded-full shadow-lg hover:bg-red-50 transition transform hover:scale-110">
                                        <X class="w-3 h-3" />
                                    </button>
                                    
                                    <div v-if="index === 0" class="absolute bottom-2 left-2 bg-black/60 text-[8px] text-white px-2 py-0.5 rounded-full font-black uppercase tracking-tighter">
                                        Capa
                                    </div>
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
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Descrição Curta (Nome do Produto)</label>
                            <input v-model="form.description" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl focus:ring-indigo-500 font-bold" required />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Fornecedor</label>
                            <select v-model="form.supplier_id" class="w-full border-gray-100 bg-gray-50 rounded-2xl focus:ring-indigo-500 text-sm font-bold" required>
                                <option :value="null">Selecione...</option>
                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.company_name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Código de Barras</label>
                            <input v-model="form.barcode" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <input v-model="form.brand" type="text" placeholder="Marca" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                            <input v-model="form.model" type="text" placeholder="Modelo" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <input v-model="form.collection" type="text" placeholder="Coleção" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                            <input v-model="form.size" type="text" placeholder="Tamanho" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                        </div>

                        <div class="grid grid-cols-2 gap-4 md:col-span-2">
                            <select v-model="form.gender" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold uppercase text-[10px]">
                                <option>Masculino</option><option>Feminino</option><option>Unissex</option><option>Infantil</option>
                            </select>
                            <input v-model="form.stock_quantity" type="number" placeholder="Qtd. Estoque" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-black" />
                        </div>
                    </div>
                </div>

                <div v-show="activeTab === 'precos'" class="animate-in fade-in slide-in-from-bottom-2 duration-500">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2 bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                            <h3 class="flex items-center text-xs font-black uppercase text-gray-400 mb-6 italic"><DollarSign class="w-4 h-4 mr-2" /> Preços</h3>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Preço de Custo</label>
                                    <input v-model="form.cost_price" type="number" step="0.01" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-black text-xl" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Preço de Venda</label>
                                    <input v-model="form.sale_price" type="number" step="0.01" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-black text-xl text-indigo-600" />
                                </div>
                            </div>
                            
                            <div class="mt-8 p-6 bg-green-50 rounded-2xl border border-green-100 flex justify-between items-center">
                                <div>
                                    <p class="text-[10px] font-black text-green-700 uppercase mb-1">Lucro Estimado</p>
                                    <p class="text-3xl font-black text-green-600 tracking-tighter">{{ profitData.value }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-green-700 uppercase mb-1">Margem (Markup)</p>
                                    <p class="text-3xl font-black text-green-600 tracking-tighter">{{ profitData.percentage }}%</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-900 p-8 rounded-3xl shadow-xl text-white relative">
                            <h3 class="flex items-center text-xs font-black uppercase opacity-60 mb-6 italic">Agendar Promoção</h3>
                            <div class="space-y-4">
                                <input v-model="form.promo_price" type="number" step="0.01" class="w-full bg-indigo-800 border-none rounded-2xl font-black text-white placeholder-indigo-400" placeholder="Preço Promo (R$)" />
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

                <div v-show="activeTab === 'seo'" class="animate-in fade-in slide-in-from-bottom-2 duration-500 space-y-6">
                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-indigo-50 rounded-lg"><Code class="w-5 h-5 text-indigo-600" /></div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-500">Scripts de Rastreamento</h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-2 italic">Google Tag Manager (Script Completo)</label>
                                <textarea v-model="form.google_tag_manager" rows="4" class="w-full border-gray-100 bg-gray-50 rounded-2xl text-[11px] font-mono text-indigo-600" placeholder="Cole o script do GTM aqui..."></textarea>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-gray-400 mb-2 italic">ID Google Ads</label>
                                    <input v-model="form.ads" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" placeholder="AW-XXXXXX" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-gray-400 mb-2 italic">URL Canônica</label>
                                    <input v-model="form.canonical_url" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" placeholder="https://..." />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 flex items-center gap-3 mb-2">
                            <div class="p-2 bg-amber-50 rounded-lg"><Search class="w-5 h-5 text-amber-600" /></div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-500">Otimização para Buscadores (Meta)</h3>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2 italic">Meta Title</label>
                            <input v-model="form.meta_title" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2 italic">
                                Meta Keywords (Digite e aperte Enter)
                            </label>
                            
                            <div class="flex flex-wrap gap-2 p-3 border border-gray-100 bg-gray-50 rounded-2xl min-h-[50px] transition-all focus-within:border-indigo-300 focus-within:ring-2 focus-within:ring-indigo-100">
                                
                                <div 
                                    v-for="(tag, index) in form.meta_keywords" 
                                    :key="index"
                                    class="flex items-center bg-indigo-600 text-white text-[10px] font-black uppercase tracking-tight px-3 py-1.5 rounded-xl shadow-sm animate-in zoom-in-95 duration-200"
                                >
                                    {{ tag }}
                                    <button 
                                        type="button" 
                                        @click="removeTag(index)" 
                                        class="ml-2 hover:bg-indigo-700 rounded-full p-0.5 transition"
                                    >
                                        <X class="w-3 h-3" />
                                    </button>
                                </div>
                                
                                <input 
                                    v-model="tagInput" 
                                    @keydown.enter.prevent="addTag"
                                    type="text" 
                                    class="flex-1 bg-transparent border-none focus:ring-0 text-sm font-bold min-w-[150px] p-0 placeholder:text-gray-300"
                                    placeholder="TAG + ENTER" 
                                />
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2 italic">Meta Description</label>
                            <textarea v-model="form.meta_description" rows="2" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold"></textarea>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 flex items-center gap-3 mb-2">
                            <div class="p-2 bg-green-50 rounded-lg"><FileText class="w-5 h-5 text-green-600" /></div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-500">Conteúdo de Página</h3>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2 italic">H1 (Título na Página)</label>
                            <input v-model="form.h1" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2 italic">H2 (Subtítulo)</label>
                            <input v-model="form.h2" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2 italic">Texto de Apresentação 1</label>
                            <textarea v-model="form.text1" rows="3" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2 italic">Schema Markup (JSON-LD)</label>
                            <textarea v-model="form.schema_markup" rows="3" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-mono text-[10px]" placeholder='{"@context": "https://schema.org"}'></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row items-center justify-end gap-6 border-t border-gray-100 pt-8">
                    <button type="submit" :disabled="form.processing" class="bg-black text-white px-12 py-5 rounded-3xl font-black uppercase text-[10px] tracking-[0.3em] shadow-2xl hover:bg-indigo-600 transition-all flex items-center gap-3 disabled:opacity-50">
                        <Save v-if="!form.processing" class="w-4 h-4" />
                        <span v-else class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                        {{ form.processing ? 'Processando' : 'Salvar Produto' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>