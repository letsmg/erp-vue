<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import {
    ArrowLeft, ShoppingCart, Globe, Star, Loader2,
    Eye, EyeOff, LayoutDashboard, ChevronLeft, ChevronRight
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import StoreLayout from '@/Layouts/StoreLayout.vue';

const props = defineProps({
    product: Object,
    relatedProducts: Array
});

// Sanitize HTML output to prevent XSS
const sanitizeHtml = (html) => {
    if (!html) return '';
    const temp = document.createElement('div');
    temp.textContent = html;
    return temp.innerHTML;
};

watch(() => props.product.id, () => {
    activeImageIndex.value = 0;
});

const page = usePage();

// 🎯 Dados de SEO
const seoData = computed(() => props.product.seo || {});

// Helper para limitar meta_title a 70 caracteres (padrão SEO)
const limitMetaTitle = (text) => {
    if (!text) return '';
    return text.length > 70 ? text.substring(0, 67) + '...' : text;
};

// meta_title derivado do product.description (limitado a 70 chars)
const metaTitle = computed(() => limitMetaTitle(props.product.description));

// 🖼️ Controle do Carrossel Manual
const activeImageIndex = ref(0);

const nextImage = () => {
    if (props.product.images?.length > 0) {
        activeImageIndex.value = (activeImageIndex.value + 1) % props.product.images.length;
    }
};

const prevImage = () => {
    if (props.product.images?.length > 0) {
        activeImageIndex.value = (activeImageIndex.value - 1 + props.product.images.length) % props.product.images.length;
    }
};

// 🛠️ Helper para URL da imagem
const getImageUrl = (path) => {
    if (!path) return null;
    const cleanPath = path.startsWith('products/') ? path : `products/${path}`;
    return `/storage/${cleanPath}`;
};

// 🔐 Admin & Status
const isAdmin = computed(() => page.props.auth?.user?.access_level === 1);
const isUpdating = ref(false);

const toggleStatus = () => {
    if (route().has('products.toggle')) {
        isUpdating.value = true;
        router.patch(route('products.toggle', props.product.id), {}, {
            preserveScroll: true,
            onFinish: () => isUpdating.value = false,
        });
    }
};

const formatCurrency = (value) => {
    // 3. Use Number() de forma direta e limpa
    return Number(value).toLocaleString('pt-BR', { 
        style: 'currency', 
        currency: 'BRL' 
    });
};
</script>

<template>
    <Head>
        <title>{{ metaTitle }}</title>
        <meta name="description" :content="seoData.meta_description" />
        <meta name="keywords" :content="seoData.meta_keywords" />
        <link rel="slug" :href="seoData.slug || page.url" />
        
        <component
            v-if="seoData.schema_markup"
            is="script"
            type="application/ld+json"
            v-html="sanitizeHtml(seoData.schema_markup)"
        />
    </Head>

    <StoreLayout>
        <div class="bg-indigo-600 text-white relative overflow-hidden">
            <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-between relative z-10">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 p-2 rounded-lg backdrop-blur-md">
                        <LayoutDashboard class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.2em] leading-none">Visualização de Loja</p>
                        <p class="text-[9px] font-medium opacity-80 uppercase tracking-widest mt-1">SEO e Galeria ativos para conferência</p>
                    </div>
                </div>
                
                <div v-if="isAdmin" class="flex items-center gap-4">
                    <button 
                        @click="toggleStatus"
                        :disabled="isUpdating"
                        class="bg-white text-indigo-600 px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2 shadow-lg hover:bg-indigo-50"
                    >
                        <Loader2 v-if="isUpdating" class="w-3 h-3 animate-spin" />
                        <component v-else :is="product.is_active ? EyeOff : Eye" class="w-3.5 h-3.5" />
                        {{ product.is_active ? 'Ocultar Produto' : 'Publicar Agora' }}
                    </button>
                </div>
            </div>
            <div class="absolute top-0 right-0 w-64 h-full bg-gradient-to-l from-white/10 to-transparent skew-x-12 transform translate-x-20"></div>
        </div>

        <div class="min-h-screen bg-[#F9FAFB] pb-24">
            <div class="max-w-6xl mx-auto px-6 py-8">
                <Link :href="route('products.index')" class="inline-flex items-center gap-2 text-slate-400 hover:text-indigo-600 text-[10px] font-black uppercase tracking-[0.2em] transition group">
                    <ArrowLeft class="w-4 h-4 group-hover:-translate-x-1 transition-transform" /> 
                    Voltar para Gestão
                </Link>
            </div>

            <main class="max-w-6xl mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-20 items-start">
                    
                    <div class="flex flex-col gap-6">
                        <div class="relative aspect-square bg-white rounded-[3rem] overflow-hidden flex items-center justify-center border border-gray-100 shadow-2xl shadow-gray-200/50 group">
                            <template v-if="product.images?.length > 0">
                                <img 
                                    :key="activeImageIndex"
                                    :src="getImageUrl(product.images[activeImageIndex].path)" 
                                    class="object-contain w-full h-full p-12 transition-all duration-500 animate-in fade-in zoom-in-95"
                                />
                                
                                <template v-if="product.images.length > 1">
                                    <button @click="prevImage" class="absolute left-6 top-1/2 -translate-y-1/2 bg-white/90 backdrop-blur p-3 rounded-full shadow-xl hover:scale-110 transition active:scale-95">
                                        <ChevronLeft class="w-6 h-6 text-gray-900" />
                                    </button>
                                    <button @click="nextImage" class="absolute right-6 top-1/2 -translate-y-1/2 bg-white/90 backdrop-blur p-3 rounded-full shadow-xl hover:scale-110 transition active:scale-95">
                                        <ChevronRight class="w-6 h-6 text-gray-900" />
                                    </button>
                                    <div class="absolute bottom-8 bg-black/5 px-4 py-1.5 rounded-full text-[10px] font-black text-gray-400 tracking-widest uppercase">
                                        {{ activeImageIndex + 1 }} / {{ product.images.length }}
                                    </div>
                                </template>
                            </template>

                            <div v-else class="flex flex-col items-center text-gray-200">
                                <Globe class="w-20 h-20 mb-2 opacity-20" />
                                <span class="text-[10px] font-black uppercase tracking-widest">Sem Imagem</span>
                            </div>
                        </div>

                        <div v-if="product.images?.length > 1" class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
                            <button 
                                v-for="(img, index) in product.images" 
                                :key="img.id"
                                @click="activeImageIndex = index"
                                class="w-20 h-20 shrink-0 rounded-2xl border-2 overflow-hidden bg-white p-2 transition-all duration-300"
                                :class="activeImageIndex === index ? 'border-indigo-600 ring-4 ring-indigo-50 scale-105 shadow-md' : 'border-transparent opacity-60 hover:opacity-100'"
                            >
                                <img :src="getImageUrl(img.path)" class="w-full h-full object-contain" />
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col py-4">
                        <nav class="text-[11px] uppercase font-black text-indigo-500 mb-4 tracking-[0.3em]">
                            {{ product.brand }} // {{ product.model }}
                        </nav>

                        <h1 class="text-4xl lg:text-5xl font-black text-gray-900 mb-6 leading-[0.9] tracking-tighter">
                            {{ seoData.h1 || product.description }}
                        </h1>

                        <div class="mb-10">
                            <div class="text-5xl font-black text-gray-900 tracking-tighter">
                                {{ formatCurrency(product.sale_price) }}
                            </div>
                        </div>

                        <div v-if="seoData.text1" class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm mb-10 relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-1.5 h-full bg-indigo-600"></div>
                            <p class="text-gray-600 text-[15px] italic leading-relaxed whitespace-pre-line">
                                "{{ seoData.text1 }}"
                            </p>
                        </div>

                        <button class="w-full bg-black text-white py-6 rounded-[2rem] font-black uppercase tracking-[0.2em] text-[11px] hover:bg-indigo-600 transition-all shadow-2xl flex items-center justify-center gap-3 active:scale-[0.98]">
                            <ShoppingCart class="w-5 h-5" />
                            Comprar Produto
                        </button>
                    </div>
                </div>

                <section class="mt-24 pt-20 border-t border-gray-200">
                    <div class="max-w-3xl">
                        <h2 class="text-2xl font-black text-gray-900 uppercase tracking-tighter mb-10">
                            {{ seoData.h2 || 'Especificações Técnicas' }}
                        </h2>
                        <div class="prose prose-indigo max-w-none text-gray-500 text-base leading-loose whitespace-pre-line font-medium">
                            {{ seoData.text2 || 'Detalhes não cadastrados.' }}
                        </div>
                    </div>
                </section>

                <section class="mt-32">
                    <div class="flex items-center gap-6 mb-12">
                        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter shrink-0">Relacionados</h3>
                        <div class="h-px w-full bg-gray-200"></div>
                    </div>

                    <div v-if="relatedProducts?.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-8">
                        <div v-for="item in relatedProducts" :key="item.id" class="group">
                            <Link :href="route('products.preview', item.id)" class="block">
                                <div class="aspect-square bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden mb-5 flex items-center justify-center p-8 group-hover:shadow-2xl transition-all duration-500 group-hover:-translate-y-2">
                                    <img v-if="item.images?.length > 0" 
                                         :src="getImageUrl(item.images[0].path)" 
                                         class="max-w-full max-h-full object-contain group-hover:scale-110 transition duration-500" />
                                    <Globe v-else class="w-12 h-12 text-gray-100" />
                                </div>
                                <p class="text-sm font-bold text-gray-800 truncate">{{ item.description }}</p>
                                <div class="text-base font-black text-gray-900 mt-2">{{ formatCurrency(item.sale_price) }}</div>
                            </Link>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </StoreLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
img { backface-visibility: hidden; transform: translateZ(0); }
</style>