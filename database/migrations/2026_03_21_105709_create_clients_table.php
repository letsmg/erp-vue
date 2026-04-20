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
            $table->enum('document_type', ['CPF', 'CNPJ']);
            $table->string('document_number')->unique(); // CPF ou CNPJ
            
            // Email será usado da tabela users (FK) - não duplicar aqui
            
            // Campos fiscais (podem ser nulos para PF)
            $table->string('state_registration')->nullable(); // Inscrição Estadual
            $table->string('municipal_registration')->nullable(); // Inscrição Municipal
            
            // Tipo de contribuinte (1=Contribuinte ICMS, 2=Isento, 9=Não Contribuinte)
            $table->tinyInteger('contributor_type')->nullable();
            
            // Contatos Principais e Secundários
            $table->string('phone1', 20);
            $table->string('contact1'); // Nome da pessoa de contato 1

            $table->string('phone2', 20)->nullable();
            $table->string('contact2')->nullable(); // Nome da pessoa de contato 2 

            // Status do cliente
            $table->boolean('is_active')->default(true);
            
            // Email verification
            $table->timestamp('email_verified_at')->nullable();

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
