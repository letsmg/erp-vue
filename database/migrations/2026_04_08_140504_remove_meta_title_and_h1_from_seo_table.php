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
        Schema::table('seo', function (Blueprint $table) {
            // Removendo colunas que são derivadas do product->description
            $table->dropColumn(['meta_title', 'h1']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo', function (Blueprint $table) {
            // Restaurando colunas em caso de rollback
            $table->string('meta_title', 70)->after('seoable_type');
            $table->string('h1')->after('meta_keywords');
        });
    }
};
