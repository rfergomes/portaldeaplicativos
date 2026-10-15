<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GerarDesfiliacaoLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && (
            $this->user()->can('desfiliacoes.links.gerar') ||
            $this->user()->can('desfiliacoes.links.gerar.proprios')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tipo_link' => ['nullable', 'in:individual,compartilhado,lote'],
            'destinatario_nome' => ['nullable', 'string', 'max:191'],
            'destinatario_contato' => ['nullable', 'string', 'max:50'],
            'canal_envio' => ['nullable', 'in:manual,whatsapp,email,kwik'],
            'horas_validade' => ['required', 'integer', 'min:1', 'max:720'],
            'limite_usos' => ['nullable', 'integer', 'min:1', 'max:50'],
            'quantidade' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'tipo_link' => 'modalidade do link',
            'destinatario_nome' => 'nome do trabalhador',
            'destinatario_contato' => 'contato (WhatsApp/E-mail)',
            'canal_envio' => 'canal de envio',
            'horas_validade' => 'validade em horas',
            'limite_usos' => 'limite de utilizações',
            'quantidade' => 'quantidade de links',
        ];
    }
}
