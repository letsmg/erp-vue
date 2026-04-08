<script setup>
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import { debounce } from 'lodash';
import { 
    ShoppingBag, ChevronLeft, 
    ChevronRight, ShieldCheck, SearchX, ArrowUpDown, ChevronDown, Package, Loader2,
    FilterX
} from 'lucide-vue-next';

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

// Estado local para filtros (controlado totalmente local)
const localSearch = ref(props.filters?.search || '');
const localMaxPrice = ref(props.filters?.max_price || '');
const localBrand = ref(props.filters?.brand || '');
const localSortBy = ref(props.filters?.sort || 'created_at_desc');

// Estados do modal de termos (simulando useStoreIndex)
const showTermsModal = ref(false);
const termsAccepted = ref(false);

// Funções do modal
const acceptTerms = () => {
    if (!termsAccepted.value) return;
    
    localStorage.setItem('erp_terms_accepted', 'true');
    showTermsModal.value = false;
    
    // Faz POST para aceitar termos
    fetch('/terms/accept', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    });
};

// Funções para SearchSuggestions
const handleSearch = (term) => {
    localSearch.value = term;
    reloadProducts();
};

const handleSuggestionSelected = (suggestion) => {
    console.log('Sugestão selecionada:', suggestion);
    handleSearch(suggestion.term);
};

// Função scroll para carrossel (3 produtos por vez)
const scroll = (id, direction) => {
    const el = document.getElementById(id);
    if (!el) return;

    // Calcula a largura de um item (1/3 do container)
    const itemWidth = el.offsetWidth / 3;
    const isAtEnd = el.scrollLeft + el.offsetWidth >= el.scrollWidth - 10;

    if (direction === 'right' && isAtEnd) {
        el.scrollTo({ left: 0, behavior: 'smooth' });
    } else {
        const offset = direction === 'left' ? -itemWidth : itemWidth;
        el.scrollBy({ left: offset, behavior: 'smooth' });
    }
};

// SEO data
const seoData = ref({
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

// Lazy Loading Setup
onMounted(() => {
    // Verifica se o browser suporta lazy loading nativo
    if ('loading' in HTMLImageElement.prototype) {
        console.log('Native lazy loading supported');
    } else {
        // Fallback para browsers antigos
        setupLazyLoadingFallback();
    }
});

// Sincroniza filtros locais com useStoreIndex (sem sobrescrever com função)
// REMOVIDO para evitar que Inertia cause refresh
// watch(() => props.filters?.search, (newValue) => {
//     if (newValue && typeof newValue === 'string') {
//         localSearch.value = newValue;
//         reloadProducts(); // Recarrega sem rolar
//     }
// });

// watch(() => props.filters?.max_price, (newValue) => {
//     if (newValue && typeof newValue === 'string') {
//         localMaxPrice.value = newValue;
//         reloadProducts(); // Recarrega sem rolar
//     }
// });

// watch(() => props.filters?.brand, (newValue) => {
//     if (newValue && typeof newValue === 'string') {
//         localBrand.value = newValue;
//         reloadProducts(); // Recarrega sem rolar
//     }
// });

// watch(() => props.filters?.sort, (newValue) => {
//     if (newValue && typeof newValue === 'string') {
//         localSortBy.value = newValue;
//         reloadProducts(); // Recarrega sem rolar
//     }
// });

// Apenas watchers locais para controlar tudo
watch(localSearch, () => reloadProducts());
watch(localMaxPrice, () => reloadProducts());
watch(localBrand, () => reloadProducts());
watch(localSortBy, () => reloadProducts());

const setupLazyLoadingFallback = () => {
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazyload');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback para browsers muito antigos
        lazyImages.forEach(img => {
            img.src = img.dataset.src || img.src;
        });
    }
};

// Função Load More
const loadMoreProducts = async () => {
    if (isLoading.value || !hasMoreProducts.value) return;
    
    isLoading.value = true;
    
    try {
        const nextPage = currentPage.value + 1;
        
        // Debug dos valores
        console.log('DEBUG COMPLETO:', {
            'localSortBy.value': localSortBy.value,
            'typeof localSortBy.value': typeof localSortBy.value,
            'props.filters.sort': props.filters?.sort,
            'props.filters': props.filters,
            'localSortBy': localSortBy
        });
        
        // Constrói URL com filtros atuais
        const params = new URLSearchParams();
        if (localSearch.value) params.append('search', localSearch.value);
        if (localMaxPrice.value) params.append('max_price', localMaxPrice.value);
        if (localBrand.value) params.append('brand', localBrand.value);
        if (localSortBy.value && typeof localSortBy.value === 'string') {
            params.append('sort', localSortBy.value);
        }
        params.append('page', nextPage);
        
        const url = `/store/products?${params.toString()}`;
        console.log('URL sendo solicitada:', url);
        
        // Adicionando timestamp para evitar cache
        const timestampedUrl = `${url}&_t=${Date.now()}`;
        
        const response = await fetch(timestampedUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            }
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (response.ok) {
            const data = await response.json();
            console.log('Dados recebidos:', data);
            
            // Adiciona novos produtos à lista existente
            allProducts.value = [...allProducts.value, ...data.data];
            currentPage.value = data.current_page;
            hasMoreProducts.value = data.next_page_url !== null;
        } else {
            console.error('Erro na resposta:', response.status, response.statusText);
            console.error('Response text:', await response.text());
        }
    } catch (error) {
        console.error('Erro ao carregar mais produtos:', error);
    } finally {
        isLoading.value = false;
    }
};

// Função para determinar tempo de debounce baseado no tamanho da busca
const getDebounceTime = () => {
    const searchLength = localSearch.value.length;
    
    if (searchLength === 0) return 500; // Busca vazia
    if (searchLength <= 3) return 200; // Busca curta (Redis) - mais rápido
    return 500; // Busca longa (PostgreSQL) - mais lento
};

// Função para recarregar produtos com filtros (sem rolar)
const reloadProducts = debounce(async () => {
    isLoading.value = true;
    
    try {
        // Constrói URL com filtros atuais
        const params = new URLSearchParams();
        if (localSearch.value) params.append('search', localSearch.value);
        if (localMaxPrice.value) params.append('max_price', localMaxPrice.value);
        if (localBrand.value) params.append('brand', localBrand.value);
        if (localSortBy.value && typeof localSortBy.value === 'string') {
            params.append('sort', localSortBy.value);
        }
        params.append('page', 1); // Sempre página 1
        
        const url = `/store/products?${params.toString()}`;
        console.log('Recarregando produtos:', url);
        
        // Adicionando timestamp para evitar cache
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
            console.log('Produtos recarregados:', data);
            
            // Substitui todos os produtos
            allProducts.value = [...data.data];
            currentPage.value = data.current_page;
            hasMoreProducts.value = data.next_page_url !== null;
        } else {
            console.error('Erro ao recarregar produtos:', response.status, response.statusText);
        }
    } catch (error) {
        console.error('Erro ao recarregar produtos:', error);
    } finally {
        isLoading.value = false;
    }
}, getDebounceTime()); // Debounce dinâmico

// Função para limpar todos os filtros
const clearFilters = () => {
    localSearch.value = '';
    localMaxPrice.value = '';
    localBrand.value = '';
    localSortBy.value = 'created_at_desc';
    reloadProducts();
};
</script>

<template>
    <StoreLayout v-model:searchTerm="localSearch">
        <Head>
            <title>{{ seoData.title }}</title>
            <meta name="description" :content="seoData.description" />
        </Head>

        <header class="max-w-7xl mx-auto px-4 md:px-6 pt-10">
            <h1 class="text-4xl md:text-6xl font-black text-slate-900 tracking-tighter uppercase italic leading-none">
                {{ seoData.h1 }}
            </h1>
            <p class="text-slate-400 text-[10px] md:text-xs font-black mt-3 uppercase tracking-[0.4em]">
                {{ seoData.description }}
            </p>
        </header>

        <section v-if="featuredProducts?.length" class="max-w-7xl mx-auto px-4 md:px-6 mt-12">
            <div class="relative group">
                <div id="hero-carousel" class="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide gap-4 rounded-[2.5rem] md:rounded-[4rem] shadow-2xl">
                    <div v-for="p in featuredProducts" :key="p.slug" 
                         class="min-w-[85%] md:min-w-[33.333%] snap-center relative aspect-[16/9] md:aspect-[4/3] bg-blue-900 overflow-hidden rounded-[2rem] md:rounded-[3rem]">
                        <img :src="p.images?.[0] ? '/storage/products/' + p.images[0].path : 'https://placehold.co/1200x500'" 
                             class="w-full h-full object-cover opacity-40 transition-transform duration-1000 group-hover:scale-110 lazyload"
                             loading="lazy"
                             :alt="p.description" />
                        
                        <div class="absolute inset-0 flex flex-col justify-end md:justify-center px-6 md:px-8 text-white bg-gradient-to-t md:bg-gradient-to-r from-blue-900 via-blue-900/60 to-transparent pb-6 md:pb-0">
                            <span class="bg-primary w-fit px-3 py-1 rounded-full text-[9px] font-black uppercase mb-2 md:mb-3 tracking-[0.15em]">Destaque</span>
                            <h2 class="text-lg md:text-2xl font-black mb-2 tracking-tight leading-tight max-w-md uppercase italic line-clamp-2">{{ p.description }}</h2>
                            <p class="text-lg md:text-xl text-primary-hover mb-3 md:mb-4 font-mono font-bold">R$ {{ p.sale_price }}</p>
                            
                            <Link :href="route('store.product', p.slug)" 
                                  class="bg-white text-slate-900 px-4 md:px-6 py-2 md:py-3 rounded-xl font-black uppercase text-[10px] w-fit hover:bg-primary hover:text-white transition-all shadow-lg hover:-translate-y-1">
                                Ver Produto
                            </Link>
                        </div>
                    </div>
                </div>
                <button @click="scroll('hero-carousel', 'left')" class="absolute left-2 md:left-8 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white text-white hover:text-black p-2 md:p-5 rounded-full backdrop-blur-xl transition border border-white/20 shadow-lg">
                    <ChevronLeft class="w-5 h-5 md:w-6 md:h-6"/>
                </button>
                <button @click="scroll('hero-carousel', 'right')" class="absolute right-2 md:right-8 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white text-white hover:text-black p-2 md:p-5 rounded-full backdrop-blur-xl transition border border-white/20 shadow-lg">
                    <ChevronRight class="w-5 h-5 md:w-6 md:h-6"/>
                </button>
            </div>
        </section>

        <main class="max-w-7xl mx-auto px-4 md:px-6 py-16 flex flex-col md:flex-row gap-12">
            
            <aside class="w-full md:w-72">
                <div class="bg-white p-8 rounded-[3rem] border border-blue-100 shadow-sm md:sticky md:top-32 space-y-10">
                    <!-- Título dos Filtros -->
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
                        
                        <!-- Botão Limpar Filtros -->
                        <button 
                            @click="clearFilters"
                            class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-800 py-3 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center justify-center gap-2"
                        >
                            <FilterX class="w-4 h-4" />
                            Limpar Filtros
                        </button>
                    </div>
                </div>
            </aside>

            <section class="flex-1">
                
                <!-- Estatísticas da Paginação -->
                <div v-if="products.total > 12" class="mb-6 text-center">
                    <p class="text-sm text-slate-500 font-medium">
                        Mostrando 
                        <span class="font-black text-slate-900">{{ products.from || 0 }}</span> 
                        a 
                        <span class="font-black text-slate-900">{{ products.to || 0 }}</span> 
                        de 
                        <span class="font-black text-slate-900">{{ products.total }}</span> 
                        produtos
                    </p>
                </div>

                <div v-if="allProducts?.length" class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 lg:gap-8">
    
                    <Link 
                        v-for="product in allProducts" 
                        :key="product.slug + '-' + product.id"
                        :href="route('store.product', product.slug)"
                        class="group bg-white p-5 rounded-[2.5rem] md:rounded-[3.5rem] border border-white shadow-sm hover:shadow-2xl transition-all duration-700 block"
                    >
                        
                        <div class="relative aspect-[4/5] rounded-[2rem] md:rounded-[2.8rem] overflow-hidden bg-blue-100 mb-6">
                            <img 
                                :src="product.images?.[0] 
                                    ? '/storage/products/' + product.images[0].path 
                                    : 'https://placehold.co/600x800'" 
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 lazyload"
                                loading="lazy"
                                :alt="product.description"
                            />

                            <div class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
                                <div class="bg-white p-4 rounded-full scale-50 group-hover:scale-100 transition-transform duration-500 shadow-2xl">
                                    <ShoppingBag class="w-6 h-6 text-slate-900" />
                                </div>
                            </div>
                        </div>

                        <div class="px-3">
                            <h3 class="text-xs md:text-sm font-black uppercase truncate text-slate-800 tracking-tight">
                                {{ product.description }}
                            </h3>

                            <div class="flex items-center justify-between mt-2">
                                <p class="text-sm md:text-2xl font-black text-primary font-mono tracking-tighter">
                                    R$ {{ product.sale_price }}
                                </p>

                                <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest group-hover:text-primary transition-colors">
                                    Ver Mais
                                </span>
                            </div>
                        </div>

                    </Link>

                </div>

                <!-- Botão Carregar Mais -->
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

                <div v-else-if="allProducts?.length" class="text-center mt-12 mb-8">
                    <p class="text-slate-400 text-sm font-medium uppercase tracking-wider">
                        Você chegou ao fim! Mostrando todos os {{ allProducts.length }} de {{ products.total }} produtos
                    </p>
                </div>

                <div v-else class="text-center py-32 bg-white rounded-[4rem] border-4 border-dashed border-blue-50">
                    <SearchX class="w-16 h-16 text-blue-200 mx-auto mb-6" />
                    <p class="text-blue-400 font-black uppercase tracking-[0.3em] text-sm italic">Nenhum resultado para os filtros aplicados</p>
                </div>
            </section>
        </main>

        <Transition enter-active-class="duration-700 ease-out" enter-from-class="opacity-0 translate-y-10" enter-to-class="opacity-100 translate-y-0">
            <div v-if="showTermsModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-blue-900/95 backdrop-blur-2xl">
                <div class="bg-white w-full max-w-xl rounded-[4rem] p-12 shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-2 bg-primary"></div>
                    
                    <div class="w-20 h-20 bg-primary/5 text-primary rounded-3xl flex items-center justify-center mb-8">
                        <ShieldCheck class="w-10 h-10" />
                    </div>

                    <h2 class="text-3xl font-black text-slate-900 mb-6 uppercase italic tracking-tighter leading-none">Proteção de Dados & Auditoria</h2>
                    <p class="text-slate-500 text-sm mb-10 leading-relaxed font-medium">
                        Para sua segurança, registramos seu IP e atividades para fins de auditoria cibernética. 
                        Ao prosseguir, você concorda com nossos <a href="#" class="text-primary font-black underline decoration-2 underline-offset-4">termos de uso</a> e política de privacidade.
                    </p>

                    <label class="flex items-center gap-4 cursor-pointer mb-10 bg-blue-50 p-6 rounded-3xl border border-blue-100 hover:bg-primary/5 transition-colors group">
                        <input type="checkbox" v-model="termsAccepted" class="h-6 w-6 rounded-lg border-blue-300 text-primary focus:ring-primary transition-all" />
                        <span class="text-[11px] font-black text-blue-700 uppercase tracking-tight group-hover:text-primary">Compreendo e aceito as condições de monitoramento.</span>
                    </label>

                    <button @click="acceptTerms" :disabled="!termsAccepted"
                        class="w-full py-6 rounded-2xl font-black uppercase tracking-[0.2em] text-xs transition-all shadow-xl"
                        :class="termsAccepted ? 'bg-blue-900 text-white hover:bg-primary hover:-translate-y-1' : 'bg-blue-100 text-blue-300 cursor-not-allowed'">
                        Confirmar Acesso
                    </button>
                </div>
            </div>
        </Transition>

    </StoreLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.sticky {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>