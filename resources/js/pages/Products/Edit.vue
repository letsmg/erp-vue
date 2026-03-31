<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import { useProductEdit } from './useProductEdit';
import { 
    Save, ArrowLeft, DollarSign, Camera, X, Code, 
    Search, FileText, Truck
} from 'lucide-vue-next';

const props = defineProps({
    product: Object,
    suppliers: Array,
    categories: Array
});

const { 
    form, 
    activeTab, 
    newImagePreviews, 
    tagInput,      
    addTag,        
    removeTag,     
    handleImageUpload, 
    removeExistingImage, 
    removeNewImage, 
    profitData, 
    submit 
} = useProductEdit(props);

const dragOptions = { 
    animation: 200, 
    ghostClass: "opacity-30", 
    dragClass: "rotate-2" 
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
                    <button type="button" @click="activeTab = 'seo'" :class="['px-4 py-2 text-[10px] font-black uppercase rounded-lg transition-all', activeTab === 'seo' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700']">Marketing & SEO</button>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                
                <div v-show="activeTab === 'geral'" class="animate-in fade-in slide-in-from-bottom-2 duration-500 space-y-6">
                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-4 tracking-wider">Galeria (Arraste para ordenar)</label>
                        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                            <draggable 
                                v-model="form.existing_images" 
                                item-key="id" 
                                class="contents" 
                                v-bind="dragOptions"
                            >
                                <template #item="{ element, index }">
                                    <div class="relative group aspect-square rounded-2xl overflow-hidden border border-gray-100 shadow-sm cursor-move">
                                        <img :src="'/storage/products/' + element.path" class="w-full h-full object-cover" />
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                            <button type="button" @click="removeExistingImage(index)" class="bg-white text-red-600 p-2 rounded-full shadow-lg hover:scale-110 transition">
                                                <X class="w-4 h-4" />
                                            </button>
                                        </div>
                                        <span class="absolute top-2 left-2 bg-black/50 backdrop-blur-md text-[8px] text-white px-2 py-0.5 rounded-full uppercase font-black">{{ index + 1 }}º</span>
                                    </div>
                                </template>
                            </draggable>

                            <div v-for="(src, index) in newImagePreviews" :key="'new-'+index" class="relative group aspect-square rounded-2xl overflow-hidden border-2 border-indigo-100">
                                <img :src="src" class="w-full h-full object-cover" />
                                <button type="button" @click="removeNewImage(index)" class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full shadow-md">
                                    <X class="w-3 h-3" />
                                </button>
                                <span class="absolute bottom-1 left-1 bg-indigo-600 text-[6px] text-white px-1 rounded font-bold uppercase">Novo</span>
                            </div>

                            <label v-if="(form.existing_images.length + form.new_images.length) < 6" class="aspect-square border-2 border-dashed border-gray-100 rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:bg-gray-50 transition group">
                                <Camera class="w-6 h-6 text-gray-300 group-hover:text-indigo-500 transition" />
                                <input type="file" class="hidden" multiple accept="image/*" @change="handleImageUpload" />
                            </label>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Descrição Curta</label>
                            <input v-model="form.description" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Fornecedor</label>
                            <select v-model="form.supplier_id" class="w-full border-gray-100 bg-gray-50 rounded-2xl text-sm font-bold" required>
                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.company_name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">EAN / Barcode</label>
                            <input v-model="form.barcode" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Marca</label>
                            <input v-model="form.brand" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
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
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Tamanho</label>
                            <input v-model="form.size" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Gênero</label>
                            <select v-model="form.gender" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold uppercase text-[10px]">
                                <option value="Masculino">Masculino</option>
                                <option value="Feminino">Feminino</option>
                                <option value="Unissex">Unissex</option>
                                <option value="Infantil">Infantil</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Estoque Real</label>
                            <input v-model="form.stock_quantity" type="number" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-black text-indigo-600" />
                        </div>
                    </div>
                </div>

                <div v-show="activeTab === 'precos'" class="animate-in fade-in slide-in-from-bottom-2 duration-500 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2 space-y-6">
                            <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                                <h3 class="flex items-center text-xs font-black uppercase text-gray-400 mb-6 italic"><DollarSign class="w-4 h-4 mr-2" /> Preços</h3>
                                <div class="grid grid-cols-2 gap-6">
                                    <div><label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Custo (R$)</label><input v-model="form.cost_price" type="number" step="0.01" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" /></div>
                                    <div><label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Venda (R$)</label><input v-model="form.sale_price" type="number" step="0.01" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-black text-indigo-600" /></div>
                                </div>
                                <div class="mt-8 p-6 bg-green-50 rounded-2xl border border-green-100 flex justify-between items-center text-green-700">
                                    <div><p class="text-[10px] font-black uppercase">Lucro</p><p class="text-3xl font-black">{{ profitData.value }}</p></div>
                                    <div class="text-right"><p class="text-[10px] font-black uppercase">Margem</p><p class="text-3xl font-black">{{ profitData.percentage }}%</p></div>
                                </div>
                            </div>

                            <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="flex items-center text-xs font-black uppercase text-gray-400 italic"><Truck class="w-4 h-4 mr-2" /> Logística</h3>
                                    <label class="flex items-center gap-2 cursor-pointer"><span class="text-[10px] font-black uppercase text-gray-400">Frete Grátis</span><input type="checkbox" v-model="form.free_shipping" class="rounded text-indigo-600 border-gray-300"></label>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div><label class="block text-[9px] font-black uppercase text-gray-400 mb-1">Peso (kg)</label><input v-model="form.weight" type="number" step="0.001" class="w-full border-gray-100 bg-gray-50 rounded-xl font-bold text-sm" placeholder="0.000" /></div>
                                    <div><label class="block text-[9px] font-black uppercase text-gray-400 mb-1">Largura (cm)</label><input v-model="form.width" type="number" step="0.01" class="w-full border-gray-100 bg-gray-50 rounded-xl font-bold text-sm" placeholder="0.00" /></div>
                                    <div><label class="block text-[9px] font-black uppercase text-gray-400 mb-1">Altura (cm)</label><input v-model="form.height" type="number" step="0.01" class="w-full border-gray-100 bg-gray-50 rounded-xl font-bold text-sm" placeholder="0.00" /></div>
                                    <div><label class="block text-[9px] font-black uppercase text-gray-400 mb-1">Comp. (cm)</label><input v-model="form.length" type="number" step="0.01" class="w-full border-gray-100 bg-gray-50 rounded-xl font-bold text-sm" placeholder="0.00" /></div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-900 p-8 rounded-3xl text-white h-fit">
                            <h3 class="text-[10px] font-black uppercase opacity-60 mb-6 italic">Promoção</h3>
                            <div class="space-y-4">
                                <div><label class="text-[9px] opacity-40 block mb-1">Preço Promo</label><input v-model="form.promo_price" type="number" step="0.01" class="w-full bg-indigo-800 border-none rounded-2xl text-white font-bold" /></div>
                                <div><label class="text-[9px] opacity-40 block mb-1">Início</label><input v-model="form.promo_start_at" type="datetime-local" class="w-full bg-indigo-800 border-none rounded-xl text-[10px] text-white" /></div>
                                <div><label class="text-[9px] opacity-40 block mb-1">Término</label><input v-model="form.promo_end_at" type="datetime-local" class="w-full bg-indigo-800 border-none rounded-xl text-[10px] text-white" /></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-show="activeTab === 'seo'" class="animate-in fade-in slide-in-from-bottom-2 duration-500 space-y-6">
                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                        <div class="flex items-center gap-3"><Code class="w-5 h-5 text-indigo-600" /><h3 class="text-xs font-black uppercase tracking-widest text-gray-500">Configurações de URL</h3></div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Slug da URL (Canônica)</label>
                            <input v-model="form.slug" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold text-xs" />
                            <p class="mt-2 text-[10px] text-gray-400 italic">Cuidado: Alterar o slug pode quebrar links externos indexados.</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Google Tag Manager Script</label>
                            <textarea v-model="form.google_tag_manager" rows="3" class="w-full border-gray-100 bg-gray-50 rounded-2xl text-[11px] font-mono"></textarea>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                        <div class="flex items-center gap-3"><Search class="w-5 h-5 text-amber-600" /><h3 class="text-xs font-black uppercase tracking-widest text-gray-500">SEO Meta Tags</h3></div>
                        <div><label class="block text-[10px] font-black uppercase text-gray-400 mb-2">SEO Title (Máx 70)</label><input v-model="form.meta_title" maxlength="70" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" required /></div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Palavras-Chave (Enter)</label>
                            <div class="flex flex-wrap gap-2 p-3 border border-gray-100 bg-gray-50 rounded-2xl">
                                <div v-for="(tag, index) in form.meta_keywords" :key="index" class="flex items-center bg-indigo-600 text-white text-[9px] font-black uppercase px-3 py-1.5 rounded-xl">
                                    {{ tag }} <button type="button" @click="removeTag(index)" class="ml-2 hover:bg-indigo-700 transition"><X class="w-3 h-3" /></button>
                                </div>
                                <input v-model="tagInput" @keydown.enter.prevent="addTag" placeholder="Nova tag..." class="flex-1 bg-transparent border-none focus:ring-0 text-sm font-bold p-0" />
                            </div>
                        </div>
                        <div><label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Meta Description (Máx 160)</label><textarea v-model="form.meta_description" maxlength="160" rows="2" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold text-xs" required></textarea></div>
                    </div>

                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                        <div class="flex items-center gap-3"><FileText class="w-5 h-5 text-green-600" /><h3 class="text-xs font-black uppercase tracking-widest text-gray-500">Conteúdo On-Page</h3></div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Título H1 Principal</label><input v-model="form.h1" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" required /></div>
                            <div><label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Texto de Apresentação (Text 1)</label><textarea v-model="form.text1" rows="3" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" required></textarea></div>    
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Subtítulo H2</label><input v-model="form.h2" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold" /></div>
                            <div><label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Detalhes Adicionais (Text 2)</label><textarea v-model="form.text2" rows="3" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-bold"></textarea></div>
                        </div>
                        <div><label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Schema JSON-LD</label><textarea v-model="form.schema_markup" rows="2" class="w-full border-gray-100 bg-gray-50 rounded-2xl font-mono text-[10px]"></textarea></div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-6 border-t border-gray-100 pt-8">
                    <button type="submit" :disabled="form.processing" class="bg-emerald-600 hover:bg-emerald-700 active:scale-95 active:shadow-lg text-white px-12 py-5 rounded-3xl font-black uppercase text-[10px] tracking-[0.3em] shadow-2xl shadow-emerald-500/20 hover:shadow-xl transition-all duration-200 flex items-center gap-3 disabled:opacity-50 disabled:scale-100 transform cursor-pointer">
                        <Save class="w-4 h-4" />
                        {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>