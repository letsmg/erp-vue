<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->warn('Limpando pasta de imagens antiga...');
        Storage::disk('public')->deleteDirectory('products');
        Storage::disk('public')->makeDirectory('products');

        $supplier = Supplier::firstOrCreate(
            ['email' => 'fornecedor@teste.com'],
            [
                'company_name' => 'Fornecedor Padrão',
                'email' => 'fornecedor@teste.com',
                'cnpj' => '00.000.000/0000-00',
                'state_registration' => 'ISENTO',
                'address' => 'Rua do Fornecedor, 100',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01000-000',
                'contact_name_1' => 'Contato Padrão',
                'phone_1' => '(11) 00000-0000',
                'is_active' => true,
            ]
        );

        $this->command->info('Iniciando criacao de produtos e download de imagens...');

        Product::factory(20)->create([
            'supplier_id' => $supplier->id
        ])->each(function ($product) {
            
            // 1. Criar o SEO
            // meta_title e h1 são derivados do product->description (usado no frontend)
            $product->seo()->firstOrCreate(
                [
                    'seoable_id' => $product->id,
                    'seoable_type' => get_class($product),
                ],
                [
                    'meta_description' => "Compre agora " . $product->description . " com as melhores condições.",
                    'meta_keywords'    => str_replace(' ', ', ', $product->description),
                    'text1'            => "Descrição detalhada do produto " . $product->description,
                    // text2, h2, etc., são nullables, então não precisam estar aqui.
                ]
            );

            // 2. Criar 3 imagens baixando da internet
            for ($i = 1; $i <= 3; $i++) {
                $imageName = 'prod_' . $product->id . '_' . $i . '.jpg';
                $imageUrl = "https://picsum.photos/640/480?random=" . rand(1, 10000);

                try {
                    $imageContent = file_get_contents($imageUrl);
                    
                    if ($imageContent) {
                        Storage::disk('public')->put('products/' . $imageName, $imageContent);

                        ProductImage::firstOrCreate(
                            ['product_id' => $product->id, 'order' => $i],
                            [
                            'product_id' => $product->id,
                            'path'       => $imageName,
                            'order'      => $i
                        ]);
                    }
                } catch (\Exception $e) {
                    $this->command->error("Falha ao baixar imagem para o produto {$product->id}");
                }
            }
            
            $this->command->comment("Produto {$product->id} criado com sucesso.");
        });

        $this->command->info('Seeder finalizado!');
    }
}

