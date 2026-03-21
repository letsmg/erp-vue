import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { fillFormData, clearFormData } from '@/lib/utils';

export function useProductForm(props) {
    const activeTab = ref('geral');
    const imagePreviews = ref([]);
    const tagInput = ref('');

    const form = useForm({
        supplier_id: null,
        description: '',
        brand: '',
        model: '',
        size: '',
        collection: '',
        gender: 'Unissex',
        barcode: '',
        stock_quantity: 0,
        is_active: true,
        is_featured: false,
        images: [],
        cost_price: 0,
        sale_price: 0,
        promo_price: null,
        promo_start_at: '',
        promo_end_at: '',
        meta_title: '',
        meta_description: '',
        meta_keywords: [], // No front-end ele é um Array
        canonical_url: '',
        h1: '',
        h2: '',
        text1: '',
        text2: '',
        schema_markup: '',
        google_tag_manager: '',
        ads: ''
    });

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

    const handleImageUpload = (e) => {
        const files = Array.from(e.target.files);
        if (form.images.length + files.length > 6) {
            alert('Máximo de 6 fotos permitido.');
            return;
        }
        files.forEach(file => {
            file.tempId = Math.random().toString(36).substr(2, 9);
            form.images.push(file);
            imagePreviews.value.push(URL.createObjectURL(file));
        });
    };

    const removeImage = (index) => {
        form.images.splice(index, 1);
        imagePreviews.value.splice(index, 1);
    };

    const onDragEnd = () => {
        imagePreviews.value = form.images.map(file => 
            file instanceof File ? URL.createObjectURL(file) : file
        );
    };

    const handleKeydown = (e) => {
        if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'p') {
            e.preventDefault();
            fillFormData(form, props.suppliers);
        }
        if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'l') {
            e.preventDefault();
            clearFormData(form);
            imagePreviews.value = [];
            tagInput.value = '';
        }
    };

    onMounted(() => window.addEventListener('keydown', handleKeydown));
    onUnmounted(() => window.removeEventListener('keydown', handleKeydown));

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
        // --- CORREÇÃO AQUI ---
        // Criamos uma cópia para transformar o Array em String antes de enviar ao Laravel
        const dataToSend = {
            ...form.data(),
            meta_keywords: Array.isArray(form.meta_keywords) 
                ? form.meta_keywords.join(', ') 
                : form.meta_keywords
        };

        form.transform(() => dataToSend).post(route('products.store'), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                form.reset();
                imagePreviews.value = [];
                tagInput.value = '';
            },
        });
    };

    return {
        form, activeTab, imagePreviews, tagInput,
        addTag, removeTag, handleImageUpload, 
        removeImage, onDragEnd, profitData, submit
    };
}