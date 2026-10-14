<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecusarDesfiliacaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('desfiliacoes.recusar');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'motivo_recusa' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * Custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'motivo_recusa' => 'justificativa formal de recusa',
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'motivo_recusa.required' => 'É obrigatório informar uma justificativa clara para a recusa da solicitação.',
            'motivo_recusa.min' => 'A justificativa de recusa deve conter no mínimo 10 caracteres explicativos.',
        ];
    }
}
