<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { 
    Search, ShoppingBag, Cloud, User as UserIcon, 
    Settings, Package, LogOut, ChevronDown 
} from 'lucide-vue-next';
import { computed, ref, watch, nextTick } from 'vue';
import { onClickOutside } from '@vueuse/core';
import { debounce } from 'lodash-es';

const page = usePage();
const auth = computed(() => page.props.auth);

// Dropdown state
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

onClickOutside(dropdownRef, () => {
    isDropdownOpen.value = false;
});

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

// Estado da busca
const searchValue = ref('');
const suggestions = ref([]);
const showSuggestions = ref(false);
const suggestionsRef = ref(null);
const highlightedIndex = ref(-1);

// Recebe o valor da busca do Index
const props = defineProps({
    searchTerm: String
});

// Avisa o Index que o usuário digitou algo
const emit = defineEmits(['update:searchTerm']);

// Inicializa o valor do input com o searchTerm da prop
watch(() => props.searchTerm, (newValue) => {
    if (newValue && newValue !== searchValue.value) {
        searchValue.value = newValue;
    }
}, { immediate: true });

// Fecha sugestões ao clicar fora
onClickOutside(suggestionsRef, () => {
    showSuggestions.value = false;
});

// Busca sugestões com debounce
const fetchSuggestions = debounce(async (term) => {
    if (term.length < 2) {
        suggestions.value = [];
        showSuggestions.value = false;
        return;
    }

    try {
        const baseUrl = window.location.origin;
        const url = `${baseUrl}/search/suggestions?term=${encodeURIComponent(term)}`;
        
        const response = await fetch(url);
        const data = await response.json();
        
        suggestions.value = data.suggestions || [];
        showSuggestions.value = suggestions.value.length > 0;
        highlightedIndex.value = -1;
    } catch (error) {
        console.error('Erro ao buscar sugestões:', error);
        suggestions.value = [];
        showSuggestions.value = false;
    }
}, 300);

// Quando digita no campo
const handleInput = () => {
    fetchSuggestions(searchValue.value);
};

// Watch para emitir mudanças no input para o pai
watch(searchValue, (newValue) => {
    emit('update:searchTerm', newValue);
});

// Navegação com teclado
const handleKeydown = (event) => {
    // Enter sempre deve funcionar, mesmo sem sugestões
    if (event.key === 'Enter') {
        event.preventDefault();
        if (showSuggestions.value && suggestions.value.length > 0 && highlightedIndex.value >= 0) {
            // Se há sugestão selecionada, usa ela
            searchValue.value = suggestions.value[highlightedIndex.value].term;
            showSuggestions.value = false;
        }
        handleSearch();
        return;
    }

    // Outras teclas só funcionam com sugestões visíveis
    if (!showSuggestions.value || suggestions.value.length === 0) return;

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            highlightedIndex.value = Math.min(highlightedIndex.value + 1, suggestions.value.length - 1);
            break;
        case 'ArrowUp':
            event.preventDefault();
            highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1);
            break;
        case 'Escape':
            showSuggestions.value = false;
            highlightedIndex.value = -1;
            break;
    }
};

// Função para buscar com Enter
const handleSearch = () => {
    if (searchValue.value.trim()) {
        showSuggestions.value = false;
        
        // Refresh completo da página com URL GET
        window.location.href = `/?search=${encodeURIComponent(searchValue.value)}`;
    }
};

// Função para buscar com clique na lupa
const handleLupaClick = () => {
    if (searchValue.value.trim()) {
        handleSearch();
    } else {
        emit('update:searchTerm', '');
    }
};

// Texto do botão estático
const buttonText = 'Pesquisar';
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-red-200 to-red-100 text-slate-900 font-sans pb-20">
        <!-- Header com busca -->
        <nav class="sticky top-0 z-40 bg-slate-900 shadow-2xl overflow-visible">
            <div class="max-w-7xl mx-auto px-4 md:px-6 h-auto md:h-20 flex flex-col md:flex-row items-center py-3 md:py-0 gap-3 md:gap-0 overflow-visible">
                <Link href="/" class="text-xl md:text-2xl font-black tracking-tighter uppercase text-white flex-shrink-0">
                    Erp<span class="text-indigo-500">Vue Laravel</span>
                </Link>
                
                <div class="flex flex-1 w-full md:max-w-md mx-auto relative" ref="suggestionsRef">
                    <input 
                        v-model="searchValue"
                        @input="handleInput"
                        @keydown="handleKeydown"
                        placeholder="Buscar produtos..."
                        class="w-full px-4 py-3 pr-12 text-sm bg-slate-900/50 border border-slate-700/50 rounded-xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300"
                        autocomplete="off"
                    />
                    <button 
                        @click="handleLupaClick"
                        class="absolute right-2 top-1/2 -translate-y-1/2 bg-primary hover:bg-primary-hover active:scale-95 active:shadow-lg text-white px-3 md:px-4 py-2 rounded-xl text-[10px] md:text-xs font-black uppercase tracking-wider transition-all duration-200 shadow-lg shadow-primary/20 flex items-center gap-1 md:gap-2 cursor-pointer transform"
                    >
                        <Search class="w-4 h-4" />
                        <span class="hidden sm:inline">{{ buttonText }}</span>
                    </button>
                    
                    <!-- Dropdown de Sugestões -->
                    <div
                        v-if="showSuggestions && suggestions.length > 0"
                        class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-[9999] min-w-[320px]"
                    >
                        <div class="max-h-80 overflow-y-auto py-2">
                            <div
                                v-for="(suggestion, index) in suggestions"
                                :key="suggestion.term"
                                @click="searchValue = suggestion.term; showSuggestions = false; handleSearch()"
                                @mouseenter="highlightedIndex = index"
                                @mouseleave="highlightedIndex = -1"
                                class="px-4 py-3 cursor-pointer flex items-center gap-3 transition-colors"
                                :class="{ 
                                    'bg-indigo-50': highlightedIndex === index,
                                    'hover:bg-slate-50': highlightedIndex !== index
                                }"
                            >
                                <Search class="w-4 h-4 text-slate-400 flex-shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm font-medium text-slate-800">{{ suggestion.term }}</span>
                                    <span class="text-xs text-slate-400 ml-2">{{ suggestion.type }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <slot />
    </div>
</template>