<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'cnpj' => fake()->numerify('##.###.###/0001-##'),
            'state_registration' => fake()->numerify('#########'),
            'address' => fake()->streetAddress(),
            'neighborhood' => fake()->word(),
            'city' => fake()->city(),
            'state' => 'SP',
            'zip_code' => '12345-678',
            'contact_name_1' => fake()->name(),
            'phone_1' => fake()->phoneNumber(),
            'is_active' => true,
        ];
    }
}