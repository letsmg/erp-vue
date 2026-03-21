<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { 
    LayoutDashboard, Users, Package, Truck, LogOut, CheckCircle2, X, AlertTriangle,
    FileBarChart, Cloud, ShoppingCart, Contact2, ClipboardList, MessageSquare, Menu 
} from 'lucide-vue-next';
import { ref, watch, onMounted, onUnmounted, computed } from 'vue';

const page = usePage();
const user = page.props.auth.user;

// --- Controle do Menu Mobile ---
const isMobileMenuOpen = ref(false);
const toggleMobileMenu = () => isMobileMenuOpen.value = !isMobileMenuOpen.value;

// Fecha o menu automaticamente ao navegar
watch(() => page.url, () => { isMobileMenuOpen.value = false; });

// --- Lógica de Notificações (Toast) ---
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success'); 

const triggerToast = () => {
    showToast.value = true;
    const duration = toastType.value === 'error' ? 6000 : 4000;
    setTimeout(() => { showToast.value = false; }, duration);
};

watch(() => page.props.flash?.message, (newMessage) => {
    if (newMessage) {
        toastType.value = 'success';
        toastMessage.value = newMessage;
        triggerToast();
    }
}, { immediate: true });

const errors = computed(() => page.props.errors);
watch(errors, (newErrors) => {
    if (Object.keys(newErrors).length > 0) {
        toastType.value = 'error';
        toastMessage.value = Object.values(newErrors)[0];
        triggerToast();
    }
}, { deep: true });

// --- Atalhos e Utilitários ---
const handleKeyDown = (e) => {
    if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'p') {
        e.preventDefault();
        window.dispatchEvent(new CustomEvent('magic-fill'));
    }
};

onMounted(() => { window.addEventListener('keydown', handleKeyDown); });
onUnmounted(() => { window.removeEventListener('keydown', handleKeyDown); });

const isUrl = (url) => page.url === url || page.url.startsWith(url + '/');
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex overflow-x-hidden">
        
        <Transition enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-30 md:hidden"></div>
        </Transition>

        <aside :class="[
            'fixed inset-y-0 left-0 w-64 bg-slate-950 text-white flex flex-col shadow-2xl z-40 transition-transform duration-300 ease-in-out md:translate-x-0',
            isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
        ]">
            <div class="p-6 border-b border-slate-900 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-indigo-600 rounded-lg flex items-center justify-center text-lg font-black shadow-lg shadow-indigo-500/20">Z</div>
                    <div class="flex flex-col">
                        <span class="text-sm font-black tracking-tighter leading-none">ERP ZENITE</span>
                        <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest mt-1">SaaS Edition</span>
                    </div>
                </div>
                <button @click="isMobileMenuOpen = false" class="md:hidden text-slate-500 hover:text-white transition-colors">
                    <X class="w-6 h-6" />
                </button>
            </div>
            
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto custom-scrollbar text-sm">
                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] px-3 mb-4 mt-2">Navegação</p>
                
                <Link :href="route('dashboard')" :class="[isUrl('/dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-slate-900 hover:text-slate-100']" class="flex items-center space-x-3 p-3 rounded-xl transition-all group">
                    <LayoutDashboard class="w-5 h-5" />
                    <span class="font-bold">Dashboard</span>
                </Link>

                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] px-3 mb-4 mt-8">Comercial</p>
                <Link :href="route('clients.index')" :class="[isUrl('/clients') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-slate-900 hover:text-slate-100']" class="flex items-center space-x-3 p-3 rounded-xl transition-all group">
                    <Contact2 class="w-5 h-5" /> <span class="font-bold">Clientes</span>
                </Link>
                <Link :href="route('sales.index')" :class="[isUrl('/sales') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-slate-900 hover:text-slate-100']" class="flex items-center space-x-3 p-3 rounded-xl transition-all group">
                    <ShoppingCart class="w-5 h-5" /> <span class="font-bold">Vendas</span>
                </Link>
                <Link :href="route('messages.index')" :class="[isUrl('/messages') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-slate-900 hover:text-slate-100']" class="flex items-center space-x-3 p-3 rounded-xl transition-all group">
                    <MessageSquare class="w-5 h-5" /> <span class="font-bold">Mensagens</span>
                </Link>

                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] px-3 mb-4 mt-8">Logística</p>
                <Link :href="route('products.index')" :class="[isUrl('/products') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-slate-900 hover:text-slate-100']" class="flex items-center space-x-3 p-3 rounded-xl transition-all group">
                    <Package class="w-5 h-5" /> <span class="font-bold">Produtos</span>
                </Link>
                <Link :href="route('suppliers.index')" :class="[isUrl('/suppliers') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-slate-900 hover:text-slate-100']" class="flex items-center space-x-3 p-3 rounded-xl transition-all group">
                    <Truck class="w-5 h-5" /> <span class="font-bold">Fornecedores</span>
                </Link>

                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] px-3 mb-4 mt-8">Gestão</p>
                <Link :href="route('users.index')" :class="[isUrl('/users') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-slate-900 hover:text-slate-100']" class="flex items-center space-x-3 p-3 rounded-xl transition-all group">
                    <Users class="w-5 h-5" /> <span class="font-bold">Usuários</span>
                </Link>
                <Link :href="route('reports.index')" :class="[isUrl('/reports') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-slate-900 hover:text-slate-100']" class="flex items-center space-x-3 p-3 rounded-xl transition-all group">
                    <FileBarChart class="w-5 h-5" /> <span class="font-bold">Relatórios</span>
                </Link>
            </nav>

            <div class="p-4 border-t border-slate-900 bg-slate-950/50">
                <Link :href="route('logout')" method="post" as="button" class="flex items-center space-x-3 p-3 w-full text-slate-500 hover:text-red-400 hover:bg-red-500/5 rounded-xl transition-all font-bold group cursor-pointer">
                    <LogOut class="w-5 h-5 group-hover:-translate-x-1 transition-transform" />
                    <span>Sair</span>
                </Link>
            </div>
        </aside>

        <div class="flex-1 flex flex-col md:ml-64 w-full min-w-0 transition-all duration-300">
            
            <header class="bg-white/80 backdrop-blur-md h-16 flex items-center justify-between px-4 md:px-8 sticky top-0 z-20 border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <button @click="toggleMobileMenu" class="p-2 bg-gray-100 rounded-lg text-gray-600 md:hidden hover:bg-gray-200 transition-colors">
                        <Menu class="w-6 h-6" />
                    </button>
                    <h1 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] truncate max-w-[150px] sm:max-w-none">
                        {{ $page.component.split('/').pop() }}
                    </h1>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="text-right hidden xs:block">
                        <p class="text-xs font-black text-gray-900 leading-none">{{ user.name }}</p>
                        <p class="text-[8px] text-indigo-600 mt-1 uppercase font-black tracking-widest">
                            {{ user.access_level === 1 ? 'Admin' : 'Operador' }}
                        </p>
                    </div>
                    <div class="w-9 h-9 bg-indigo-100 text-indigo-700 flex items-center justify-center rounded-lg font-black border border-indigo-200 uppercase text-xs">
                        {{ user.name.substring(0,2) }}
                    </div>
                </div>
            </header>

            <main class="p-4 md:p-8 flex-1 animate-in fade-in duration-500">
                <slot />
            </main>
        </div>

        <Transition enter-active-class="transform ease-out duration-300 transition" enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4" enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showToast" class="fixed bottom-4 right-4 left-4 sm:left-auto z-[100] flex items-center bg-gray-950 text-white p-1 pr-6 rounded-2xl shadow-2xl border border-white/10 overflow-hidden sm:min-w-[350px]">
                <div :class="['p-4 rounded-xl text-white mr-4', toastType === 'success' ? 'bg-emerald-500' : 'bg-red-500']">
                    <CheckCircle2 v-if="toastType === 'success'" class="w-6 h-6" />
                    <AlertTriangle v-else class="w-6 h-6" />
                </div>
                <div class="flex-1 min-w-0">
                    <p :class="['text-[10px] font-black uppercase tracking-widest', toastType === 'success' ? 'text-emerald-400' : 'text-red-400']">
                        {{ toastType === 'success' ? 'Sucesso' : 'Erro' }}
                    </p>
                    <p class="text-sm font-bold truncate">{{ toastMessage }}</p>
                </div>
                <button @click="showToast = false" class="ml-2 p-1 hover:bg-white/10 rounded-lg"><X class="w-4 h-4 text-gray-500" /></button>
                <div :class="['absolute bottom-0 left-0 h-1 animate-shrink', toastType === 'success' ? 'bg-emerald-500' : 'bg-red-500']" :style="{ animationDuration: toastType === 'error' ? '6s' : '4s' }"></div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
@keyframes shrink { from { width: 100%; } to { width: 0%; } }
.animate-shrink { animation-name: shrink; animation-timing-function: linear; animation-fill-mode: forwards; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }

/* Suporte para ecrãs muito pequenos */
@media (max-width: 380px) {
    .xs\:block { display: none; }
}
</style>