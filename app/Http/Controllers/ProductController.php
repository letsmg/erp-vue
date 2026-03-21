<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;

class ProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => Product::with(['supplier:id,company_name', 'images'])
                ->latest()
                ->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create', [
            'suppliers' => Supplier::select('id', 'company_name')->orderBy('company_name')->get()
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($request, $data) {
            // Regras de negócio: Sempre inativo e slug automático
            $data['is_active'] = false;
            $data['slug'] = Str::slug($data['description']) . '-' . Str::lower(Str::random(5));
            
            $product = Product::create($data);

            // Processar Imagens
            if ($request->hasFile('images')) {
                $this->uploadImages($product, $request->file('images'));
            }

            // Sincronizar SEO (Só cria se tiver algo preenchido)
            $this->syncSeoMetadata($product, $request);

            return redirect()->route('products.index')
                ->with('message', 'Produto cadastrado com sucesso! Pendente de ativação.');
        });
    }

    public function edit(Product $product)
    {
        return Inertia::render('Products/Edit', [
            'product' => $product->load(['images', 'seo']),
            'suppliers' => Supplier::select('id', 'company_name')->orderBy('company_name')->get()
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($request, $data, $product) {
            // 1. Gestão de Imagens Antigas
            $existingIds = collect($request->existing_images)->pluck('id')->toArray();
            $imagesToDelete = $product->images()->whereNotIn('id', $existingIds)->get();

            foreach ($imagesToDelete as $oldImg) {
                Storage::disk('public')->delete('products/' . $oldImg->path);
                $oldImg->delete();
            }

            // 2. Novas Imagens
            if ($request->hasFile('new_images')) {
                $this->uploadImages($product, $request->file('new_images'));
            }

            // 3. Atualizar Slug se a descrição mudar
            if ($product->description !== $data['description']) {
                $data['slug'] = Str::slug($data['description']) . '-' . Str::lower(Str::random(5));
            }

            $product->update($data);

            // 4. Sincronizar SEO
            $this->syncSeoMetadata($product, $request);

            return redirect()->route('products.index')->with('message', 'Produto atualizado!');
        });
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $img) {
            Storage::disk('public')->delete('products/' . $img->path);
            $img->delete();
        }

        if ($product->seo) {
            $product->seo->delete();
        }

        $product->delete();

        return redirect()->route('products.index')->with('message', 'Produto removido.');
    }

    /**
     * Métodos Privados Auxiliares
     */

    private function uploadImages($product, array $files)
    {
        foreach ($files as $file) {
            if (!$this->isImageSafe($file)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'images' => 'Uma das imagens contém conteúdo impróprio.'
                ]);
            }
            $path = $file->store('products', 'public');
            $product->images()->create(['path' => basename($path)]);
        }
    }

    private function syncSeoMetadata($product, $request)
    {
        $seoFields = [
            'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 
            'h1', 'text1', 'h2', 'text2', 'schema_markup', 'google_tag_manager', 'ads'
        ];

        // Só processamos se houver algum campo preenchido ou se já existir SEO (para updates)
        $hasInput = collect($seoFields)->some(fn($f) => $request->filled($f));

        if ($hasInput || $product->seo()->exists()) {
            $product->seo()->updateOrCreate(
                ['seoable_id' => $product->id, 'seoable_type' => get_class($product)],
                array_merge(
                    $request->only($seoFields),
                    ['slug' => $product->slug]
                )
            );
        }
    }

    private function isImageSafe($image)
    {
        $credentialPath = base_path('google-credentials.json');

        if (!class_exists('Google\Cloud\Vision\V1\ImageAnnotatorClient') || !file_exists($credentialPath)) {
            return true; 
        }

        try {
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialPath);
            $imageAnnotator = new ImageAnnotatorClient();
            $content = file_get_contents($image->getRealPath());
            $response = $imageAnnotator->safeSearchDetection($content);
            $safe = $response->getSafeSearchAnnotation();
            $imageAnnotator->close();

            $unsafeLevels = [3, 4, 5]; // Likely to Very Likely
            return !(in_array($safe->getAdult(), $unsafeLevels) || in_array($safe->getViolence(), $unsafeLevels));
        } catch (\Exception $e) {
            Log::error("Erro API Vision: " . $e->getMessage());
            return true;
        }
    }
}