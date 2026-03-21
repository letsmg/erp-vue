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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com o login (User)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('name');
            $table->string('document_number')->unique(); // CPF ou CNPJ
            
            // Contatos Principais e Secundários
            $table->string('phone1', 20)->nullable();
            $table->string('contact1')->nullable(); // Nome da pessoa de contato 1
            
            $table->string('phone2', 20)->nullable();
            $table->string('contact2')->nullable(); // Nome da pessoa de contato 2
            
            // Mantive o campo 'phone' original caso queira usar como "Telefone Geral" 
            // ou você pode removê-lo agora que tem o phone1 e phone2.
            $table->string('phone', 20)->nullable(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
