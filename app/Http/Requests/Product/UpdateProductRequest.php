<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseFormRequest;

class UpdateProductRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Dados Básicos
            'supplier_id'     => 'required|exists:suppliers,id',
            'title'           => 'required|string|max:150',
            'subtitle'        => 'nullable|string|max:200',
            'description'     => 'nullable|string',
            'features'        => 'nullable|string',
            'brand'           => 'required|nullable|string|max:100',
            'category_id'     => 'required','exists:categories,id',
            'model'           => 'required|nullable|string|max:100',
            'size'            => 'nullable|string|max:50',
            'collection'      => 'nullable|string|max:100',
            'gender'          => 'required|nullable|string|max:50',
            'barcode'         => 'nullable|string|max:100',
            'cost_price'      => 'required|required|numeric|min:0',
            'sale_price'      => 'required|required|numeric|min:0',
            'stock_quantity'  => 'required|required|integer|min:0',
            'is_active'       => 'boolean',
            'is_featured'     => 'boolean',

            // Promoção
            'promo_price'    => 'nullable|numeric|min:0',
            'promo_start_at' => 'nullable|date',
            'promo_end_at'   => 'nullable|date|after_or_equal:promo_start_at',

            // Status e Logística (Dica: use nullable para campos que podem vir vazios)
            'weight' => 'required|numeric|min:0',
            'width'  => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'length' => 'required|numeric|min:0',
            'length'          => 'required|numeric|min:1',
            'free_shipping'   => 'boolean',

            // SEO & Conteúdo (Sincronizado com seu novo Layout)
            'meta_description'  => 'required|string|max:160',
            'meta_keywords'     => 'required|string',
            'slug'              => 'required|string', // Alterado de url para string para evitar erros de prefixo

            'schema_markup'     => 'nullable|string',
            'google_tag_manager'=> 'nullable|string',            

            // Gestão de Imagens
            'existing_images'      => 'nullable|array',
            'existing_images.*.id' => 'required|integer|exists:product_images,id',
            'removed_images'       => 'nullable|array',
            'removed_images.*'     => 'integer|exists:product_images,id',
            
            'new_images'           => 'nullable|array|max:6',
            'new_images.*'         => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            // Dados Básicos
            'supplier_id.required' => 'Selecione um fornecedor para este produto.',
            'supplier_id.exists'   => 'O fornecedor selecionado é inválido.',
            'category_id.exists'   => 'A categoria é obrigatória.',
            'title.required' => 'O título do produto é obrigatório.',
            'title.max'      => 'O título não deve ultrapassar 150 caracteres.',
            'cost_price.required'  => 'Informe o preço de custo.',
            'sale_price.required'  => 'Informe o preço de venda.',
            'stock_quantity.required' => 'Informe a quantidade em estoque.',

            // Logística e Frete
            'weight.required' => 'O peso é obrigatório para o cálculo de frete.',
            'weight.min'      => 'O peso deve ser maior que zero (mínimo 0.001kg).',
            'width.required'  => 'A largura é obrigatória.',
            'width.min'       => 'A largura deve ser de no mínimo 1cm.',
            'height.required' => 'A altura é obrigatória.',
            'height.min'      => 'A altura deve ser de no mínimo 1cm.',
            'length.required' => 'O comprimento é obrigatório.',
            'length.min'      => 'O comprimento deve ser de no mínimo 1cm.',

            // Promoção
            'promo_price.numeric' => 'O preço promocional deve ser um número válido.',
            'promo_start_at.date' => 'Informe uma data de início válida.',
            'promo_end_at.date'   => 'Informe uma data de término válida.',
            'promo_end_at.after_or_equal' => 'A data de término da promoção deve ser igual ou posterior ao início.',

            // SEO
            'meta_description.max' => 'A meta descrição não deve ultrapassar 160 caracteres.',            

            // Imagens
            'images.required' => 'Você precisa enviar pelo menos uma imagem para cadastrar o produto.',
            'images.min'      => 'Envie ao menos :min imagem.',
            'images.max'      => 'Você pode enviar no máximo 6 imagens.',
            'images.*.image'  => 'O arquivo enviado deve ser uma imagem válida.',
            'images.*.mimes'  => 'As imagens devem ser do tipo: jpg, jpeg, png ou webp.',
            'images.*.max'    => 'Cada imagem não pode ser maior que 2MB.',
            
            // Mensagens para o Update (Imagens Existentes)
            'new_images.max'  => 'Você pode carregar no máximo 6 novas imagens.',
            'existing_images.*.id.exists' => 'Uma das imagens enviadas não foi encontrada no banco de dados.',
        ];
    }

    protected function passedValidation()
    {
        // Garante que o produto não fique sem nenhuma imagem após a edição
        $totalImages = count($this->existing_images ?? []) + count($this->file('new_images') ?? []);
        
        if ($totalImages < 1) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'new_images' => 'O produto deve manter pelo menos uma imagem ativa.'
            ]);
        }
    }
}