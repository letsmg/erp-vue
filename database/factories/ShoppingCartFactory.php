<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShoppingCart>
 */
class ShoppingCartFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ShoppingCart::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 10, 500);
        $quantity = fake()->numberBetween(1, 10);

        return [
            'user_id' => User::factory()->client(), // Apenas usuários clientes
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
        ];
    }

    /**
     * Carrinho com quantidade baixa
     */
    public function lowQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(1, 3),
        ]);
    }

    /**
     * Carrinho com quantidade alta
     */
    public function highQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(5, 20),
        ]);
    }

    /**
     * Carrinho com produtos baratos
     */
    public function cheapProducts(): static
    {
        $unitPrice = fake()->randomFloat(2, 5, 50);
        
        return $this->state(fn (array $attributes) => [
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * ($attributes['quantity'] ?? 1),
        ]);
    }

    /**
     * Carrinho com produtos caros
     */
    public function expensiveProducts(): static
    {
        $unitPrice = fake()->randomFloat(2, 100, 1000);
        
        return $this->state(fn (array $attributes) => [
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * ($attributes['quantity'] ?? 1),
        ]);
    }

    /**
     * Carrinho para um usuário específico
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Carrinho com um produto específico
     */
    public function forProduct(Product $product): static
    {
        $unitPrice = $product->sale_price ?? $product->cost_price ?? 100;
        
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * ($attributes['quantity'] ?? 1),
        ]);
    }
}
