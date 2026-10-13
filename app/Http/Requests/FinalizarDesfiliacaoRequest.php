<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinalizarDesfiliacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assinatura_base64' => [
                'required',
                'string',
                'starts_with:data:image/png;base64,',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    // Verificar se não é um canvas em branco ou muito curto
                    if (strlen((string) $value) < 500) {
                        $fail('A rubrica desenhada não é válida. Por favor, assine no campo indicado antes de prosseguir.');
                    }
                },
            ],
            'aceite_termos' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'assinatura_base64.required' => 'A rubrica manuscrita na tela é obrigatória.',
            'assinatura_base64.starts_with' => 'Formato de imagem de assinatura inválido.',
            'aceite_termos.required' => 'Você deve declarar ciência e concordância com os termos do requerimento.',
            'aceite_termos.accepted' => 'Você deve marcar a caixa confirmando ciência e consentimento aos termos.',
        ];
    }
}
