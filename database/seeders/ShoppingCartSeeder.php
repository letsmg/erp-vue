<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShoppingCartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pegar apenas usuários clientes
        $clientUsers = User::where('access_level', 2)->get();
        $products = Product::where('is_active', true)->get();

        foreach ($clientUsers as $user) {
            // Cada cliente terá entre 0 e 5 itens no carrinho
            $cartItemCount = fake()->numberBetween(0, 5);
            
            for ($i = 0; $i < $cartItemCount; $i++) {
                $product = $products->random();
                $quantity = fake()->numberBetween(1, 5);
                $unitPrice = $product->sale_price ?? $product->cost_price ?? fake()->randomFloat(2, 10, 500);
                
                ShoppingCart::factory()->create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $quantity,
                ]);
            }
        }
    }
}
