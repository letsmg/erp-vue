<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     * Sanitizes all string inputs by trimming and stripping HTML tags.
     * EXCLUDES: schema_markup and google_tag_manager (SEO fields that require raw HTML/JS)
     */
    protected function prepareForValidation(): void
    {
        $this->merge(
            collect($this->all())->map(function ($value, $key) {
                if (is_string($value)) {
                    // EXCLUDE schema_markup and google_tag_manager from sanitization
                    // These SEO fields require raw HTML/JS for JSON-LD and GTM scripts
                    if (in_array($key, ['schema_markup', 'google_tag_manager'])) {
                        return $value;
                    }
                    // Remove HTML tags and trim whitespace
                    return trim(strip_tags($value));
                }
                return $value;
            })->toArray()
        );
    }
}
