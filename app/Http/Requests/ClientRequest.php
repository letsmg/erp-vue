<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $clientId = $this->route('client') ?? $this->route('id');
        $isUpdate = !empty($clientId);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'in:CPF,CNPJ'],
            'document_number' => [
                'required',
                'string',
                $isUpdate 
                    ? Rule::unique('clients', 'document_number')->ignore($clientId)
                    : 'unique:clients,document_number',
                function ($attribute, $value, $fail) {
                    $cleanValue = preg_replace('/[^0-9]/', '', $value);
                    $documentType = $this->input('document_type');
                    
                    if ($documentType === 'CPF' && strlen($cleanValue) !== 11) {
                        $fail('CPF deve ter 11 dígitos');
                    }
                    
                    if ($documentType === 'CNPJ' && strlen($cleanValue) !== 14) {
                        $fail('CNPJ deve ter 14 dígitos');
                    }
                },
            ],
            'phone1' => ['nullable', 'string', 'max:20'],
            'contact1' => ['nullable', 'string', 'max:255'],
            'phone2' => ['nullable', 'string', 'max:20'],
            'contact2' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'state_registration' => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    if ($this->input('document_type') === 'CNPJ' && empty($value)) {
                        $fail('Inscrição Estadual é obrigatória para CNPJ');
                    }
                },
            ],
            'municipal_registration' => ['nullable', 'string', 'max:20'],
            'contributor_type' => ['nullable', 'integer', 'in:1,2,9'],
            'is_active' => ['boolean'],
        ];

        // Regras para criação de usuário
        if (!$isUpdate && !$this->input('user_id')) {
            $rules = array_merge($rules, [
                'user_name' => ['required', 'string', 'max:255'],
                'user_email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    'unique:users,email',
                ],
                'user_password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'name.max' => 'O nome não pode ter mais de 255 caracteres',
            'document_type.required' => 'O tipo de documento é obrigatório',
            'document_type.in' => 'Tipo de documento inválido',
            'document_number.required' => 'O número do documento é obrigatório',
            'document_number.unique' => 'Este documento já está cadastrado',
            'state_registration.required' => 'A inscrição estadual é obrigatória para CNPJ',
            'contributor_type.in' => 'Tipo de contribuinte inválido',
            'user_name.required' => 'O nome do usuário é obrigatório',
            'user_email.required' => 'O email é obrigatório',
            'user_email.email' => 'Email inválido',
            'user_email.unique' => 'Este email já está cadastrado',
            'user_password.required' => 'A senha é obrigatória',
            'user_password.min' => 'A senha deve ter no mínimo 8 caracteres',
            'user_password.confirmed' => 'A confirmação de senha não confere',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'document_number' => 'documento',
            'state_registration' => 'inscrição estadual',
            'municipal_registration' => 'inscrição municipal',
            'contributor_type' => 'tipo de contribuinte',
            'user_name' => 'nome do usuário',
            'user_email' => 'email do usuário',
            'user_password' => 'senha',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpa documento para manter apenas números
        if ($this->has('document_number')) {
            $this->merge([
                'document_number' => preg_replace('/[^0-9]/', '', $this->input('document_number')),
            ]);
        }
    }
}
