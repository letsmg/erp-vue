<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar usuários clientes primeiro
        $clientUsers = User::factory()->count(10)->create([
            'access_level' => 2, // CLIENT
            'is_active' => true,
        ]);

        // Para cada usuário cliente, criar um cliente
        foreach ($clientUsers as $user) {
            Client::factory()->create([
                'user_id' => $user->id,
                'document_type' => fake()->randomElement(['CPF', 'CNPJ']),
                'document_number' => fake()->numerify(
                    fake()->randomElement(['###########', '##############'])
                ),
                'state_registration' => fake()->optional(0.7)->numerify('#########'),
                'municipal_registration' => fake()->optional(0.5)->numerify('#########'),
                'contributor_type' => fake()->randomElement([1, 2, 9]),
                'is_active' => true,
            ]);
        }

        // Criar alguns clientes sem usuário (para testes)
        Client::factory()->count(5)->create([
            'user_id' => null,
            'document_type' => fake()->randomElement(['CPF', 'CNPJ']),
            'document_number' => fake()->numerify(
                fake()->randomElement(['###########', '##############'])
            ),
            'state_registration' => fake()->optional(0.7)->numerify('#########'),
            'municipal_registration' => fake()->optional(0.5)->numerify('#########'),
            'contributor_type' => fake()->randomElement([1, 2, 9]),
            'is_active' => true,
        ]);
    }
}
