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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com a venda/pedido
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            
            // Dados do pagamento Mercado Pago
            $table->string('payment_id')->nullable(); // ID do pagamento no Mercado Pago
            $table->string('preference_id')->nullable(); // ID da preferência de pagamento
            $table->string('payment_type')->nullable(); // credit_card, pix, boleto
            $table->string('status')->default('pending'); // pending, approved, rejected, in_process
            $table->string('status_detail')->nullable(); // Detalhes do status
            
            // Valores
            $table->decimal('transaction_amount', 15, 2);
            $table->decimal('net_amount', 15, 2)->nullable();
            $table->decimal('tax_amount', 15, 2)->default(0);
            
            // Dados do cartão (se aplicável)
            $table->string('cardholder_name')->nullable();
            $table->string('card_last_four')->nullable();
            $table->string('card_first_six')->nullable();
            $table->string('card_brand')->nullable();
            
            // Dados PIX (se aplicável)
            $table->string('pix_code')->nullable();
            $table->timestamp('pix_expiration_date')->nullable();
            
            // Dados do boleto (se aplicável)
            $table->string('barcode')->nullable();
            $table->timestamp('due_date')->nullable();
            
            // Metadados
            $table->json('metadata')->nullable();
            $table->string('external_reference')->nullable();
            
            // Timestamps
            $table->timestamp('date_approved')->nullable();
            $table->timestamp('date_created')->nullable();
            $table->timestamp('date_last_updated')->nullable();
            $table->timestamp('payment_method_created_at')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index('payment_id');
            $table->index('preference_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
