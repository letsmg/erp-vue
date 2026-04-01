<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shopping_cart', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com o cliente (user com access_level = 2)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Relacionamento com o produto
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Quantidade e preços
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            
            // Timestamps
            $table->timestamps();
            
            // Índices únicos para evitar duplicação
            $table->unique(['user_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_cart');
    }
};
