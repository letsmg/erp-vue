import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

// --- NOVAS FUNÇÕES DE MÁSCARA (CENTRALIZADAS) ---

/**
 * Formata Telefone e bloqueia letras.
 */
export const maskPhone = (value: string): string => {
    let r = value.replace(/\D/g, ""); 
    if (r.length > 11) r = r.substring(0, 11);

    if (r.length > 10) {
        r = r.replace(/^(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
    } else if (r.length > 6) {
        r = r.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, "($1) $2-$3");
    } else if (r.length > 2) {
        r = r.replace(/^(\d{2})(\d{0,5})/, "($1) $2");
    } else if (r.length > 0) {
        r = "(" + r;
    }
    return r;
};

/**
 * Formata CNPJ e bloqueia letras.
 */
export const maskCNPJ = (value: string): string => {
    let r = value.replace(/\D/g, "");
    if (r.length > 14) r = r.substring(0, 14);

    return r
        .replace(/^(\d{2})(\d)/, "$1.$2")
        .replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3")
        .replace(/\.(\d{3})(\d)/, ".$1/$2")
        .replace(/(\d{4})(\d)/, "$1-$2");
};

/**
 * Formata CEP e bloqueia letras.
 */
export const maskCEP = (value: string): string => {
    let r = value.replace(/\D/g, "");
    if (r.length > 8) r = r.substring(0, 8);
    return r.replace(/^(\d{5})(\d)/, "$1-$2");
};

// --- FUNÇÕES DE TESTE ---

// --- FUNÇÕES DE GERAÇÃO DE CPF/CNPJ VÁLIDOS ---

/**
 * Gera um CPF válido aleatório
 */
const generateValidCPF = (): string => {
    const cpf = Array.from({ length: 9 }, () => Math.floor(Math.random() * 10));

    for (let t = 9; t < 11; t++) {
        let d = 0;
        for (let c = 0; c < t; c++) {
            d += cpf[c] * ((t + 1) - c);
        }
        d = ((10 * d) % 11) % 10;
        cpf.push(d);
    }

    return cpf.join('');
};

/**
 * Gera um CNPJ válido aleatório
 */
const generateValidCNPJ = (): string => {
    const cnpj = Array.from({ length: 12 }, () => Math.floor(Math.random() * 10));

    // Primeiro dígito verificador
    let sum = 0;
    let weight = 5;
    for (let i = 0; i < 12; i++) {
        sum += cnpj[i] * weight;
        weight = weight === 2 ? 9 : weight - 1;
    }
    let remainder = sum % 11;
    const digit1 = remainder < 2 ? 0 : 11 - remainder;
    cnpj.push(digit1);

    // Segundo dígito verificador
    sum = 0;
    weight = 6;
    for (let i = 0; i < 13; i++) {
        sum += cnpj[i] * weight;
        weight = weight === 2 ? 9 : weight - 1;
    }
    remainder = sum % 11;
    const digit2 = remainder < 2 ? 0 : 11 - remainder;
    cnpj.push(digit2);

    return cnpj.join('');
};

/**
 * Formata CPF para exibição
 */
const formatCPF = (cpf: string): string => {
    return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
};

/**
 * Formata CNPJ para exibição
 */
const formatCNPJ = (cnpj: string): string => {
    return cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
};

// --- FUNÇÕES DE TESTE ---

export const fillFormData = (form: any, suppliers: any[] = [], categories: any[] = []) => {
    if (!form) return;

    const ufs = ['SP', 'RJ', 'MG', 'PR', 'SC', 'RS', 'BA', 'GO', 'CE', 'PE'];

    const fakeData: Record<string, any> = {
        name: () => {
            const nomes = ['João', 'Maria', 'Pedro', 'Ana', 'Carlos', 'Lucas'];
            const sobrenomes = ['Silva', 'Souza', 'Oliveira', 'Costa', 'Pereira'];
            return `${nomes[Math.floor(Math.random()*nomes.length)]} ${sobrenomes[Math.floor(Math.random()*sobrenomes.length)]}`;
        },
        company_name: () => "Empresa Teste " + Math.random().toString(36).substring(7).toUpperCase(),
        email: () => `teste_${Math.random().toString(36).substring(5)}@vuelaravel.com`,
        password: () => "Mudar@123",
        password_confirmation: () => "Mudar@123",

        cnpj: () => "42.123.456/0001-99",
        zip_code: () => "01001-000",
        address: () => "Rua de Teste, " + Math.floor(Math.random() * 999),
        neighborhood: () => "Bairro Industrial",
        city: () => "São Paulo",
        state: () => ufs[Math.floor(Math.random() * ufs.length)],
        contact_name_1: () => "Contato Principal",
        phone_1: () => "(11) 98888-7777",

        // --- CLIENTES (CPF/CNPJ e campos adicionais) ---
        document_type: () => Math.random() > 0.5 ? 'CPF' : 'CNPJ',
        document_number: () => {
            const type = Math.random() > 0.5 ? 'CPF' : 'CNPJ';
            return type === 'CPF' ? formatCPF(generateValidCPF()) : formatCNPJ(generateValidCNPJ());
        },
        phone1: () => "(11) 98888-7777",
        contact1: () => "João Silva",
        phone2: () => "(11) 97777-6666",
        contact2: () => "Maria Santos",
        phone: () => "(11) 96666-5555",
        state_registration: () => "123456789",
        municipal_registration: () => "987654321",
        contributor_type: () => Math.floor(Math.random() * 3) + 1, // 1, 2 ou 9
        user_name: () => {
            const nomes = ['João', 'Maria', 'Pedro', 'Ana', 'Carlos', 'Lucas'];
            const sobrenomes = ['Silva', 'Souza', 'Oliveira', 'Costa', 'Pereira'];
            return `${nomes[Math.floor(Math.random()*nomes.length)]} ${sobrenomes[Math.floor(Math.random()*sobrenomes.length)]}`;
        },
        user_email: () => `usuario_${Math.random().toString(36).substring(5)}@empresa.com`,
        user_password: () => "Mudar@123",
        user_password_confirmation: () => "Mudar@123",

        // Produtos
        description: () => "Tênis Performance Turbo " + Math.floor(Math.random() * 1000),
        brand: () => "Nike",
        model: () => "Air Max 2026",
        size: () => "42",
        collection: () => "Verão 2026",
        gender: () => "Unissex",
        barcode: () => Math.floor(Math.random() * 1000000000000).toString(),
        stock_quantity: () => Math.floor(Math.random() * 100),
        cost_price: () => 150.00,
        sale_price: () => 449.90,
        promo_price: () => 389.90,
        promo_start_at: () => new Date().toISOString().slice(0, 16),
        promo_end_at: () => {
            const date = new Date();
            date.setDate(date.getDate() + 7);
            return date.toISOString().slice(0, 16);
        },
        is_featured: () => Math.random() > 0.5,
        supplier_id: () => (suppliers && suppliers.length > 0) ? suppliers[0].id : '',
        category_id: () => (categories && categories.length > 0) ? categories[0].id : null,

        // Dimensões
        weight: () => 0.350,
        width: () => 30.00,
        height: () => 12.00,
        length: () => 28.00,
        free_shipping: () => false,

        // --- SEO E MARKETING (CORREÇÃO DE ARRAY AQUI) ---
        meta_title: () => "Tênis Nike Air Max 2026 - Oferta Especial",
        meta_description: () => "Compre o novo Air Max 2026 com tecnologia de amortecimento turbo.",
        // CORREÇÃO: Transformamos a string em Array para o componente de tags entender
        meta_keywords: () => ["tênis nike", "air max 2026", "corrida", "esportes"],
        slug: () => "PATH/produtos/tenis-nike-2026",
        h1: () => "Tênis Nike Air Max 2026 Original",
        h2: () => "O máximo em performance e estilo",
        text1: () => "Desenvolvido para atletas que buscam quebrar recordes.",
        text2: () => "Garantia de 12 meses direto com o fabricante.",
        schema_markup: () => '{"@context": "https://schema.org", "@type": "Product", "name": "Nike Air Max"}',
        google_tag_manager: () => "\n<script>(function(w,d,s,l,i){w[l]=w[l]||[];})(window,document,'script','dataLayer','GTM-XXXX');</script>"

    };

    Object.keys(form.data()).forEach((key) => {
        if (fakeData[key]) {
            form[key] = fakeData[key]();
        }
    });
};

export const clearFormData = (form: any) => {
    if (!form) return;
    Object.keys(form.data()).forEach((key) => {
        const value = form[key];
        if (['promo_price', 'promo_start_at', 'promo_end_at', 'supplier_id'].includes(key)) {
            form[key] = null;
        } 
        else if (typeof value === 'string') form[key] = '';
        else if (typeof value === 'number') form[key] = 0;
        else if (typeof value === 'boolean') form[key] = (key === 'is_active');
        else if (Array.isArray(value)) form[key] = [];
    });
    form.clearErrors();
};