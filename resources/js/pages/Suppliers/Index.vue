<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Truck, Plus, Trash2, Phone, MapPin, UserCheck, Power } from 'lucide-vue-next';

defineProps({ 
    suppliers: Array 
});

const handleDelete = (supplier) => {
    if (confirm(`Deseja excluir permanentemente o fornecedor ${supplier.company_name}?`)) {
        router.delete(route('suppliers.destroy', supplier.id));
    }
};

/**
 * Altera o status do fornecedor.
 * O uso do preserveScroll: true evita que a página pule para o topo após a atualização.
 */
const toggleStatus = (supplier) => {
    router.patch(route('suppliers.update', supplier.id), {
        is_active: !supplier.is_active
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Fornecedores" />

        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tighter uppercase">Fornecedores</h2>
                <p class="mt-1 text-sm text-gray-500">Gestão de parceiros e origens de suprimentos.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <Link :href="route('suppliers.create')" class="bg-indigo-600 hover:bg-indigo-700 active:scale-95 active:shadow-lg text-white px-6 py-3 rounded-2xl flex items-center gap-2 transition-all duration-200 shadow-lg shadow-indigo-500/20 hover:shadow-xl font-bold uppercase text-xs tracking-widest cursor-pointer">
                    <Plus class="w-4 h-4 mr-2" /> Novo Fornecedor
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="supplier in suppliers" :key="supplier.id" 
                :class="[
                    'bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-md transition-all duration-300 group relative',
                    supplier.is_active ? 'border-gray-100' : 'border-gray-200 opacity-80 bg-gray-50'
                ]"
            >
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div :class="[
                            'p-2 rounded-lg transition-colors',
                            supplier.is_active ? 'bg-indigo-50 text-indigo-600' : 'bg-gray-200 text-gray-500'
                        ]">
                            <Truck class="w-6 h-6" />
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <button 
                                @click.stop="toggleStatus(supplier)" 
                                type="button"
                                title="Alterar Status"
                                :class="[
                                    'p-1.5 rounded-lg border transition-all cursor-pointer outline-none focus:ring-2 focus:ring-offset-2',
                                    supplier.is_active 
                                        ? 'bg-green-50 border-green-200 text-green-600 hover:bg-green-100 focus:ring-green-500' 
                                        : 'bg-red-50 border-red-200 text-red-600 hover:bg-red-100 focus:ring-red-500'
                                ]"
                            >
                                <Power class="w-4 h-4" />
                            </button>

                            <button @click.stop="handleDelete(supplier)" class="text-gray-300 hover:text-red-600 transition p-1">
                                <Trash2 class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 truncate" :title="supplier.company_name">
                        {{ supplier.company_name }}
                    </h3>
                    
                    <div class="flex justify-between items-end mt-1">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-tighter">CNPJ: {{ supplier.cnpj }}</span>
                            <span class="text-xs font-medium text-gray-400">IE: {{ supplier.state_registration }}</span>
                        </div>
                        <span :class="[
                            'text-[9px] font-black uppercase px-2 py-0.5 rounded-md tracking-tighter transition-colors',
                            supplier.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                        ]">
                            {{ supplier.is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-start text-sm text-gray-600">
                            <MapPin class="w-4 h-4 mr-2 text-indigo-500 flex-shrink-0 mt-0.5" />
                            <span class="truncate">{{ supplier.city }} - {{ supplier.neighborhood }}</span>
                        </div>

                        <div class="flex items-center text-sm text-gray-600">
                            <UserCheck class="w-4 h-4 mr-2 text-green-500 flex-shrink-0" />
                            <span class="font-medium">{{ supplier.contact_name_1 }}</span>
                        </div>

                        <div class="flex items-center text-sm text-gray-600 bg-gray-50 p-2 rounded-lg border border-gray-100">
                            <Phone class="w-4 h-4 mr-2 text-gray-400" />
                            <span class="font-bold text-indigo-600">{{ supplier.phone_1 }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50/50 px-6 py-3 border-t border-gray-100 flex justify-between items-center group-hover:bg-gray-50 transition-colors">
                    <span class="text-[10px] font-bold text-gray-400 uppercase">CEP: {{ supplier.zip_code }}</span>
                    <Link :href="route('suppliers.edit', supplier.id)" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-widest">
                        Editar Dados
                    </Link>
                </div>
            </div>
        </div>

        <div v-if="suppliers.length === 0" class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border-2 border-dashed border-gray-100 mt-6">
            <div class="bg-gray-50 p-4 rounded-full mb-4">
                <Truck class="w-12 h-12 text-gray-200" />
            </div>
            <p class="text-gray-400 font-medium">Nenhum fornecedor encontrado.</p>
        </div>
    </AuthenticatedLayout>
</template>