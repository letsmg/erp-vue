<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class ShoppingCartRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Apenas usuários logados podem manipular o carrinho
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [];

        // Regras para adicionar item ao carrinho
        if ($this->isMethod('POST') && $this->has('product_id')) {
            $rules = [
                'product_id' => [
                    'required',
                    'integer',
                    'exists:products,id,deleted_at,NULL,is_active,1',
                ],
                'quantity' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:100',
                    function ($attribute, $value, $fail) {
                        $product = Product::find($this->input('product_id'));
                        if ($product && $value > $product->stock_quantity) {
                            $fail('Quantidade indisponível em estoque. Máximo: ' . $product->stock_quantity);
                        }
                    },
                ],
            ];
        }

        // Regras para atualizar quantidade
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = [
                'quantity' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:100',
                    function ($attribute, $value, $fail) {
                        $cartItem = \App\Models\ShoppingCart::find($this->route('cart_item'));
                        if ($cartItem && $cartItem->product) {
                            if ($value > $cartItem->product->stock_quantity) {
                                $fail('Quantidade indisponível em estoque. Máximo: ' . $cartItem->product->stock_quantity);
                            }
                        }
                    },
                ],
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'O produto é obrigatório',
            'product_id.exists' => 'Produto não encontrado ou não disponível',
            'quantity.required' => 'A quantidade é obrigatória',
            'quantity.min' => 'A quantidade deve ser no mínimo 1',
            'quantity.max' => 'A quantidade máxima por item é 100',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'produto',
            'quantity' => 'quantidade',
        ];
    }
}
