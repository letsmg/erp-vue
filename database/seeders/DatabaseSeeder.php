<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Client;
use App\Models\Address;
use App\Models\Product;
use App\Models\ShoppingCart;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Só roda os seeders se as tabelas estiverem vazias

        // Só roda o SingleUserSeeder se não houver usuários
        if (User::count() == 0) {
            $this->call(SingleUserSeeder::class);
        }

        // Só roda o UserSeeder se não houver usuários (apenas cria usuários aleatórios se count < 5)
        if (User::count() < 5) {
            $this->call(UserSeeder::class);
        }

        // Só roda o SupplierSeeder se não houver fornecedores
        if (Supplier::count() == 0) {
            $this->call(SupplierSeeder::class);
        }

        // Só roda o CategorySeeder se não houver categorias
        if (Category::count() == 0) {
            $this->call(CategorySeeder::class);
        }

        // Só roda o ClientSeeder se não houver clientes
        if (Client::count() == 0) {
            $this->call(ClientSeeder::class);
        }

        // Só roda o AddressSeeder se não houver endereços
        if (Address::count() == 0) {
            $this->call(AddressSeeder::class);
        }

        // Só roda o ProductSeeder se não houver produtos
        if (Product::count() == 0) {
            $this->call(ProductSeeder::class);
        }

        // Só roda o ShoppingCartSeeder se não houver carrinhos de compras
        if (ShoppingCart::count() == 0) {
            $this->call(ShoppingCartSeeder::class);
        }
    }
}
