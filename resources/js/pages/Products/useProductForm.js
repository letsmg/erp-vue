import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { fillFormData, clearFormData } from '@/lib/utils';

export function useProductForm(props) {
    const activeTab = ref('geral');
    const imagePreviews = ref([]);

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
        meta_keywords: '',
        canonical_url: '',
        h1: '',
        h2: '',
        text1: '',
        text2: '',
        schema_markup: '',
        google_tag_manager: '',
        ads: ''
    });

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
        imagePreviews.value = form.images.map(file => URL.createObjectURL(file));
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
        form.post(route('products.store'), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                form.reset();
                imagePreviews.value = [];
            },
        });
    };

    return {
        form, activeTab, imagePreviews, 
        handleImageUpload, removeImage, onDragEnd, 
        profitData, submit
    };
}