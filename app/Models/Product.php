<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
        
    protected $fillable = [
        'supplier_id', 'description', 'brand', 'model', 'size', 
        'collection', 'gender', 'cost_price', 'sale_price', 
        'promo_price', 'promo_start_at', 'promo_end_at',
        'barcode', 'stock_quantity', 'is_active', 'is_featured',
        'slug'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'promo_start_at' => 'datetime',
        'promo_end_at' => 'datetime',
    ];

    // Faz o current_price aparecer automaticamente no Vue/Inertia
    protected $appends = ['current_price', 'seo_display'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function images(): HasMany
    {
        // Ordena por 'order' se você tiver esse campo, para as fotos não embaralharem
        return $this->hasMany(ProductImage::class)->orderBy('id', 'asc');
    }

    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'seoable');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(fn ($product) => 
            $product->slug = $product->slug ?? Str::slug($product->description) . '-' . Str::random(5)
        );
    }

    public function getCurrentPriceAttribute()
    {
        $now = now();
        if ($this->promo_price && $this->promo_start_at && $this->promo_end_at) {
            if ($now->between($this->promo_start_at, $this->promo_end_at)) {
                return (float) $this->promo_price;
            }
        }
        return (float) $this->sale_price;
    }

    public function getSeoDisplayAttribute()
    {
        $seo = $this->seo; // Relacionamento MorphOne

        return [
            'meta_title'       => $seo?->meta_title ?: $this->description,
            'meta_description' => $seo?->meta_description ?: "Confira {$this->description} com o melhor preço na nossa loja.",
            'slug'             => $seo?->slug ?: $this->slug, // Usa o slug do produto se o do SEO sumir
            'h1'               => $seo?->h1 ?: $this->description,
            'meta_keywords'    => $seo?->meta_keywords ?: str_replace(' ', ', ', $this->description),
        ];
    }
}