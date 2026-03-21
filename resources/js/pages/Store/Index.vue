<script setup>
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useStoreIndex } from './useStoreIndex';
import { 
    SlidersHorizontal, 
    ShoppingBag, 
    ChevronLeft, 
    ChevronRight, 
    X, 
    ExternalLink 
} from 'lucide-vue-next';

const props = defineProps({
    products: Object,
    featuredProducts: Array,
    onSaleProducts: Array,
    ads: Array,
    brands: Array
});

const { search, minPrice, maxPrice, brand } = useStoreIndex(props);

// Lógica do Modal
const isModalOpen = ref(false);
const selectedProduct = ref(null);

const openDetails = (p) => { 
    selectedProduct.value = p; 
    isModalOpen.value = true; 
};

// Lógica do Carousel
let timer = null;
const scroll = (id, direction) => {
    const el = document.getElementById(id);
    if (!el) return;
    const isAtEnd = el.scrollLeft + el.offsetWidth >= el.scrollWidth - 10;
    if (direction === 'right' && isAtEnd) {
        el.scrollTo({ left: 0, behavior: 'smooth' });
    } else {
        const offset = direction === 'left' ? -el.offsetWidth : el.offsetWidth;
        el.scrollBy({ left: offset, behavior: 'smooth' });
    }
};

onMounted(() => { 
    timer = setInterval(() => scroll('hero-carousel', 'right'), 7000); 
});

onUnmounted(() => clearInterval(timer));
</script>

<template>
    <StoreLayout v-model:searchTerm="search">
        <Head title="Vitrine Premium" />

        <section v-if="featuredProducts?.length" class="max-w-7xl mx-auto px-4 md:px-6 mt-4 md:mt-8">
            <div class="relative group">
                <div id="hero-carousel" class="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide gap-4 rounded-[2rem] md:rounded-[3rem] shadow-2xl">
                    <div v-for="p in featuredProducts" :key="p.id" 
                         class="min-w-full snap-center relative aspect-[16/9] md:aspect-[21/9] bg-slate-900 overflow-hidden">
                        <img :src="p.images?.[0] ? '/storage/products/' + p.images[0].path : 'https://placehold.co/1200x500'" 
                             class="w-full h-full object-cover opacity-40 transition-transform duration-700 group-hover:scale-105" />
                        
                        <div class="absolute inset-0 flex flex-col justify-center px-6 md:px-12 text-white bg-gradient-to-r from-slate-900 via-slate-900/40 to-transparent">
                            <span class="bg-indigo-600 w-fit px-3 py-1 rounded-full text-[8px] md:text-[10px] font-black uppercase mb-2 md:mb-4 tracking-widest">Destaque</span>
                            <h2 class="text-2xl md:text-5xl font-black mb-1 md:mb-2 tracking-tighter leading-tight">{{ p.description }}</h2>
                            <p class="text-lg md:text-xl text-slate-300 mb-4 md:mb-6 font-medium">R$ {{ p.sale_price }}</p>
                            <button @click="openDetails(p)" class="bg-white text-slate-900 px-6 py-3 md:px-8 md:py-4 rounded-xl md:rounded-2xl font-black uppercase text-[10px] md:text-xs w-fit hover:bg-indigo-600 hover:text-white transition shadow-xl">
                                Ver Produto
                            </button>
                        </div>
                    </div>
                </div>
                <button @click="scroll('hero-carousel', 'left')" class="absolute left-6 top-1/2 -translate-y-1/2 bg-white/10 hover:bg-white text-white hover:text-black p-4 rounded-full backdrop-blur-md transition hidden md:group-hover:block border border-white/20">
                    <ChevronLeft/>
                </button>
                <button @click="scroll('hero-carousel', 'right')" class="absolute right-6 top-1/2 -translate-y-1/2 bg-white/10 hover:bg-white text-white hover:text-black p-4 rounded-full backdrop-blur-md transition hidden md:group-hover:block border border-white/20">
                    <ChevronRight/>
                </button>
            </div>
        </section>

        <main class="max-w-7xl mx-auto px-4 md:px-6 py-8 md:py-16 flex flex-col md:flex-row gap-8 md:gap-12">
            
            <aside class="w-full md:w-64">
                <div class="bg-white p-5 md:p-7 rounded-[2rem] md:rounded-[2.5rem] border border-slate-200 shadow-xl md:sticky md:top-28 space-y-6 md:space-y-8">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                        <SlidersHorizontal class="w-3 h-3" /> Filtrar
                    </h3>
                    <div class="flex flex-row md:flex-col gap-4">
                        <input v-model="maxPrice" type="number" placeholder="Preço até R$" class="w-1/2 md:w-full bg-slate-50 border-slate-100 rounded-xl text-[10px] md:text-xs font-bold p-3 outline-none focus:ring-2 focus:ring-indigo-500" />
                        <select v-model="brand" class="w-1/2 md:w-full bg-slate-50 border-slate-100 rounded-xl text-[10px] md:text-xs font-bold p-3 outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Todas as Marcas</option>
                            <option v-for="b in brands" :key="b" :value="b">{{ b }}</option>
                        </select>
                    </div>
                </div>
            </aside>

            <section class="flex-1 flex flex-col">
                
                <div v-if="products.links?.length > 3" class="mb-10 flex justify-center flex-wrap gap-2">
                    <Link v-for="(link, k) in products.links" :key="k"
                        :href="link.url || '#'" v-html="link.label"
                        class="px-4 py-2 rounded-xl text-[10px] font-black uppercase transition-all duration-300 border"
                        :class="{'bg-slate-900 text-white border-slate-900 shadow-lg': link.active, 'bg-white text-slate-400 border-slate-100 hover:bg-slate-50': !link.active, 'opacity-30 cursor-not-allowed': !link.url}"
                    />
                </div>

                <div v-if="products.data?.length" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-8">
                    <div v-for="product in products.data" :key="product.id" 
                         @click="openDetails(product)"
                         class="group bg-white p-3 md:p-5 rounded-[1.5rem] md:rounded-[3rem] border border-white shadow-md hover:shadow-2xl transition-all duration-500 cursor-pointer">
                        
                        <div class="relative aspect-[4/5] rounded-[1.2rem] md:rounded-[2.2rem] overflow-hidden bg-slate-100 mb-3 md:mb-5 shadow-inner">
                            <img :src="product.images?.[0] ? '/storage/products/' + product.images[0].path : 'https://placehold.co/600x800'" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-700" />
                        </div>
                        
                        <h3 class="text-[10px] md:text-sm font-black uppercase truncate text-slate-800 tracking-tight px-1">{{ product.description }}</h3>
                        <p class="text-sm md:text-xl font-black text-indigo-600 mt-1 md:mt-2 font-mono px-1">R$ {{ product.sale_price }}</p>
                    </div>
                </div>

                <div v-else class="text-center py-20 bg-white rounded-[2rem] md:rounded-[3rem] border-2 border-dashed border-slate-200">
                    <p class="text-slate-400 font-bold uppercase tracking-widest">Nenhum produto encontrado</p>
                </div>

                <div v-if="products.links?.length > 3" class="mt-16 flex justify-center flex-wrap gap-2">
                    <Link v-for="(link, k) in products.links" :key="'bottom-'+k"
                        :href="link.url || '#'" v-html="link.label"
                        class="px-5 py-3 rounded-2xl text-xs font-black uppercase transition-all duration-300 border"
                        :class="{'bg-indigo-600 text-white border-indigo-600 shadow-xl shadow-indigo-100': link.active, 'bg-white text-slate-500 border-slate-100 hover:border-indigo-200 hover:text-indigo-600': !link.active, 'opacity-30 cursor-not-allowed': !link.url}"
                    />
                </div>
            </section>
        </main>

        <Transition
            enter-active-class="duration-300 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100"
            leave-active-class="duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95"
        >
            <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-6 bg-slate-900/80 backdrop-blur-md" @click.self="isModalOpen = false">
                <div class="bg-white w-full max-w-4xl rounded-[2rem] md:rounded-[3rem] overflow-hidden shadow-2xl flex flex-col md:flex-row relative max-h-[90vh] overflow-y-auto md:overflow-hidden">
                    
                    <button @click="isModalOpen = false" class="absolute top-4 right-4 md:top-6 md:right-6 p-2 bg-slate-100 rounded-full hover:bg-red-50 text-slate-900 transition z-10">
                        <X class="w-5 h-5"/>
                    </button>

                    <div class="w-full md:w-1/2 bg-slate-100 aspect-square md:aspect-auto">
                        <img :src="selectedProduct?.images?.[0] ? '/storage/products/' + selectedProduct.images[0].path : 'https://placehold.co/600x800'" 
                             class="w-full h-full object-cover" />
                    </div>

                    <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
                        <span class="text-indigo-600 text-[10px] font-black uppercase tracking-[0.2em] mb-2">Nexus Exclusive</span>
                        <h2 class="text-2xl md:text-4xl font-black text-slate-900 mb-2 leading-tight">{{ selectedProduct?.description }}</h2>
                        
                        <Link 
                            v-if="selectedProduct?.seo?.slug"
                            :href="route('store.product', selectedProduct.seo.slug)"
                            class="flex items-center gap-1 text-[10px] font-bold text-slate-400 hover:text-indigo-600 transition mb-6 uppercase tracking-widest group"
                        >
                            Ver descrição completa
                            <ExternalLink class="w-3 h-3 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" />
                        </Link>

                        <p class="text-indigo-600 text-2xl md:text-3xl font-mono font-black mb-8">R$ {{ selectedProduct?.sale_price }}</p>
                        
                        <div class="space-y-3">
                            <button class="w-full bg-slate-900 text-white py-4 md:py-5 rounded-xl md:rounded-2xl font-black uppercase flex items-center justify-center gap-3 hover:bg-indigo-600 transition shadow-xl">
                                <ShoppingBag /> Adicionar à Sacola
                            </button>

                            <Link 
                                v-if="selectedProduct?.seo?.slug"
                                :href="route('store.product', selectedProduct.seo.slug)"
                                class="w-full bg-slate-50 text-slate-600 py-3 rounded-xl font-bold uppercase text-[10px] flex items-center justify-center gap-2 hover:bg-slate-100 transition"
                            >
                                Ficha Técnica & Detalhes
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </StoreLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>