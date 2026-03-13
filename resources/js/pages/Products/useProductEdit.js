import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { clearFormData } from '@/lib/utils';

export function useProductEdit(props) {
    const activeTab = ref('geral');
    const newImagePreviews = ref([]);

    const form = useForm({
        _method: 'PUT', 
        supplier_id: props.product.supplier_id,
        description: props.product.description,
        brand: props.product.brand,
        model: props.product.model,
        size: props.product.size,
        collection: props.product.collection,
        gender: props.product.gender || 'Unissex',
        barcode: props.product.barcode,
        stock_quantity: props.product.stock_quantity,
        is_active: Boolean(props.product.is_active),
        is_featured: Boolean(props.product.is_featured),
        
        existing_images: [...props.product.images], 
        new_images: [], 

        cost_price: props.product.cost_price,
        sale_price: props.product.sale_price,
        promo_price: props.product.promo_price,
        promo_start_at: props.product.promo_start_at ? props.product.promo_start_at.slice(0, 16) : '',
        promo_end_at: props.product.promo_end_at ? props.product.promo_end_at.slice(0, 16) : '',

        meta_title: props.product.seo?.meta_title || '',
        meta_description: props.product.seo?.meta_description || '',
        meta_keywords: props.product.seo?.meta_keywords || '',
        canonical_url: props.product.seo?.canonical_url || '',
        h1: props.product.seo?.h1 || '',
        h2: props.product.seo?.h2 || '',
        text1: props.product.seo?.text1 || '',
        text2: props.product.seo?.text2 || '',
        schema_markup: props.product.seo?.schema_markup || '',
        google_tag_manager: props.product.seo?.google_tag_manager || '',
        ads: props.product.seo?.ads || ''
    });

    const handleImageUpload = (e) => {
        const files = Array.from(e.target.files);
        const totalCurrent = form.existing_images.length + form.new_images.length;
        
        if (totalCurrent + files.length > 6) {
            alert('O limite máximo é de 6 fotos.');
            return;
        }

        files.forEach(file => {
            form.new_images.push(file);
            newImagePreviews.value.push(URL.createObjectURL(file));
        });
    };

    const removeExistingImage = (index) => form.existing_images.splice(index, 1);
    const removeNewImage = (index) => {
        form.new_images.splice(index, 1);
        newImagePreviews.value.splice(index, 1);
    };

    const handleKeydown = (e) => {
        if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'l') {
            e.preventDefault();
            if(confirm('Limpar todos os campos?')) {
                clearFormData(form);
                newImagePreviews.value = [];
            }
        }
    };

    onMounted(() => window.addEventListener('keydown', handleKeydown, true));
    onUnmounted(() => window.removeEventListener('keydown', handleKeydown, true));

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
        form.post(route('products.update', props.product.id), {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    return {
        form, activeTab, newImagePreviews,
        handleImageUpload, removeExistingImage, removeNewImage,
        profitData, submit
    };
}