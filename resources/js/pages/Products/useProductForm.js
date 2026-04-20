import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { fillFormData, clearFormData } from '@/lib/utils';

export function useProductForm(props) {
    const activeTab = ref('geral');
    const imagePreviews = ref([]);
    const tagInput = ref('');

    const form = useForm({
        // Geral
        supplier_id: '',
        title: '',
        description: '',
        brand: '',
        category_id: '',
        model: '',
        size: '',
        collection: '',
        gender: 'Unissex',
        barcode: '',
        stock_quantity: 0,
        is_active: true,
        is_featured: false,
        images: [],
        
        // Financeiro
        cost_price: 0,
        sale_price: 0,
        promo_price: null,
        promo_start_at: '',
        promo_end_at: '',
        
        // Logística (Mantido conforme seu código: inicializado com 0.1)
        weight: '',
        width: '',
        height: '',
        length: '',
        free_shipping: false,

        // Marketing & SEO
        meta_description: '',
        meta_keywords: [], 
        h2: '',
        text1: '',
        text2: '',
        schema_markup: '',
        google_tag_manager: ''       
    });

    // Funções de Tags (SEO)
    const addTag = () => {
        const val = tagInput.value.trim();
        if (val) {
            if (!Array.isArray(form.meta_keywords)) form.meta_keywords = [];
            if (!form.meta_keywords.includes(val)) {
                form.meta_keywords.push(val);
            }
            tagInput.value = ''; 
        }
    };

    const removeTag = (index) => {
        form.meta_keywords.splice(index, 1);
    };

    // Upload de Imagens
    const handleImageUpload = (e) => {
        const files = Array.from(e.target.files);
        if (form.images.length + files.length > 6) {
            alert('Máximo de 6 fotos permitido.');
            return;
        }
        files.forEach(file => {
            // Preservada a lógica de tempId para controle de estado
            file.tempId = Math.random().toString(36).substr(2, 9);
            form.images.push(file);
            imagePreviews.value.push(URL.createObjectURL(file));
        });
    };

    const removeImage = (index) => {
        // Revoga a URL para liberar memória
        if (imagePreviews.value[index].startsWith('blob:')) {
            URL.revokeObjectURL(imagePreviews.value[index]);
        }
        form.images.splice(index, 1);
        imagePreviews.value.splice(index, 1);
    };

    // Lógica de Reordenação (Mantida conforme seu código)
    const onDragEnd = () => {
        imagePreviews.value = form.images.map(file => 
            file instanceof File ? URL.createObjectURL(file) : file
        );
    };

    // Atalhos de Teclado
    const handleKeydown = (e) => {
        // Atalho para preencher (CTRL + SHIFT + P)
        if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'p') {
            e.preventDefault();
            fillTestForm();
        }
        // Atalho para limpar (CTRL + SHIFT + L)
        if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'l') {
            e.preventDefault();
            clearFormData(form);
            imagePreviews.value = [];
            tagInput.value = '';
        }
    };

    // Função de preenchimento para uso externo/atalho
    const fillTestForm = () => fillFormData(form, props.suppliers, props.categories);

    // Função de limpeza para uso externo/atalho
    const clearCurrentForm = () => {
        clearFormData(form);
        imagePreviews.value = [];
        tagInput.value = '';
    };

    onMounted(() => window.addEventListener('keydown', handleKeydown));
    onUnmounted(() => window.removeEventListener('keydown', handleKeydown));

    // Cálculos Financeiros
    const profitData = computed(() => {
        const cost = parseFloat(form.cost_price) || 0;
        const sale = parseFloat(form.sale_price) || 0;
        const profit = sale - cost;
        const margin = cost > 0 ? (profit / cost) * 100 : 0;
        return {
            value: profit.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }),
            percentage: margin.toFixed(2)
        };
    });

    const submit = () => {
        // Garantimos que o transform envie os dados formatados
        form.transform((data) => ({
            ...data,
            // Transforma o array ['tag1', 'tag2'] em "tag1, tag2" para o SEO
            meta_keywords: Array.isArray(data.meta_keywords) 
                ? data.meta_keywords.join(', ') 
                : data.meta_keywords,
        })).post(route('products.store'), {
            preserveScroll: true,
            forceFormData: true, // Essencial para envio de arquivos (Files)
            onSuccess: () => {
                // Limpa os previews da memória após o sucesso
                imagePreviews.value.forEach(url => {
                    if (url.startsWith('blob:')) URL.revokeObjectURL(url);
                });
                form.reset();
                imagePreviews.value = [];
                tagInput.value = '';
            },
            onError: (errors) => {
                console.error("Erro ao salvar:", errors);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    };

    return {
        form, 
        activeTab, 
        imagePreviews, 
        tagInput,
        addTag, 
        removeTag, 
        handleImageUpload, 
        removeImage, 
        onDragEnd, 
        profitData, 
        fillTestForm,
        clearCurrentForm,
        submit
    };
}