<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    public function index()
    {
        // Cria o objeto do Sitemap
        $sitemap = Sitemap::create();

        // Adiciona a página inicial da loja
        $sitemap->add(Url::create('/')
            ->setPriority(1.0)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

        // Adiciona página de login de cliente
        $sitemap->add(Url::create('/login')
            ->setPriority(0.8)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY));

        // Adiciona página de registro de cliente
        $sitemap->add(Url::create('/cliente/registrar')
            ->setPriority(0.7)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY));

        // Adiciona página de esqueci senha do cliente
        $sitemap->add(Url::create('/cliente/esqueci-senha')
            ->setPriority(0.6)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY));

        // Adiciona todos os produtos ativos da loja
        $products = Product::where('is_active', true)->get();

        foreach ($products as $product) {
            $sitemap->add(
                Url::create("/store/product/{$product->slug}")
                    ->setLastModificationDate($product->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.9)
            );
        }

        // Retorna o XML direto para o navegador
        return $sitemap->toResponse(request());
    }
}