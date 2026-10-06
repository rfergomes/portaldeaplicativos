<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WhatsappTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $templateId = $this->route('whatsapp_template') ?? $this->route('id');
        if (is_object($templateId)) {
            $templateId = $templateId->id;
        }

        return [
            'nome' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_\-]+$/',
                Rule::unique('whatsapp_templates', 'nome')->ignore($templateId),
            ],
            'descricao' => ['required', 'string', 'max:255'],
            'corpo_exemplo' => ['nullable', 'string'],
            'parametros_esperados' => ['nullable'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do template é obrigatório.',
            'nome.unique' => 'Já existe um template cadastrado com este nome.',
            'nome.regex' => 'O nome do template deve conter apenas letras, números, traços e underscores (sem espaços).',
            'descricao.required' => 'A descrição do template é obrigatória.',
        ];
    }
}
