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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos
            $table->foreignId('client_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Vendedor
            $table->foreignId('address_id')->nullable()->constrained()->onDelete('set null'); // Endereço de entrega
            
            // Dados da NF-e (Grupo A)
            $table->char('cUF', 2)->nullable(); // Código da UF do emitente
            $table->string('natOp', 60)->nullable(); // Natureza da Operação
            $table->char('mod', 2)->default('55'); // Modelo do documento fiscal (55 ou 65)
            $table->string('serie', 3)->default('001'); // Série da nota fiscal
            $table->integer('nNF')->nullable(); // Número da NF-e
            $table->timestamp('dhEmi')->nullable(); // Data e hora de emissão
            $table->char('tpNF', 1)->default('1'); // Tipo de documento (0-Entrada, 1-Saída)
            $table->char('idDest', 1)->nullable(); // Identificador de local de destino
            $table->string('chNFe', 44)->nullable(); // Chave de Acesso da NF-e
            
            // Totais (Grupo W)
            $table->decimal('vBC', 15, 2)->default(0); // Base de cálculo ICMS
            $table->decimal('vICMS', 15, 2)->default(0); // Valor do ICMS
            $table->decimal('vIPI', 15, 2)->default(0); // Valor do IPI
            $table->decimal('vPIS', 15, 2)->default(0); // Valor do PIS
            $table->decimal('vCOFINS', 15, 2)->default(0); // Valor do COFINS
            $table->decimal('vFrete', 15, 2)->default(0); // Valor do frete
            $table->decimal('vSeg', 15, 2)->default(0); // Valor do seguro
            $table->decimal('vDesc', 15, 2)->default(0); // Valor do desconto
            $table->decimal('vNF', 15, 2)->default(0); // Valor total da nota fiscal
            
            // Transporte (Grupo X)
            $table->char('modFrete', 1)->default('0'); // Modalidade do frete (0-CIF, 1-FOB)
            
            // Pagamento (Grupo Y)
            $table->char('tPag', 2)->default('01'); // Forma de pagamento
            $table->decimal('vPag', 15, 2)->default(0); // Valor pago
            
            // Informações Adicionais (Grupo Z)
            $table->text('infCpl')->nullable(); // Informações complementares
            
            // Protocolo de Autorização (Grupo PR)
            $table->string('nProt', 15)->nullable(); // Número do protocolo
            $table->string('digVal', 28)->nullable(); // Digest Value
            
            // Campos originais
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamp('sale_date')->useCurrent();
            $table->string('status')->default('pending'); // pending, paid, canceled
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
