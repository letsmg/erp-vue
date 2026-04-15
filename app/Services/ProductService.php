<?php

namespace App\Services;

use App\Helpers\SanitizerHelper;
use App\Helpers\SchemaMarkupValidator;
use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Illuminate\Support\Facades\Log;

class ProductService
{
    protected $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    public function storeProduct(array $data, $request)
    {
        return DB::transaction(function () use ($data, $request) {

            $data['slug'] = $this->generateSlug($data['title'] ?? '');

            $product = $this->repository->create($data);

            if ($request->hasFile('images')) {
                $this->handleImageUpload($product, $request->file('images'));
            }

            $this->syncSeo($product, $request->all());

            return $product;
        });
    }

    public function updateProduct(Product $product, array $data, $request)
    {
        return DB::transaction(function () use ($product, $data, $request) {

            $existingIds = $data['existing_images'] ?? [];

            $imagesToDelete = $product->images()->whereNotIn('id', $existingIds)->get();
            foreach ($imagesToDelete as $oldImg) {
                Storage::disk('public')->delete('products/' . $oldImg->path);
                $oldImg->delete();
            }

            foreach ($existingIds as $index => $id) {
                ProductImage::where('id', $id)->update(['order' => $index]);
            }

            if ($request->hasFile('new_images')) {
                $lastOrder = count($existingIds) > 0 ? count($existingIds) - 1 : -1;
                $this->handleImageUpload($product, $request->file('new_images'), $lastOrder + 1);
            }

            $product = $this->repository->update($product, $data);

            $this->syncSeo($product, $request->all());

            return $product;
        });
    }

    public function deleteProduct(Product $product)
    {
        return DB::transaction(function () use ($product) {

            foreach ($product->images as $img) {
                Storage::disk('public')->delete('products/' . $img->path);
                $img->delete();
            }

            if ($product->seo) {
                $product->seo->delete();
            }

            return $this->repository->delete($product);
        });
    }

    private function generateSlug($description)
    {
        return Str::slug($description) . '-' . Str::lower(Str::random(5));
    }

    private function handleImageUpload($product, array $files, $startOrder = 0)
    {
        foreach ($files as $index => $file) {

            if (!$this->isImageSafe($file)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'images' => 'Uma das imagens contém conteúdo impróprio detectado pela IA.'
                ]);
            }

            $path = $file->store('products', 'public');

            $product->images()->create([
                'path' => basename($path),
                'order' => $startOrder + $index
            ]);
        }
    }

    private function syncSeo($product, array $input)
    {
        $seoFields = [
            'meta_title', 'meta_description', 'meta_keywords',
            'schema_markup', 'google_tag_manager',
        ];

        $data = collect($input)->only($seoFields)->toArray();

        // Aplica sanitização em TODOS os campos, exceto schema_markup e google_tag_manager
        $data = SanitizerHelper::sanitize($data, ['schema_markup', 'google_tag_manager']);

        // Valida e sanitiza schema_markup especificamente
        if (isset($data['schema_markup'])) {
            $data['schema_markup'] = SchemaMarkupValidator::validateAndSanitize($data['schema_markup']);
        }

        // Valida google_tag_manager (apenas permite scripts válidos)
        if (isset($data['google_tag_manager'])) {
            $data['google_tag_manager'] = $this->validateGoogleTagManager($data['google_tag_manager']);
        }

        $product->seo()->updateOrCreate(
            [
                'seoable_id' => $product->id,
                'seoable_type' => Product::class
            ],
            $data
        );
    }

    /**
     * Valida Google Tag Manager script
     */
    private function validateGoogleTagManager(?string $script): ?string
    {
        if (empty($script)) {
            return null;
        }

        // Permite apenas scripts GTM válidos
        $gtmPattern = '/^\s*<!--\s*Google Tag Manager\s*-->.*?<!--\s*End Google Tag Manager\s*-->\s*$/s';
        
        if (!preg_match($gtmPattern, $script)) {
            return null;
        }

        // Remove tags perigosas que não sejam GTM
        $script = preg_replace('/<script\b(?![^>]*Google\s+Tag\s+Manager)[^<]*>.*?<\/script>/is', '', $script);
        
        return trim($script);
    }

    private function isImageSafe($image)
    {
        $credentialPath = base_path('google-credentials.json');

        if (!class_exists(ImageAnnotatorClient::class) || !file_exists($credentialPath)) {
            return true;
        }

        try {
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialPath);

            $imageAnnotator = new ImageAnnotatorClient();
            $content = file_get_contents($image->getRealPath());

            $response = $imageAnnotator->safeSearchDetection($content);
            $safe = $response->getSafeSearchAnnotation();

            $imageAnnotator->close();

            $unsafeLevels = [4, 5];

            return !(in_array($safe->getAdult(), $unsafeLevels) || in_array($safe->getViolence(), $unsafeLevels));

        } catch (\Exception $e) {
            Log::error("Erro API Vision: " . $e->getMessage());
            return true;
        }
    }
}