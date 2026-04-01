<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'zip_code' => fake()->numerify('#####-###'),
            'street' => fake()->streetName(),
            'number' => fake()->buildingNumber(),
            'neighborhood' => fake()->word(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'complement' => fake()->optional(0.4)->secondaryAddress(),
            'is_delivery_address' => false,
        ];
    }

    /**
     * Indica que este é o endereço de entrega
     */
    public function deliveryAddress(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_delivery_address' => true,
        ]);
    }

    /**
     * Indica que este é um endereço comercial
     */
    public function commercial(): static
    {
        return $this->state(fn (array $attributes) => [
            'street' => fake()->streetAddress(),
            'complement' => fake()->optional(0.6)->secondaryAddress(),
        ]);
    }

    /**
     * Indica que este é um endereço residencial
     */
    public function residential(): static
    {
        return $this->state(fn (array $attributes) => [
            'street' => fake()->streetName(),
            'complement' => fake()->optional(0.3)->secondaryAddress(),
        ]);
    }

    /**
     * Endereço em São Paulo (para testes)
     */
    public function saoPaulo(): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => fake()->numerify('#####-###'),
        ]);
    }
}
