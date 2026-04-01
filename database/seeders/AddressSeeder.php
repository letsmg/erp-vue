<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Client;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();

        foreach ($clients as $index => $client) {
            // Cada cliente terá entre 1 e 3 endereços
            $addressCount = fake()->numberBetween(1, 3);
            
            for ($i = 1; $i <= $addressCount; $i++) {
                Address::factory()->create([
                    'client_id' => $client->id,
                    'is_delivery_address' => $i === 1, // Primeiro endereço é o de entrega
                ]);
            }
        }
    }
}
