<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Desfiliacao;
use Illuminate\Foundation\Http\FormRequest;

class StoreDesfiliacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome_completo' => ['required', 'string', 'min:3', 'max:191'],
            'cpf' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $cpf = preg_replace('/\D/', '', (string) $value);

                    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
                        $fail('O CPF informado é inválido.');
                        return;
                    }

                    // Validação dos dígitos verificadores
                    for ($t = 9; $t < 11; $t++) {
                        $d = 0;
                        for ($c = 0; $c < $t; $c++) {
                            $d += (int) $cpf[$c] * (($t + 1) - $c);
                        }
                        $d = ((10 * $d) % 11) % 10;
                        if ((int) $cpf[$c] !== $d) {
                            $fail('O CPF informado possui dígitos verificadores inválidos.');
                            return;
                        }
                    }

                    // Verificar duplicação de solicitação pendente nas últimas 24 horas
                    if (Desfiliacao::hasSolicitacaoPendenteRecente((string) $value)) {
                        $fail('Já existe uma solicitação de desfiliação pendente para este CPF enviada nas últimas 24 horas. Verifique sua caixa de entrada de e-mail.');
                    }
                },
            ],
            'empresa' => ['required', 'string', 'min:2', 'max:191'],
            'email' => ['required', 'email', 'max:191'],
            'telefone' => ['required', 'string', 'min:10', 'max:20'],
            'motivo' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome_completo.required' => 'O nome completo é obrigatório.',
            'nome_completo.min' => 'O nome completo deve ter no mínimo 3 caracteres.',
            'cpf.required' => 'O CPF é obrigatório.',
            'empresa.required' => 'A empresa empregadora é obrigatória.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'telefone.required' => 'O telefone/WhatsApp de contato é obrigatório.',
            'telefone.min' => 'Informe um telefone válido com DDD.',
        ];
    }
}
