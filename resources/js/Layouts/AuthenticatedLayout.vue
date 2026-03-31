<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard, Users, Package, LogOut, CheckCircle2, X, AlertTriangle,
    FileBarChart, ShoppingCart, Contact2, ChevronDown, Menu
} from 'lucide-vue-next';
import { ref, watch, onMounted, onUnmounted, computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// --- Controle de UI ---
const isMobileMenuOpen = ref(false);
const showReportsMenu = ref(page.url.startsWith('/reports'));
const toggleMobileMenu = () => isMobileMenuOpen.value = !isMobileMenuOpen.value;

// --- Lógica de Notificações (Toast) ---
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const triggerToast = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
    
    const duration = type === 'error' ? 6000 : 4000;
    setTimeout(() => { showToast.value = false; }, duration);
};

// Monitorar Mensagens de Sucesso (Flash)
watch(() => page.props.flash?.message, (newMessage) => {
    if (newMessage) triggerToast(newMessage, 'success');
}, { immediate: true });

// Monitorar Erros de Validação
const errors = computed(() => page.props.errors);
watch(errors, (newErrors) => {
    const errorKeys = Object.keys(newErrors);
    if (errorKeys.length > 0) {
        const firstErrorMessage = newErrors[errorKeys[0]];
        triggerToast(firstErrorMessage, 'error');
    }
}, { deep: true });

// --- Atalhos e Utilitários ---
const handleKeyDown = (e) => {
    if (e.ctrlKey && e.altKey && e.key === '1') {
        e.preventDefault();
        window.dispatchEvent(new CustomEvent('magic-fill'));
    }
    if (e.ctrlKey && e.altKey && e.key === '2') {
        e.preventDefault();
        window.dispatchEvent(new CustomEvent('magic-clear'));
    }
};

onMounted(() => window.addEventListener('keydown', handleKeyDown));
onUnmounted(() => window.removeEventListener('keydown', handleKeyDown));

const isUrl = (url) => page.url === url || page.url.startsWith(url + '/');

// Fecha menu mobile ao navegar
watch(() => page.url, () => isMobileMenuOpen.value = false);
</script>

<template>
    <div class="min-h-screen bg-blue-100 flex overflow-x-hidden font-sans text-slate-900">
        
        <Transition 
            enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false"
                 class="fixed inset-0 bg-indigo-950/60 z-40 md:hidden backdrop-blur-sm"></div>
        </Transition>

        <aside :class="[
            'fixed inset-y-0 left-0 w-64 bg-blue-950 text-white flex flex-col z-50 transition-transform duration-300 ease-in-out md:translate-x-0 shadow-2xl',
            isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full' 
        ]">
            <div class="p-6 border-b border-indigo-900/50 flex justify-between items-center">
                <span class="font-black text-xl tracking-tighter">ERP<span class="text-indigo-400">PRO</span></span>
                <button @click="isMobileMenuOpen = false" class="md:hidden p-1 hover:bg-indigo-900 rounded-lg transition-colors">
                    <X class="w-6 h-6"/>
                </button>
            </div>

            <nav class="flex-1 p-4 space-y-1 overflow-y-auto scrollbar-none [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden scroll-smooth">
                
                <Link :href="route('dashboard')" 
                    :class="[isUrl('/dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : ' hover:bg-indigo-900 hover:text-white']"
                    class="flex items-center p-3 rounded-xl transition-all duration-200 group font-medium">
                    <LayoutDashboard class="w-5 group-hover:scale-110 transition-transform"/>
                    <span class="ml-3">Dashboard</span>
                </Link>

                <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-400/50 mt-6 mb-2 px-3">Comercial</p>
                <div class="flex items-center gap-3 p-3 rounded-xl font-medium opacity-40 cursor-not-allowed ">
                    <Contact2 class="w-5"/> <span>Clientes</span>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-xl font-medium opacity-40 cursor-not-allowed ">
                    <ShoppingCart class="w-5"/> <span>Vendas</span>
                </div>

                <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-400/50 mt-6 mb-2 px-3">Logística</p>
                <Link :href="route('products.index')" 
                    :class="[isUrl('/products') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : ' hover:bg-indigo-900 hover:text-white']"
                    class="flex items-center gap-3 p-3 rounded-xl font-medium transition-all">
                    <Package class="w-5"/> <span>Produtos</span>
                </Link>

                <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-400/50 mt-6 mb-2 px-3">Gestão</p>
                <Link :href="route('users.index')" 
                    :class="[isUrl('/users') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : ' hover:bg-indigo-900 hover:text-white']"
                    class="flex items-center gap-3 p-3 rounded-xl font-medium transition-all">
                    <Users class="w-5"/> <span>Usuários</span>
                </Link>

                <div class="pt-2">
                    <button @click="showReportsMenu = !showReportsMenu"
                        class="flex items-center justify-between w-full p-3 rounded-xl  hover:bg-indigo-900 hover:text-white transition-all font-medium">
                        <div class="flex items-center gap-3">
                            <FileBarChart class="w-5"/>
                            <span>Relatórios</span>
                        </div>
                        <ChevronDown :class="{'rotate-180': showReportsMenu}" class="w-4 h-4 transition-transform duration-300"/>
                    </button>

                    <Transition
                        enter-active-class="transition duration-200 ease-out" enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100"
                        leave-active-class="transition duration-150 ease-in" leave-from-class="transform translate-y-0 opacity-100" leave-to-class="transform -translate-y-2 opacity-0">
                        <div v-if="showReportsMenu" class="ml-11 mt-1 space-y-1">
                            <Link
                                :href="route('reports.index')"
                                :class="[isUrl('/reports') ? 'text-white font-semibold' : 'text-indigo-400']"
                                class="block p-2 text-sm hover:text-white transition-colors"
                            >
                                Produtos
                            </Link>
                            <span class="block p-2 text-sm text-indigo-800 cursor-not-allowed italic">Vendas</span>
                        </div>
                    </Transition>
                </div>
            </nav>

            <div class="p-4 border-t border-indigo-900/50">
                <Link :href="route('logout')" method="post" as="button" 
                    class="flex items-center gap-3 w-full p-3 text-indigo-300 hover:text-white hover:bg-yellow-900 rounded-xl transition-all font-medium cursor-pointer">
                    <LogOut class="w-5"/> <span>Sair do Sistema</span>
                </Link>
            </div>
        </aside>

        <div class="flex-1 md:ml-64 flex flex-col min-w-0">
            <header class="h-16 bg-blue-900 border-b border-indigo-100 flex items-center justify-between px-6 sticky top-0 z-30 shadow-sm">
                <div class="flex items-center gap-4">
                    <button @click="toggleMobileMenu" class="md:hidden p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                        <Menu class="w-6 h-6"/>
                    </button>
                    <h2 class="hidden md:block text-xs font-bold text-indigo-300 uppercase tracking-widest text-white">Painel de Controle</h2>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold text-slate-900 leading-none mb-1 text-white">{{ user.name }}</p>
                        <p class="text-[9px] text-indigo-500 font-black uppercase tracking-tighter bg-indigo-50 px-2 py-0.5 rounded-md inline-block">Admin</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-black shadow-lg shadow-indigo-600/20 ring-4 ring-indigo-50">
                        {{ user.name.charAt(0) }}
                    </div>
                </div>
            </header>

            <main class="p-4 md:p-8 flex-1">
                <slot />
            </main>
        </div>

        <Transition 
            enter-active-class="transform transition duration-500 ease-out" 
            enter-from-class="translate-y-20 opacity-0 scale-90" 
            enter-to-class="translate-y-0 opacity-100 scale-100"
            leave-active-class="transition duration-300 ease-in" 
            leave-from-class="opacity-100 scale-100" 
            leave-to-class="opacity-0 scale-90">
            <div v-if="showToast" class="fixed bottom-8 right-8 z-[100] w-full max-w-sm px-4 sm:px-0">
                <div :class="[
                    'p-4 rounded-3xl shadow-2xl border flex items-center gap-4 backdrop-blur-xl',
                    toastType === 'success' ? 'bg-white/90 border-emerald-200 shadow-emerald-950/5' : 'bg-white/90 border-red-200 shadow-red-950/5'
                ]">
                    <div :class="['p-2.5 rounded-2xl shrink-0 shadow-lg', toastType === 'success' ? 'bg-emerald-500 shadow-emerald-500/40' : 'bg-red-500 shadow-red-500/40']">
                        <CheckCircle2 v-if="toastType === 'success'" class="w-5 h-5 text-white" />
                        <AlertTriangle v-else class="w-5 h-5 text-white" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">
                            {{ toastType === 'success' ? 'Operação Concluída' : 'Atenção' }}
                        </p>
                        <p class="text-sm font-bold text-slate-800 leading-tight">{{ toastMessage }}</p>
                    </div>
                    <button @click="showToast = false" class="p-2 hover:bg-slate-100 rounded-xl transition-colors text-slate-400">
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </Transition>

    </div>
</template>