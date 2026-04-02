<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { FileText, Printer, FileSearch, Truck, Users, CheckSquare, Square } from 'lucide-vue-next';

// Recebe os fornecedores do ReportController
const props = defineProps({
    suppliers: Array
});

// Estado do formulário de Produtos
const productForm = ref({
    type: 'sintetico',
    supplier_id: ''
});

// Estado do formulário de Clientes
const clientForm = ref({
    document_type: '',
    status: '',
    fields: ['name', 'document_number', 'email'] // IDs dos campos
});

// Grupos de campos com labels em Português
const fieldGroups = {
    personal: { 
        label: 'Dados Pessoais', 
        fields: [
            { id: 'name', label: 'Nome Completo' },
            { id: 'document_number', label: 'CPF/CNPJ' },
            { id: 'email', label: 'E-mail' },
            { id: 'phone1', label: 'Telefone' }
        ] 
    },
    address: { 
        label: 'Endereço', 
        fields: [
            { id: 'zip_code', label: 'CEP' },
            { id: 'street', label: 'Logradouro' },
            { id: 'number', label: 'Número' },
            { id: 'neighborhood', label: 'Bairro' },
            { id: 'city', label: 'Cidade' },
            { id: 'state', label: 'UF' }
        ] 
    },
    billing: { 
        label: 'Faturamento/Compras', 
        fields: [
            { id: 'total_purchases', label: 'Total Comprado' },
            { id: 'last_purchase_date', label: 'Última Compra' }
        ] 
    }
};

// Toggle grupo
const toggleGroup = (groupKey) => {
    const groupFieldIds = fieldGroups[groupKey].fields.map(f => f.id);
    const allPresent = groupFieldIds.every(id => clientForm.value.fields.includes(id));
    
    if (allPresent) {
        clientForm.value.fields = clientForm.value.fields.filter(id => !groupFieldIds.includes(id));
    } else {
        const uniqueFields = new Set([...clientForm.value.fields, ...groupFieldIds]);
        clientForm.value.fields = Array.from(uniqueFields);
    }
};

const isGroupSelected = (groupKey) => {
    return fieldGroups[groupKey].fields.every(f => clientForm.value.fields.includes(f.id));
};

// Função que monta a URL e abre o PDF de Produtos
const generateProductReport = () => {
    const params = new URLSearchParams({
        type: productForm.value.type,
        supplier_id: productForm.value.supplier_id
    }).toString();
    
    window.open(route('reports.products') + '?' + params, '_blank');
};

// Função que monta a URL e abre o PDF de Clientes
const generateClientReport = () => {
    const params = new URLSearchParams();
    params.append('document_type', clientForm.value.document_type);
    params.append('status', clientForm.value.status);
    clientForm.value.fields.forEach(f => params.append('fields[]', f));
    
    window.open(route('reports.clients') + '?' + params.toString(), '_blank');
};
</script>

<template>
    <Head title="Relatórios" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto py-8 px-4">
            <div class="mb-10">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight uppercase italic">Centro de Relatórios</h2>
                <p class="text-slate-500 font-medium mt-1 uppercase text-xs tracking-widest">Gere documentos PDF detalhados do seu sistema.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Relatório de Produtos -->
                <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="p-4 bg-indigo-50 text-indigo-600 rounded-2xl">
                            <Truck class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tighter">Produtos</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Inventário e Fornecedores</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Tipo de Relatório</label>
                            <div class="grid grid-cols-2 gap-4">
                                <button 
                                    @click="productForm.type = 'sintetico'"
                                    :class="productForm.type === 'sintetico' ? 'bg-slate-900 text-white shadow-xl shadow-slate-200' : 'bg-slate-50 text-slate-500 hover:bg-slate-100'"
                                    class="p-4 rounded-2xl text-xs font-black uppercase tracking-widest transition-all"
                                >
                                    Sintético
                                </button>
                                <button 
                                    @click="productForm.type = 'analitico'"
                                    :class="productForm.type === 'analitico' ? 'bg-slate-900 text-white shadow-xl shadow-slate-200' : 'bg-slate-50 text-slate-500 hover:bg-slate-100'"
                                    class="p-4 rounded-2xl text-xs font-black uppercase tracking-widest transition-all"
                                >
                                    Analítico
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Filtrar por Fornecedor</label>
                            <select v-model="productForm.supplier_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-indigo-500 transition-all outline-none appearance-none">
                                <option value="">Todos os Fornecedores</option>
                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.company_name }}</option>
                            </select>
                        </div>

                        <button @click="generateProductReport" class="w-full btn-primary py-5 rounded-2xl flex items-center justify-center gap-3 group mt-4">
                            <Printer class="w-5 h-5 group-hover:scale-110 transition-transform" />
                            GERAR PDF DE PRODUTOS
                        </button>
                    </div>
                </div>

                <!-- Relatório de Clientes -->
                <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="p-4 bg-emerald-50 text-emerald-600 rounded-2xl">
                            <Users class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tighter">Clientes</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Base de Dados e Perfil</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Tipo Pessoa</label>
                                <select v-model="clientForm.document_type" class="w-full bg-slate-50 border-transparent rounded-2xl px-5 py-3 text-xs focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                                    <option value="">Todos</option>
                                    <option value="CPF">Física (CPF)</option>
                                    <option value="CNPJ">Jurídica (CNPJ)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Status</label>
                                <select v-model="clientForm.status" class="w-full bg-slate-50 border-transparent rounded-2xl px-5 py-3 text-xs focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                                    <option value="">Todos</option>
                                    <option value="1">Ativos</option>
                                    <option value="0">Bloqueados</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 ml-1">Campos para Exibição</label>
                            
                            <div class="space-y-4">
                                <div v-for="(group, key) in fieldGroups" :key="key" class="border border-slate-100 rounded-2xl p-4 bg-slate-50/50">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-700">{{ group.label }}</span>
                                        <button @click="toggleGroup(key)" class="text-[9px] font-black uppercase text-emerald-600 hover:underline">
                                            {{ isGroupSelected(key) ? 'Desmarcar Grupo' : 'Marcar Grupo' }}
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label v-for="field in group.fields" :key="field.id" class="flex items-center gap-2 cursor-pointer group">
                                            <input type="checkbox" v-model="clientForm.fields" :value="field.id" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-[10px] font-bold uppercase tracking-tight text-slate-500 group-hover:text-slate-900 transition-colors">{{ field.label }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button @click="generateClientReport" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-5 rounded-2xl flex items-center justify-center gap-3 group mt-4 transition-all shadow-lg shadow-emerald-200">
                            <Printer class="w-5 h-5 group-hover:scale-110 transition-transform" />
                            GERAR PDF DE CLIENTES
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>