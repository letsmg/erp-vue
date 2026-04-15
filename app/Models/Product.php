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
        'supplier_id','category_id', 'title', 'subtitle', 'description', 'features', 'brand', 'model', 'size',
        'collection', 'gender', 'cost_price', 'sale_price',
        'promo_price', 'promo_start_at', 'promo_end_at',
        'barcode', 'stock_quantity', 'is_active', 'is_featured',
        'slug',
        'weight', 'width', 'height', 'length', 'free_shipping'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'free_shipping' => 'boolean', 
        'promo_start_at' => 'datetime',
        'promo_end_at' => 'datetime',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'promo_price' => 'decimal:2',
        'weight' => 'decimal:3',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'length' => 'decimal:2',
    ];

    protected $appends = ['current_price', 'seo_display'];

    
    /**
     * Eventos do Model
     */
    protected static function booted()
    {
        // Criar slug automaticamente
        static::creating(function ($product) {
            if (!$product->slug) {
                $product->slug = self::generateUniqueSlug($product->title);
            }
        });

        // Atualizar slug se título mudar (opcional 🔥)
        static::updating(function ($product) {
            if ($product->isDirty('title')) {
                $product->slug = self::generateUniqueSlug($product->title);
            }
        });

        // Delete em cascata
        static::deleting(function ($product) {
            if ($product->seo) {
                $product->seo()->delete();
            }

            $product->images()->delete();
        });
    }

    /**
     * 🔥 Geração de slug único (melhor que random puro)
     */
    public static function generateUniqueSlug($text)
    {
        $baseSlug = Str::slug($text);
        $slug = $baseSlug;
        $count = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $count++;
        }

        return $slug;
    }

    /**
     * RELAÇÕES
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc');
    }
    
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    /**
     * ACCESSORS
     */
    public function getCurrentPriceAttribute()
    {
        $now = now();

        if (
            $this->promo_price &&
            $this->promo_start_at &&
            $this->promo_end_at &&
            $now->between($this->promo_start_at, $this->promo_end_at)
        ) {
            return (float) $this->promo_price;
        }

        return (float) $this->sale_price;
    }

    public function getSeoDisplayAttribute()
    {
        $seo = $this->seo;

        return [
            'meta_title'       => $seo?->meta_title ?: $this->title,
            'meta_description' => $seo?->meta_description ?: "Confira {$this->title} com o melhor preço na nossa loja.",
            'h1'               => $seo?->h1 ?: $this->title,
            'meta_keywords'    => $seo?->meta_keywords ?: str_replace(' ', ', ', $this->title),
            'slug'             => $this->slug,
            'canonical_url'    => config('app.url') . '/store/product/' . $this->slug,
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}