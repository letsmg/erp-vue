<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Supplier;
use App\Helpers\SanitizerHelper;

class ProductRepository
{
    /**
     * Busca produtos com filtros de pesquisa e paginação.
     */
    public function getFiltered(array $filters)
    {
        // Debug inicial
        \Log::info('=== INÍCIO getFiltered ===');
        \Log::info('Filters recebidos: ' . json_encode($filters));

        $query = Product::query()
            ->with(['supplier:id,company_name', 'images' => function($q) {
                $q->orderBy('order', 'asc');
            }]);

        // Debug: contar produtos antes dos filtros
        $totalBefore = $query->count();
        \Log::info('Total de produtos antes dos filtros: ' . $totalBefore);

        // Ordenação - padrão é ordem alfabética
        $sortBy = $filters['sort'] ?? 'description_asc';
        \Log::info('Sort by: ' . $sortBy);
        
        switch ($sortBy) {
            case 'description_asc':
                $query->orderBy('description', 'asc');
                break;
            case 'description_desc':
                $query->orderBy('description', 'desc');
                break;
            case 'sale_price_asc':
                $query->orderBy('sale_price', 'asc');
                break;
            case 'sale_price_desc':
                $query->orderBy('sale_price', 'desc');
                break;
            case 'stock_quantity_asc':
                $query->orderBy('stock_quantity', 'asc');
                break;
            case 'stock_quantity_desc':
                $query->orderBy('stock_quantity', 'desc');
                break;
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'created_at_desc':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('description', 'asc');
                break;
        }

        // Filtros aplicados após a ordenação

        // 1. Filtro de Status (blocked/active) - mutuamente exclusivos
        if (isset($filters['blocked']) && $filters['blocked'] == 1) {
            \Log::info('Aplicando filtro blocked = 1');
            $query->where('is_active', false);
        } elseif (isset($filters['active']) && $filters['active'] == 1) {
            \Log::info('Aplicando filtro active = 1');
            $query->where('is_active', true);
        }

        // 2. Filtros Avançados
        if (!empty($filters['brand'])) {
            \Log::info('Aplicando filtro brand: ' . $filters['brand']);
            $query->where('brand', $filters['brand']);
        }

        if (!empty($filters['model'])) {
            \Log::info('Aplicando filtro model: ' . $filters['model']);
            $query->where('model', 'like', '%' . $filters['model'] . '%');
        }

        if (!empty($filters['category_id']) && is_numeric($filters['category_id'])) {
            \Log::info('Aplicando filtro category_id: ' . $filters['category_id']);
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['price_min']) && is_numeric($filters['price_min'])) {
            \Log::info('Aplicando filtro price_min: ' . $filters['price_min']);
            $query->where('sale_price', '>=', (float) $filters['price_min']);
        }
        if (!empty($filters['price_max']) && is_numeric($filters['price_max'])) {
            \Log::info('Aplicando filtro price_max: ' . $filters['price_max']);
            $query->where('sale_price', '<=', (float) $filters['price_max']);
        }

        if (!empty($filters['stock_min']) && is_numeric($filters['stock_min'])) {
            \Log::info('Aplicando filtro stock_min: ' . $filters['stock_min']);
            $query->where('stock_quantity', '>=', (int) $filters['stock_min']);
        }
        if (!empty($filters['stock_max']) && is_numeric($filters['stock_max'])) {
            \Log::info('Aplicando filtro stock_max: ' . $filters['stock_max']);
            $query->where('stock_quantity', '<=', (int) $filters['stock_max']);
        }

        // 3. Busca por Texto (melhorada)
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            \Log::info('Aplicando filtro search: ' . $search);
            
            $query->where(function ($q) use ($search) {
                // Busca por descrição, marca e modelo
                $searchTerm = "%{$search}%";
                $q->where(function ($sub) use ($searchTerm) {
                    $sub->whereRaw("unaccent(description) ilike unaccent(?)", [$searchTerm])
                       ->orWhereRaw("unaccent(brand) ilike unaccent(?)", [$searchTerm])
                       ->orWhereRaw("unaccent(model) ilike unaccent(?)", [$searchTerm]);
                });

                // Busca por preço (se for numérico)
                $numericValue = $this->parseNumeric($search);
                if ($numericValue > 0) {
                    $q->orWhere(function ($priceSub) use ($numericValue) {
                        $priceSub->where('sale_price', '<=', $numericValue)
                               ->orWhere('promo_price', '<=', $numericValue);
                    });
                }
            });
        }

        // Debug: contar produtos depois dos filtros
        $totalAfter = $query->count();
        \Log::info('Total de produtos depois dos filtros: ' . $totalAfter);

        // Log da query SQL para debug
        \Log::info('SQL Query: ' . $query->toSql());
        \Log::info('Query Bindings: ' . json_encode($query->getBindings()));

        // Importante: usamos withQueryString para manter os filtros ao trocar de página
        $result = $query->paginate(12)->withQueryString();
        
        // Log do resultado
        \Log::info('Total products found: ' . $result->total());
        \Log::info('Products on current page: ' . $result->count());
        \Log::info('=== FIM getFiltered ===');
        
        return $result;
    }

    /**
     * Retorna fornecedores ativos para o formulário de cadastro.
     */
    public function getActiveSuppliers() 
    {
        return Supplier::select('id', 'company_name')
            ->orderBy('company_name')
            ->get();
    }

    /**
     * Retorna todas as marcas disponíveis.
     */
    public function getAllBrands() 
    {
        return Product::query()
            ->whereNotNull('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');
    }

    /**
     * Retorna todas as categorias ativas.
     */
    public function getAllCategories() 
    {
        return \App\Models\Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Cria um produto aplicando a regra de ativação por nível de usuário.
     */
    public function create(array $data) 
    { 
        $user = auth()->user();

        // 1. Sanitiza todos os dados antes de processar
        $data = SanitizerHelper::sanitize($data);

        // 2. Definimos o status de ativação
        $data['is_active'] = ($user && $user->access_level == 1);

        // 3. Criamos uma lista com os campos que pertencem à tabela 'seos'
        $seoFields = ['meta_description', 'meta_keywords'];
        
        // 4. O SEGREDO: Criamos um array excluindo os campos de SEO
        // Isso evita que o SQL tente inserir 'meta_title' na tabela 'products'
        $productData = collect($data)->except($seoFields)->toArray();

        // 5. Salvamos o Produto com os dados limpos
        $product = Product::create($productData);

        // 6. Agora pegamos apenas os dados de SEO para salvar na tabela correta
        $seoData = collect($data)->only($seoFields)->filter()->toArray();

        if (!empty($seoData)) {
            // O Laravel usa a relação MorphOne para criar o registro em 'seos'
            $product->seo()->create($seoData);
        }

        return $product; 
    }
    /**
     * Atualiza os dados básicos do produto.
     */
    public function update(Product $product, array $data) 
    { 
        // 1. Sanitiza todos os dados antes de processar
        $data = SanitizerHelper::sanitize($data);

        // 2. Campos que permitimos atualizar via request
        $productFields = [
            'description', 'supplier_id', 'barcode', 'brand', 'model', 
            'collection', 'size', 'gender', 'stock_quantity', 'slug',
            'cost_price', 'sale_price', 'promo_price', 'promo_start_at', 
            'promo_end_at', 'weight', 'width', 'height', 'length', 'free_shipping',
            'is_active' 
        ];

        $filteredData = collect($data)->only($productFields)->toArray();

        // 3. Trava de Segurança
        $user = auth()->user();
        if ($user && $user->access_level !== 1) {
            // Se não for admin, removemos o is_active do que será salvo
            unset($filteredData['is_active']);
        }

        // 4. Atualização
        // Se filteredData tiver 'description', ela TEM que ser salva aqui
        $product->update($filteredData); 
        
        //dump($product->getChanges());

        return $product; 
    }

    /**
     * Alterna o status de destaque do produto. 
     */
    public function toggleFeatured(Product $product)
    {
        $product->update([
            'is_featured' => !$product->is_featured
        ]);
        
        return $product;
    }

    /**
     * Remove um produto do banco de dados.
     */
    public function delete(Product $product) 
    { 
        return $product->delete(); 
    }

    /**
     * Limpa strings para busca numérica (preços).
     */
    private function parseNumeric($value) 
    {
        $cleaned = preg_replace('/[^0-9,.]/', '', str_replace(',', '.', $value));
        return is_numeric($cleaned) ? (float) $cleaned : 0;
    }
}