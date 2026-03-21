<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para a criação de um produto.
     */
    public function rules(): array
    {
        return [
            // Dados Básicos
            'supplier_id'    => 'required|exists:suppliers,id',
            'description'     => 'required|string|max:255',            
            'brand'           => 'nullable|string|max:100',
            'model'           => 'nullable|string|max:100',
            'cost_price'      => 'required|numeric|min:0',
            'sale_price'      => 'required|numeric|min:0',
            'stock_quantity'  => 'required|integer|min:0',
            
            // SEO (Campos Nullable - Fallback no Model)
            'meta_title'        => 'nullable|string|max:70',
            'meta_description'  => 'nullable|string|max:160',
            'meta_keywords'     => 'nullable|string',
            'canonical_url'     => 'nullable|url',
            'h1'                => 'nullable|string',
            'text1'             => 'nullable|string',
            'h2'                => 'nullable|string',
            'text2'             => 'nullable|string',
            'schema_markup'     => 'nullable|string',
            'google_tag_manager'=> 'nullable|string',
            'ads'               => 'nullable|string',

            // Imagens (Obrigatórias na criação)
            'images'          => 'required|array|min:1|max:6',
            'images.*'        => 'image|mimes:jpg,jpeg,png|max:2048', 
        ];
    }

    /**
     * Mensagens de erro personalizadas.
     */
    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Selecione um fornecedor.',
            'description.required' => 'A descrição do produto é obrigatória.',
            'images.required'      => 'Você precisa enviar pelo menos uma imagem para o produto.',
            'images.min'           => 'Envie ao menos :min imagem.',
            'images.max'           => 'O limite máximo é de :max imagens.',
            'images.*.image'       => 'O arquivo deve ser uma imagem válida.',
            'images.*.mimes'       => 'Apenas imagens JPG, JPEG e PNG são permitidas.',
            'images.*.max'         => 'Cada imagem não pode ser maior que 2MB.',
        ];
    }
}