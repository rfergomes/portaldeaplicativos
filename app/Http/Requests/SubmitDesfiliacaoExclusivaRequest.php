<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitDesfiliacaoExclusivaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Rota pública protegida por token exclusivo
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome_completo' => ['required', 'string', 'min:3', 'max:191'],
            'cpf' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $c = preg_replace('/\D/', '', (string) $value);
                    if (strlen($c) !== 11 || preg_match("/^{$c[0]}{11}$/", $c)) {
                        $fail('O CPF informado é inválido.');
                        return;
                    }
                    for ($t = 9; $t < 11; $t++) {
                        for ($d = 0, $cIndex = 0; $cIndex < $t; $cIndex++) {
                            $d += (int) $c[$cIndex] * (($t + 1) - $cIndex);
                        }
                        $d = ((10 * $d) % 11) % 10;
                        if ((int) $c[$cIndex] !== $d) {
                            $fail('O CPF informado é inválido.');
                            return;
                        }
                    }
                },
            ],
            'empresa' => ['required', 'string', 'min:2', 'max:191'],
            'email' => ['required', 'email', 'max:191'],
            'telefone' => ['required', 'string', 'min:10', 'max:20'],
            'motivo' => ['nullable', 'string', 'max:1000'],
            'aceite_lgpd' => ['required', 'accepted'],
            'assinatura_base64' => ['required', 'string', 'starts_with:data:image/'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'nome_completo' => 'nome completo',
            'cpf' => 'CPF',
            'empresa' => 'empresa empregadora',
            'email' => 'e-mail',
            'telefone' => 'telefone / WhatsApp',
            'motivo' => 'motivo da desfiliação',
            'aceite_lgpd' => 'aceite dos termos de privacidade e LGPD',
            'assinatura_base64' => 'assinatura manuscrita digitalizada',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'aceite_lgpd.accepted' => 'É obrigatório declarar ciência e aceitar os termos de tratamento de dados (LGPD).',
            'assinatura_base64.required' => 'Por favor, desenhe sua rubrica manuscrita no campo de assinatura antes de enviar.',
            'assinatura_base64.starts_with' => 'A assinatura digitalizada enviada é inválida.',
        ];
    }
}
