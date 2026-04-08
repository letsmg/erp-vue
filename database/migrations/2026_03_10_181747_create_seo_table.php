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
        Schema::create('seo', function (Blueprint $table) {
            $table->id();
            
            // Em vez de $table->morphs('seoable'), fazemos manualmente:
            $table->unsignedBigInteger('seoable_id');
            $table->string('seoable_type');
            
            // SEO Fields
            // meta_title e h1 são derivados do product->description (limitado a 70 chars)
            $table->string('meta_description', 160);
            $table->string('meta_keywords');                        
            $table->text('text1');      
            $table->string('h2')->nullable();
            $table->text('text2')->nullable();
            $table->text('schema_markup')->nullable();
            $table->text('google_tag_manager')->nullable();                        
            $table->timestamps();

            // ÍNDICES PARA PERFORMANCE
            $table->index(['seoable_id', 'seoable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo');
    }
};
