<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Seo extends Model
{
    protected $table = 'seo';

    protected $fillable = [
        'meta_description', 'meta_keywords', 
        'text1', 'h2', 'text2', 
        'schema_markup', 'google_tag_manager', 
        'seoable_id', 'seoable_type'
    ];

    /**
     * IMPORTANTE: Faz o Laravel entender meta_keywords como Array 
     * em vez de uma string pura.
     */
    protected $casts = [
        'meta_keywords' => 'array',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * meta_title é derivado do description do produto (limitado a 70 chars)
     * Acessado via $product->description no frontend
     */
    public function getMetaTitleAttribute($value)
    {
        // Retorna vazio - o frontend deve usar product->description limitado a 70 chars
        return null;
    }

    public function getMetaDescriptionAttribute($value)
    {
        $desc = $value ?: 'Confira os detalhes deste produto em nossa loja oficial.';
        return htmlspecialchars($desc, ENT_QUOTES, 'UTF-8');
    }

    public function getMetaKeywordsAttribute($value)
    {
        $keywords = $this->castAttribute('meta_keywords', $value);
        
        if (is_string($keywords)) {
            $keywords = json_decode($keywords, true) ?: explode(',', $keywords);
        }

        $keywords = is_array($keywords) ? $keywords : [];
        
        return array_map(fn($kw) => htmlspecialchars(trim($kw), ENT_QUOTES, 'UTF-8'), $keywords);
    }

    /**
     * h1 é derivado do description do produto
     * Acessado via $product->description no frontend
     */
    public function getH1Attribute($value)
    {
        // Retorna vazio - o frontend deve usar product->description
        return null;
    }

    public function getH2Attribute($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public function getText1Attribute($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public function getText2Attribute($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public function getSlugAttribute($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}