<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Seo;


class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'supplier_id'    => Supplier::factory(),
            'category_id'    => Category::factory(),
            'title'          => ucfirst($this->faker->words(3, true)),
            'subtitle'       => $this->faker->sentence(),
            'description'    => $this->faker->paragraph(),
            'features'       => $this->faker->paragraph(),
            'brand'          => $this->faker->company(),
            'model'          => $this->faker->bothify('??-###'),
            'size'           => $this->faker->randomElement(['P', 'M', 'G', 'GG']),
            'collection'     => 'Coleção ' . $this->faker->word(),
            'gender'         => $this->faker->randomElement(['Masculino', 'Feminino', 'Unissex']),
            'cost_price'     => $this->faker->randomFloat(2, 50, 150),
            'sale_price'     => $this->faker->randomFloat(2, 200, 500),
            'barcode'        => $this->faker->ean13(),
            'stock_quantity' => $this->faker->numberBetween(10, 100),
            'is_active'      => true,
            'is_featured'    => $this->faker->boolean(30), // 30% de chance de ser featured
            'weight'         => $this->faker->randomFloat(2, 0.1, 5),
            'width'          => $this->faker->numberBetween(10, 100),
            'height'         => $this->faker->numberBetween(10, 100),
            'length'         => $this->faker->numberBetween(10, 100),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Product $product) {
            // meta_title e h1 são derivados do product->title
            $product->seo()->create([
                'meta_description' => "Compre " . $product->title . " com as melhores condições.",
                'meta_keywords'    => implode(',', $this->faker->words(5)),
            ]);
        });
    }
}