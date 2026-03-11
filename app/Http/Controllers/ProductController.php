<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Lista todos os produtos com o nome do fornecedor
     */
    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => Product::with('supplier:id,company_name')
                ->latest()
                ->get()
        ]);
    }

    /**
     * Abre a tela de cadastro
     */
    public function create()
    {
        return Inertia::render('Products/Create', [
            'suppliers' => Supplier::select('id', 'company_name')
                ->orderBy('company_name')
                ->get()
        ]);
    }

    /**
     * Salva o novo produto e seu metadado SEO
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'    => 'required|exists:suppliers,id',
            'description'    => 'required|string|max:255',            
            'brand'          => 'nullable|string|max:100',
            'model'          => 'nullable|string|max:100',
            'size'           => 'nullable|string|max:50',
            'collection'     => 'nullable|string|max:100',
            'gender'         => 'nullable|in:Masculino,Feminino,Unissex,Infantil',
            'cost_price'     => 'required|numeric|min:0',
            'sale_price'     => 'required|numeric|min:0',
            'promo_price'    => 'nullable|numeric|min:0',
            'promo_start_at' => 'nullable|date',
            'promo_end_at'   => 'nullable|date',
            'barcode'        => 'nullable|string|unique:products,barcode',
            'stock_quantity' => 'required|integer|min:0',
            'is_active'      => 'boolean',
            'is_featured'    => 'boolean',
            'meta_title'     => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160'
        ]);

        // 1. Gera o slug do PRODUTO (tabela products)
        $data['slug'] = Str::slug($data['description']) . '-' . Str::lower(Str::random(5));
        
        // 2. Cria o Produto
        $product = Product::create($data);

        // 3. Cria o SEO relacionado (tabela seo_metadata)
        // Resolve o erro SQLSTATE[23502] garantindo o slug
        $seoTitle = $data['meta_title'] ?? $data['description'];
        
        $product->seo()->create([
            'meta_title'       => $seoTitle,
            'meta_description' => $data['meta_description'] ?? null,
            'slug'        => Str::slug($seoTitle) . '-' . time(), // Garante o slug não nulo
        ]);

        return redirect()->route('products.index')->with('message', 'Produto cadastrado com sucesso!');
    }

    /**
     * Abre a tela de edição
     */
    public function edit(Product $product)
    {
        // Carrega o SEO junto com o produto
        $product->load('seo');

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'suppliers' => Supplier::select('id', 'company_name')
                ->orderBy('company_name')
                ->get()
        ]);
    }

    /**
     * Atualiza o produto e o SEO
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'supplier_id'      => 'required|exists:suppliers,id',
            'description'      => 'required|string|max:255', // Faltava na sua validação
            'brand'            => 'nullable|string|max:100',
            'model'            => 'nullable|string|max:100',
            'size'             => 'nullable|string|max:50',
            'collection'       => 'nullable|string|max:100',
            'gender'           => 'nullable|in:Masculino,Feminino,Unissex,Infantil',
            'cost_price'       => 'required|numeric|min:0',
            'sale_price'       => 'required|numeric|min:0',
            'barcode'          => ['nullable', Rule::unique('products')->ignore($product->id)],
            'stock_quantity'   => 'required|integer|min:0',
            'is_active'        => 'boolean',
            'is_featured'      => 'boolean',
            // Validação dos campos de SEO que vêm do request
            'meta_title'       => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords'    => 'nullable|string|max:160',
        ]);

        // 1. Atualiza o slug do PRODUTO apenas se a DESCRIÇÃO mudar
        if ($product->description !== $data['description']) {
            $data['slug'] = Str::slug($data['description']) . '-' . Str::lower(Str::random(5));
        }

        $product->update($data);

        // 2. Atualiza ou Cria o metadado SEO (tabela seo_metadata)
        // Usamos meta_title e meta_description conforme sua migration
        $seoTitle = $data['meta_title'] ?? $data['description'];
        
        $product->seo()->updateOrCreate(
            ['seoable_id' => $product->id, 'seoable_type' => get_class($product)],
            [
                'meta_title'       => $seoTitle,
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords'    => $data['meta_keywords'] ?? null,
                'slug'             => Str::slug($seoTitle) . '-' . $product->id, // Slug único para SEO
            ]
        );

        return redirect()->route('products.index')
            ->with('message', 'Produto e SEO atualizados com sucesso!');
    }

    /**
     * Remove o produto (O SEO deve ser removido via cascade no banco ou manualmente aqui)
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('message', 'Produto excluído com sucesso!');
    }
}