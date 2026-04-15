<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, ShoppingBag, ShieldCheck, SearchX, ArrowUpDown, ChevronDown, Package, Loader2, FilterX } from 'lucide-vue-next';
import { ref, computed, watch, onMounted, onUnmounted, inject } from 'vue';
import { debounce } from 'lodash-es';
import StoreLayout from '@/Layouts/StoreLayout.vue';

const theme = inject('theme');
const page = usePage();

const props = defineProps({
    products: Object,
    featuredProducts: Array,
    onSaleProducts: Array,
    brands: Array,
    filters: Object
});

// Estado para Load More
const allProducts = ref([...props.products.data]);
const currentPage = ref(props.products.current_page);
const lastPage = ref(props.products.last_page);
const isLoading = ref(false);
const hasMoreProducts = ref(props.products.next_page_url !== null);

// Estado local para filtros
const localSearch = ref(props.filters?.search || '');
const localMaxPrice = ref(props.filters?.max_price || '');
const localBrand = ref(props.filters?.brand || '');
const localSortBy = ref(props.filters?.sort || 'created_at_desc');

// Sincroniza com URL ao carregar
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const searchFromUrl = urlParams.get('search');
    
    if (searchFromUrl && searchFromUrl !== localSearch.value) {
        localSearch.value = searchFromUrl;
    }
});

// Watch para sincronizar com StoreLayout (apenas filtros, não busca automática)
// Removido watch do localSearch para não buscar ao digitar
watch(localMaxPrice, () => reloadProducts());
watch(localBrand, () => reloadProducts());
watch(localSortBy, () => reloadProducts());

// Função para recarregar produtos
const reloadProducts = debounce(async () => {
    isLoading.value = true;
    
    try {
        const params = new URLSearchParams();
        if (localSearch.value) params.append('search', localSearch.value);
        if (localMaxPrice.value) params.append('max_price', localMaxPrice.value);
        if (localBrand.value) params.append('brand', localBrand.value);
        if (localSortBy.value && typeof localSortBy.value === 'string') {
            params.append('sort', localSortBy.value);
        }
        params.append('page', 1);
        
        const url = `/store/products?${params.toString()}`;
        const timestampedUrl = `${url}&_t=${Date.now()}`;
        
        const response = await fetch(timestampedUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            allProducts.value = [...data.data];
            currentPage.value = data.current_page;
            hasMoreProducts.value = data.next_page_url !== null;
        }
    } catch (error) {
        console.error('Erro ao recarregar produtos:', error);
    } finally {
        isLoading.value = false;
    }
}, 500);

// Função Load More
const loadMoreProducts = async () => {
    if (isLoading.value || !hasMoreProducts.value) return;
    
    isLoading.value = true;
    
    try {
        const nextPage = currentPage.value + 1;
        
        const params = new URLSearchParams();
        if (localSearch.value) params.append('search', localSearch.value);
        if (localMaxPrice.value) params.append('max_price', localMaxPrice.value);
        if (localBrand.value) params.append('brand', localBrand.value);
        if (localSortBy.value && typeof localSortBy.value === 'string') {
            params.append('sort', localSortBy.value);
        }
        params.append('page', nextPage);
        
        const url = `/store/products?${params.toString()}`;
        const timestampedUrl = `${url}&_t=${Date.now()}`;
        
        const response = await fetch(timestampedUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            allProducts.value = [...allProducts.value, ...data.data];
            currentPage.value = data.current_page;
            hasMoreProducts.value = data.next_page_url !== null;
        }
    } catch (error) {
        console.error('Erro ao carregar mais produtos:', error);
    } finally {
        isLoading.value = false;
    }
};

// Função para limpar todos os filtros
const clearFilters = () => {
    localSearch.value = '';
    localMaxPrice.value = '';
    localBrand.value = '';
    localSortBy.value = 'created_at_desc';
    reloadProducts();
};

// SEO data from page props
const seoData = computed(() => page.props.store_seo ?? {
    title: "Vitrine Premium | ERP Vue Laravel",
    description: "Explore nossa seleção exclusiva de produtos.",
    h1: "Catálogo de Produtos"
});

// Função para obter URL da imagem
const getImageUrl = (path) => {
    if (!path) return 'https://placehold.co/600x800';
    return path.startsWith('http') ? path : `/storage/products/${path}`;
};

// Opções de ordenação
const sortOptions = [
    { value: 'created_at_desc', label: 'Mais recentes' },
    { value: 'best_selling', label: 'Mais vendidos' },
    { value: 'sale_price_asc', label: 'Menor preço' },
    { value: 'sale_price_desc', label: 'Maior preço' },
    { value: 'promo_price_asc', label: 'Melhores promoções' },
    { value: 'description_asc', label: 'Nome (A-Z)' },
    { value: 'description_desc', label: 'Nome (Z-A)' },
    { value: 'created_at_asc', label: 'Mais antigos' },
];

// Função handleSearch removida - busca agora é feita via refresh completo da página

// Função scroll para carrossel
const scroll = (id, direction) => {
    const el = document.getElementById(id);
    if (!el) return;

    const isMobile = window.innerWidth < 768;
    const itemWidth = isMobile ? el.offsetWidth * 0.85 : el.offsetWidth / 3;
    const currentPos = el.scrollLeft;

    if (direction === 'right') {
        const isAtEnd = el.scrollLeft + el.offsetWidth >= el.scrollWidth - 15;
        if (isAtEnd) {
            el.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            el.scrollTo({ left: currentPos + itemWidth, behavior: 'smooth' });
        }
    } else {
        el.scrollTo({ left: currentPos - itemWidth, behavior: 'smooth' });
    }
};
</script>

<template>
    <StoreLayout :searchTerm="localSearch" @update:searchTerm="localSearch = $event">
        <Head>
            <title>{{ seoData.title }}</title>
            <meta name="description" :content="seoData.description" />
            
            <!-- Open Graph / Facebook -->
            <meta property="og:type" content="website" />
            <meta property="og:title" :content="seoData.title" />
            <meta property="og:description" :content="seoData.description" />
            <meta property="og:url" :content="page.props.url" />
            
            <!-- Twitter -->
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" :content="seoData.title" />
            <meta name="twitter:description" :content="seoData.description" />
        </Head>

        <header class="max-w-7xl mx-auto px-4 md:px-6 pt-10">
            <h1 class="text-4xl md:text-6xl font-black tracking-tighter uppercase italic leading-none" :class="theme === 'dark' ? 'text-white' : 'text-slate-900'">
                {{ seoData.h1 }}
            </h1>
            <p class="text-[10px] md:text-xs font-black mt-3 uppercase tracking-[0.4em" :class="theme === 'dark' ? 'text-white' : 'text-slate-900'">
                {{ seoData.description }}
            </p>
        </header>

        <section v-if="featuredProducts?.length" class="max-w-7xl mx-auto px-4 md:px-6 mt-12">
            <div class="relative group">
                <div id="hero-carousel" class="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide gap-4 rounded-[2.5rem] md:rounded-[4rem] shadow-2xl scroll-smooth">
                    <div v-for="p in featuredProducts" :key="p.slug" 
                        class="min-w-[85%] md:min-w-[33.333%] snap-center relative aspect-[16/9] md:aspect-[4/3] overflow-hidden rounded-[2rem] md:rounded-[3rem] isolate border border-transparent">
                        
                        <img :src="p.images?.[0] ? '/storage/products/' + p.images[0].path : 'https://placehold.co/1200x500'" 
                            class="w-full h-full object-cover opacity-100 transition-transform duration-1000 group-hover:scale-110 lazyload"
                            style="backface-visibility: hidden; transform: translateZ(0);"
                            loading="lazy" 
                            :alt="p.description" />
                        
                        <div class="absolute inset-0 flex flex-col justify-end md:justify-center px-6 md:px-8 text-white bg-gradient-to-t md:bg-gradient-to-r from-black/80 via-black/50 to-transparent pb-6 md:pb-0">
                            <span class="bg-primary w-fit px-3 py-1 rounded-full text-[9px] font-black uppercase mb-2 md:mb-3 tracking-[0.15em]">Destaque</span>
                            <h2 class="text-lg md:text-2xl font-black mb-2 tracking-tight leading-tight max-w-md uppercase italic line-clamp-2">{{ p.description }}</h2>
                            <p class="text-lg md:text-xl mb-3 md:mb-4 font-mono font-bold" :class="theme === 'dark' ? 'text-indigo-400' : 'text-primary-hover'">R$ {{ p.sale_price }}</p>
                            
                            <Link :href="route('store.product', p.slug)" 
                                class="bg-white text-slate-900 px-4 md:px-6 py-2 md:py-3 rounded-xl font-black uppercase text-[10px] w-fit hover:bg-primary hover:text-white transition-all shadow-lg hover:-translate-y-1">
                                Ver Produto
                            </Link>
                        </div>
                    </div>
                </div>

                <button @click="scroll('hero-carousel', 'left')" class="absolute left-2 md:left-8 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white text-white hover:text-black p-2 md:p-5 rounded-full backdrop-blur-xl transition border border-white/20 shadow-lg z-20">
                    <ChevronLeft class="w-5 h-5 md:w-6 md:h-6"/>
                </button>
                <button @click="scroll('hero-carousel', 'right')" class="absolute right-2 md:right-8 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white text-white hover:text-black p-2 md:p-5 rounded-full backdrop-blur-xl transition border border-white/20 shadow-lg z-20">
                    <ChevronRight class="w-5 h-5 md:w-6 md:h-6"/>
                </button>
            </div>
        </section>

        <main class="max-w-7xl mx-auto px-4 md:px-6 py-16 flex flex-col md:flex-row gap-12">
            
            <aside class="w-full md:w-72">
                <div class="bg-white p-8 rounded-[3rem] border border-blue-100 shadow-sm md:sticky md:top-32 space-y-10">
                    <h3 class="text-sm font-black uppercase text-blue-900 tracking-wider flex items-center gap-2">
                        <Package class="w-4 h-4" />
                        Filtros
                    </h3>
                    
                    <div class="space-y-6">
                        <div class="group">
                            <label class="text-[10px] font-black uppercase text-blue-900 mb-2 block ml-1 tracking-wider">Preço Limite</label>
                            <input v-model="localMaxPrice" type="number" placeholder="Até R$" 
                                class="w-full bg-blue-50 border-none rounded-2xl text-xs font-bold p-4 focus:ring-2 focus:ring-blue-500 transition-all" />
                        </div>
                        
                        <div>
                            <label class="text-[10px] font-black uppercase text-blue-900 mb-2 block ml-1 tracking-wider">Marca</label>
                            <select v-model="localBrand" class="w-full bg-blue-50 border-none rounded-2xl text-xs font-bold p-4 focus:ring-2 focus:ring-blue-500 transition-all">
                                <option value="">Todas as Marcas</option>
                                <option v-for="b in brands" :key="b" :value="b">{{ b }}</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="text-[10px] font-black uppercase text-blue-900 mb-2 block ml-1 tracking-wider">Ordenar por</label>
                            <div class="relative">
                                <ArrowUpDown class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-400" />
                                <select 
                                    v-model="localSortBy"
                                    class="w-full bg-blue-50 border-none rounded-2xl text-xs font-bold p-4 pl-10 focus:ring-2 focus:ring-blue-500 transition-all appearance-none cursor-pointer"
                                >
                                    <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <button 
                            @click="clearFilters"
                            class="w-full py-3 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center justify-center gap-2"
                            :class="theme === 'dark' ? 'bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-800'"
                        >
                            <FilterX class="w-4 h-4" />
                            Limpar Filtros
                        </button>
                    </div>
                </div>
            </aside>

            <section class="flex-1">
                <div v-if="allProducts?.length" class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 lg:gap-8">
                    <Link 
                        v-for="product in allProducts" 
                        :key="product.slug + '-' + product.id"
                        :href="route('store.product', product.slug)"
                        class="group p-5 rounded-[2.5rem] md:rounded-[3.5rem] border shadow-sm hover:shadow-2xl transition-all duration-700 block"
                        :class="theme === 'dark' ? 'bg-slate-800 border-slate-700' : 'bg-white border-white'"
                    >
                        <div class="relative aspect-[4/5] rounded-[2rem] md:rounded-[2.8rem] overflow-hidden bg-blue-100 mb-6">
                            <img 
                                :src="product.images?.[0] 
                                    ? '/storage/products/' + product.images[0].path 
                                    : 'https://placehold.co/600x800'" 
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 lazyload"
                                loading="lazy"
                                :alt="product.title"
                            />

                            <div class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
                                <div class="bg-white p-4 rounded-full scale-50 group-hover:scale-100 transition-transform duration-500 shadow-2xl">
                                    <ShoppingBag class="w-6 h-6 text-slate-900" />
                                </div>
                            </div>
                        </div>

                        <div class="px-3">
                            <h3 class="text-xs md:text-sm font-black uppercase truncate tracking-tight" :class="theme === 'dark' ? 'text-white' : 'text-slate-800'">
                                {{ product.title }}
                            </h3>

                            <div class="flex items-center justify-between mt-2">
                                <p class="text-sm md:text-2xl font-black font-mono tracking-tighter" :class="theme === 'dark' ? 'text-indigo-400' : 'text-primary'">
                                    R$ {{ product.sale_price }}
                                </p>

                                <span class="text-[8px] font-black uppercase tracking-widest group-hover:text-primary transition-colors" :class="theme === 'dark' ? 'text-slate-400' : 'text-slate-300'">
                                    Ver Mais
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-if="hasMoreProducts" class="text-center mt-12 mb-8">
                    <button 
                        @click="loadMoreProducts"
                        :disabled="isLoading"
                        class="inline-flex items-center gap-3 bg-gradient-to-r from-red-600 to-red-700 text-white px-8 py-4 rounded-2xl font-black text-sm uppercase tracking-wider hover:from-red-700 hover:to-red-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    >
                        <Loader2 v-if="isLoading" class="w-5 h-5 animate-spin" />
                        <Package v-else class="w-5 h-5" />
                        {{ isLoading ? 'Carregando...' : 'Carregar Mais Produtos' }}
                        <ChevronDown class="w-5 h-5" />
                    </button>
                </div>

                <div v-else class="text-center py-32 rounded-[4rem] border-4 border-dashed" :class="theme === 'dark' ? 'bg-slate-800 border-slate-700' : 'bg-white border-blue-50'">
                    <SearchX class="w-16 h-16 mx-auto mb-6" :class="theme === 'dark' ? 'text-slate-600' : 'text-blue-200'" />
                    <p class="font-black uppercase tracking-[0.3em] text-sm italic" :class="theme === 'dark' ? 'text-slate-400' : 'text-blue-400'">Nenhum resultado para os filtros aplicados</p>
                </div>
            </section>
        </main>
    </StoreLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>