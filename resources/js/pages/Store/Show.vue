<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { 
    ArrowLeft, ShoppingCart, Globe, Star, Loader2, 
    Eye, EyeOff, LayoutDashboard, ChevronLeft, ChevronRight,
    Tag, ShieldCheck, Truck
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import StoreLayout from '@/Layouts/StoreLayout.vue'; 

const props = defineProps({
    product: Object,
    relatedProducts: Array    
});

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

// h1 derivado do product.description (sem limite)
const h1Text = computed(() => props.product.description || '');

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

// 💰 Lógica de Promoção
const isPromoActive = computed(() => {
    if (!props.product.promo_price) return false;
    const now = new Date();
    const start = props.product.promo_start_at ? new Date(props.product.promo_start_at) : null;
    const end = props.product.promo_end_at ? new Date(props.product.promo_end_at) : null;
    if (start && now < start) return false;
    if (end && now > end) return false;
    return true;
});

const getImageUrl = (path) => {
    if (!path) return null;
    // Garante que não duplique "products/products/"
    const cleanPath = path.startsWith('products/') ? path : `products/${path}`;
    return `/storage/${cleanPath}`;
};

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
    return Number(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};
</script>

<template>
    <Head>
        <title>{{ metaTitle }}</title>
        <meta name="description" :content="seoData.meta_description" />
        <meta name="keywords" :content="seoData.meta_keywords" />
        <link rel="slug" :href="seoData.slug_url || page.url" />
        <component v-if="seoData.schema_markup" is="script" type="application/ld+json" v-html="seoData.schema_markup" />
    </Head>

    <StoreLayout>
        <div v-if="isAdmin" class="bg-indigo-600 text-white relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 py-3 flex items-center justify-between relative z-10">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 p-2 rounded-lg backdrop-blur-md">
                        <LayoutDashboard class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.2em] leading-none">Visualização de Admin</p>
                        <p class="text-[9px] font-medium opacity-80 uppercase tracking-widest mt-1">SEO e Status de Publicação</p>
                    </div>
                </div>
                
                <button @click="toggleStatus" :disabled="isUpdating"
                    class="bg-white text-indigo-600 px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2 shadow-lg hover:bg-indigo-50">
                    <Loader2 v-if="isUpdating" class="w-3 h-3 animate-spin" />
                    <component v-else :is="product.is_active ? EyeOff : Eye" class="w-3.5 h-3.5" />
                    {{ product.is_active ? 'Ocultar da Loja' : 'Publicar Produto' }}
                </button>
            </div>
        </div>

        <div class="min-h-screen pb-24">
            <div class="max-w-7xl mx-auto px-6 py-8">
                <Link :href="route('products.index')" class="inline-flex items-center gap-2 text-slate-400 hover:text-indigo-600 text-[10px] font-black uppercase tracking-[0.2em] transition group">
                    <ArrowLeft class="w-4 h-4 group-hover:-translate-x-1 transition-transform" /> 
                    Voltar para Loja
                </Link>
            </div>

            <main class="max-w-7xl mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-12 lg:gap-16 items-start">
                    
                    <div class="md:col-span-5 flex flex-col gap-6 sticky top-8">
                        <div class="relative aspect-square bg-white rounded-[2.5rem] overflow-hidden flex items-center justify-center border border-gray-100 shadow-2xl shadow-gray-200/40 group">
                            <div v-if="isPromoActive" class="absolute top-6 left-6 z-20 bg-red-600 text-white text-[10px] font-black px-4 py-2 rounded-full shadow-lg animate-pulse uppercase tracking-widest">
                                Oferta Especial
                            </div>

                            <template v-if="product.images?.length > 0">
                                <img :key="activeImageIndex" :src="getImageUrl(product.images[activeImageIndex].path)" 
                                    class="object-contain w-full h-full p-8 transition-all duration-700 animate-in fade-in zoom-in-95" />
                                
                                <template v-if="product.images.length > 1">
                                    <button @click="prevImage" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/90 backdrop-blur p-2 rounded-full shadow-xl hover:bg-indigo-600 hover:text-white transition">
                                        <ChevronLeft class="w-5 h-5" />
                                    </button>
                                    <button @click="nextImage" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/90 backdrop-blur p-2 rounded-full shadow-xl hover:bg-indigo-600 hover:text-white transition">
                                        <ChevronRight class="w-5 h-5" />
                                    </button>
                                </template>
                            </template>
                            <Globe v-else class="w-20 h-20 text-gray-100" />
                        </div>

                        <div v-if="product.images?.length > 1" class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide justify-center">
                            <button v-for="(img, index) in product.images" :key="img.id" @click="activeImageIndex = index"
                                class="w-16 h-16 shrink-0 rounded-2xl border-2 overflow-hidden bg-white p-1 transition-all"
                                :class="activeImageIndex === index ? 'border-indigo-600 scale-105 shadow-md' : 'border-gray-100 opacity-50 hover:opacity-100'">
                                <img :src="getImageUrl(img.path)" class="w-full h-full object-contain" />
                            </button>
                        </div>
                    </div>

                    <div class="md:col-span-5 flex flex-col pt-2">
                        <nav class="text-[10px] uppercase font-black text-indigo-500 mb-3 tracking-[0.4em]">
                            {{ product.brand }} <span class="text-gray-300 mx-2">/</span> {{ product.model }}
                        </nav>

                        <h1 class="text-4xl lg:text-5xl font-black text-gray-900 mb-6 leading-none tracking-tighter">
                            {{ seoData.h1 || product.description }}
                        </h1>

                        <div class="mb-10 flex flex-col">
                            <template v-if="isPromoActive">
                                <span class="text-gray-400 line-through text-lg font-bold">{{ formatCurrency(product.sale_price) }}</span>
                                <div class="text-6xl font-black text-red-600 tracking-tighter">
                                    {{ formatCurrency(product.promo_price) }}
                                </div>
                            </template>
                            <div v-else class="text-6xl font-black text-gray-900 tracking-tighter">
                                {{ formatCurrency(product.sale_price) }}
                            </div>
                            <p class="text-[10px] font-black uppercase text-gray-400 mt-2 tracking-widest">Ou 10x sem juros no cartão</p>
                        </div>

                        <div v-if="seoData.text1" class="mb-10 border-l-4 border-indigo-600 pl-6 py-1">
                            <p class="text-gray-600 text-[16px] leading-relaxed font-medium">
                                {{ seoData.text1 }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-4 mb-12">
                            <button class="w-full bg-gray-900 text-white py-6 rounded-[2rem] font-black uppercase tracking-[0.2em] text-[11px] hover:bg-indigo-600 transition-all shadow-xl flex items-center justify-center gap-3 active:scale-95">
                                <ShoppingCart class="w-5 h-5" />
                                Adicionar ao Carrinho
                            </button>
                            
                            <div class="flex items-center justify-between px-4 py-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <div class="flex flex-col items-center gap-1">
                                    <Truck class="w-4 h-4 text-indigo-600" />
                                    <span class="text-[8px] font-black uppercase">Frete Rápido</span>
                                </div>
                                <div class="flex flex-col items-center gap-1 border-x border-gray-200 px-8">
                                    <ShieldCheck class="w-4 h-4 text-indigo-600" />
                                    <span class="text-[8px] font-black uppercase">Compra Segura</span>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <Tag class="w-4 h-4 text-indigo-600" />
                                    <span class="text-[8px] font-black uppercase">Melhor Preço</span>
                                </div>
                            </div>
                        </div>
                    </div>
        

                </div>

                <section class="mt-24 pt-20 border-t border-gray-100">
                    <div class="max-w-4xl mx-auto">
                        <div class="inline-block bg-indigo-600 text-white px-6 py-2 rounded-full text-[10px] font-black uppercase tracking-widest mb-6">
                            Ficha Técnica
                        </div>
                        <h2 class="text-3xl lg:text-4xl font-black text-gray-900 uppercase tracking-tighter mb-10">
                            {{ seoData.h2 || 'Especificações Completas' }}
                        </h2>
                        <div class="prose prose-indigo max-w-none text-gray-600 text-lg leading-relaxed whitespace-pre-line font-medium italic">
                            {{ seoData.text2 || 'O fabricante não disponibilizou detalhes técnicos adicionais para este modelo.' }}
                        </div>
                    </div>
                </section>

                <section class="mt-32">
                    <div class="flex items-center justify-between mb-12">
                        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter">Quem viu, gostou também</h3>
                        <div class="flex-1 h-px bg-gray-100 mx-8"></div>
                        <Link :href="route('products.index')" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:underline">Ver Tudo</Link>
                    </div>

                    <div v-if="relatedProducts?.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-8">
                        <div v-for="item in relatedProducts" :key="item.id" class="group">
                            <Link :href="route('products.preview', item.id)" class="block">
                                <div class="aspect-square bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden mb-5 flex items-center justify-center p-8 group-hover:shadow-2xl transition-all duration-500 group-hover:-translate-y-2 relative">
                                    <img v-if="item.images?.length > 0" 
                                         :src="getImageUrl(item.images[0].path)" 
                                         class="max-w-full max-h-full object-contain group-hover:scale-110 transition duration-500" />
                                    <Globe v-else class="w-12 h-12 text-gray-100" />
                                </div>
                                <p class="text-[11px] font-black text-gray-900 uppercase tracking-tight truncate px-2">{{ item.description }}</p>
                                <div class="text-sm font-black text-indigo-600 mt-1 px-2">{{ formatCurrency(item.sale_price) }}</div>
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
h1, h2, h3 { letter-spacing: -0.05em; }
</style>