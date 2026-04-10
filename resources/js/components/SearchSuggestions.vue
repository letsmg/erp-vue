// Componente de sugestões de busca inteligente
<template>
  <div class="relative">
    <input
      type="text"
      v-model="searchTerm"
      @input="handleInput"
      @keydown="handleKeydown"
      @focus="() => alert('🔍 DEBUG: SearchSuggestions input focado')"
      @blur="hideSuggestions"
      @click="() => alert('🔍 DEBUG: SearchSuggestions input clicado')"
      @keyup="() => alert('🔍 DEBUG: SearchSuggestions keyup: ' + searchTerm)"
      placeholder="Buscar produtos..."
      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
    />
    
    <!-- Lista de sugestões -->
    <div
      v-show="showSuggestions && suggestions.length > 0"
      class="absolute z-[100] w-full bg-white border border-gray-300 rounded-lg shadow-xl mt-1 max-h-60 overflow-y-auto top-full left-0"
    >
      <div class="p-2">
        <div
          v-for="(suggestion, index) in suggestions"
          :key="suggestion.term"
          @click="selectSuggestion(suggestion)"
          @mouseenter="highlightedIndex = index"
          @mouseleave="highlightedIndex = -1"
          class="px-3 py-2 cursor-pointer hover:bg-gray-100 flex justify-between items-center"
          :class="{ 'bg-blue-50': highlightedIndex === index }"
        >
          <div>
            <span class="font-medium">{{ suggestion.term }}</span>
            <span class="text-xs text-gray-500 ml-2">{{ suggestion.type }}</span>
          </div>
          <div class="text-xs text-gray-400">
            {{ suggestion.score }} buscas
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'
import { debounce } from 'lodash-es'

// DEBUG: Verificar se este componente está sendo carregado
alert('🔍 DEBUG: SearchSuggestions.vue carregado!');
console.log('🔍 DEBUG: SearchSuggestions.vue script setup executado');

const props = defineProps({
  initialSearch: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['search', 'suggestion-selected'])

const searchTerm = ref(props.initialSearch)
const suggestions = ref([])
const showSuggestions = ref(false)
const highlightedIndex = ref(-1)
const loading = ref(false)

// Busca sugestões com debounce
const fetchSuggestions = debounce(async (term) => {
  if (term.length < 2) {
    suggestions.value = []
    showSuggestions.value = false
    return
  }

  try {
    loading.value = true
    const response = await fetch(`/api/search/suggestions?term=${encodeURIComponent(term)}`)
    const data = await response.json()
    
    suggestions.value = data.suggestions || []
    showSuggestions.value = true
    
  } catch (error) {
    console.error('Erro ao buscar sugestões:', error)
    suggestions.value = []
  } finally {
    loading.value = false
  }
}, 300)

// Quando usuário digita - só busca sugestões, NÃO registra e NÃO busca
const handleInput = (event) => {
  const term = event.target.value
  searchTerm.value = term
  
  // Só busca sugestões (não registra no Redis ainda)
  fetchSuggestions(term)
}

// Seleciona sugestão
const selectSuggestion = (suggestion) => {
  searchTerm.value = suggestion.term
  showSuggestions.value = false
  highlightedIndex.value = -1
  
  // Emite evento de busca (vai para a página de resultados)
  emit('suggestion-selected', suggestion)
  emit('search', suggestion.term)
}

// Esconde sugestões
const hideSuggestions = () => {
  setTimeout(() => {
    showSuggestions.value = false
    highlightedIndex.value = -1
  }, 200)
}

// Registra busca no backend
const registerSearch = async (term) => {
  try {
    await fetch('/api/search/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ term })
    })
  } catch (error) {
    console.error('Erro ao registrar busca:', error)
  }
}

// Navega com teclado
watch(highlightedIndex, (newIndex) => {
  if (newIndex >= 0 && newIndex < suggestions.value.length) {
    const element = document.querySelector(`[data-suggestion-index="${newIndex}"]`)
    if (element) {
      element.scrollIntoView({ block: 'nearest' })
    }
  }
})

// Teclado
const handleKeydown = (event) => {
  if (!showSuggestions.value) return
  
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      highlightedIndex.value = Math.min(highlightedIndex.value + 1, suggestions.value.length - 1)
      break
    case 'ArrowUp':
      event.preventDefault()
      highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1)
      break
    case 'Enter':
      event.preventDefault()
      if (highlightedIndex.value >= 0) {
        selectSuggestion(suggestions.value[highlightedIndex.value])
      }
      break
    case 'Escape':
      hideSuggestions()
      break
  }
}
</script>

<style scoped>
.relative {
  position: relative;
}

.absolute {
  position: absolute;
}

.z-50 {
  z-index: 50;
}

.w-full {
  width: 100%;
}

.bg-white {
  background-color: white;
}

.border {
  border-width: 1px;
}

.border-gray-300 {
  border-color: #d1d5db;
}

.rounded-lg {
  border-radius: 0.5rem;
}

.shadow-lg {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.mt-1 {
  margin-top: 0.25rem;
}

.max-h-60 {
  max-height: 15rem;
}

.overflow-y-auto {
  overflow-y: auto;
}

.p-2 {
  padding: 0.5rem;
}

.px-3 {
  padding-left: 0.75rem;
  padding-right: 0.75rem;
}

.py-2 {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}

.cursor-pointer {
  cursor: pointer;
}

.hover\:bg-gray-100:hover {
  background-color: #f3f4f6;
}

.flex {
  display: flex;
}

.justify-between {
  justify-content: space-between;
}

.items-center {
  align-items: center;
}

.font-medium {
  font-weight: 500;
}

.text-xs {
  font-size: 0.75rem;
}

.text-gray-500 {
  color: #6b7280;
}

.ml-2 {
  margin-left: 0.5rem;
}

.text-gray-400 {
  color: #9ca3af;
}

.bg-blue-50 {
  background-color: #eff6ff;
}

.focus\:ring-2:focus {
  --tw-ring-offset-shadow: 0 0 0 0 rgba(59, 130, 246, 0.5);
}

.focus\:ring-blue-500:focus {
  --tw-ring-color: #3b82f6;
}

.focus\:border-transparent:focus {
  border-color: transparent;
}

.px-4 {
  padding-left: 1rem;
  padding-right: 1rem;
}

.py-2 {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}
</style>
