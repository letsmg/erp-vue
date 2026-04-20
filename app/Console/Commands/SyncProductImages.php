<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-product-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica e baixa imagens de produtos que estão no banco mas não existem no servidor';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando imagens de produtos...');

        // Garante que a pasta de produtos existe
        if (!Storage::disk('public')->exists('products')) {
            Storage::disk('public')->makeDirectory('products');
        }

        $productImages = ProductImage::all();
        $missingCount = 0;
        $downloadedCount = 0;

        foreach ($productImages as $image) {
            $imagePath = 'products/' . $image->path;

            if (!Storage::disk('public')->exists($imagePath)) {
                $this->warn("Imagem ausente: {$image->path} (Produto ID: {$image->product_id})");
                $missingCount++;

                try {
                    $imageUrl = "https://picsum.photos/640/480?random=" . rand(1, 10000);
                    $imageContent = file_get_contents($imageUrl);

                    if ($imageContent) {
                        Storage::disk('public')->put($imagePath, $imageContent);
                        $this->info("✓ Baixada: {$image->path}");
                        $downloadedCount++;
                    }
                } catch (\Exception $e) {
                    $this->error("✗ Falha ao baixar {$image->path}: {$e->getMessage()}");
                }
            }
        }

        if ($missingCount === 0) {
            $this->info('Todas as imagens estão presentes no servidor.');
        } else {
            $this->info("Total de imagens ausentes: {$missingCount}");
            $this->info("Total de imagens baixadas: {$downloadedCount}");
        }

        return self::SUCCESS;
    }
}
