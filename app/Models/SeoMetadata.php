<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMetadata extends Model
{
    protected $fillable = ['slug', 
    'meta_title', 'meta_description', 'meta_keywords',  
    'seoable_id', 'seoable_type'];

    public function seoable()
    {
        // Esse método permite que o SEO descubra a quem ele pertence
        return $this->morphTo();
    }
}