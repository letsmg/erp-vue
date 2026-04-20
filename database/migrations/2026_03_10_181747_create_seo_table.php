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
            $table->string('meta_description', 160);
            $table->string('meta_keywords');
            // IMPORTANT: schema_markup contains JSON-LD structured data and MUST NOT be sanitized
            // This field accepts valid JSON/HTML for SEO purposes
            $table->text('schema_markup')->nullable();
            // IMPORTANT: google_tag_manager contains GTM scripts and MUST NOT be sanitized
            // This field accepts valid HTML/JS for tracking purposes
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
