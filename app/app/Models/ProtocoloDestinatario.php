<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocoloDestinatario extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocolo_id',
        'nome',
        'email',
        'telefone',
        'cpf_cnpj',
        'endereco',
        'metadados',
    ];

    protected $casts = [
        'metadados' => 'array',
        'endereco' => 'array',
    ];

    public function protocolo()
    {
        return $this->belongsTo(Protocolo::class);
    }

    public function envios()
    {
        return $this->hasMany(ProtocoloEnvio::class, 'destinatario_id');
    }

    /**
     * Retorna o telefone higienizado (apenas dígitos, incluindo código do país).
     * Formato esperado pela API AR-Online: 5519912345678
     */
    public function telefoneSanitizado(): ?string
    {
        if (!$this->telefone) {
            return null;
        }

        $apenas_digitos = preg_replace('/\D/', '', $this->telefone);

        // Garante o prefixo 55 (Brasil)
        if (!str_starts_with($apenas_digitos, '55')) {
            $apenas_digitos = '55' . $apenas_digitos;
        }

        return $apenas_digitos;
    }

    /**
     * Valida se o telefone é um celular brasileiro válido.
     * Padrão: +55 DDD (2 dígitos) + 9 dígitos (começa com 9).
     */
    public function isCelularValido(): bool
    {
        $sanitizado = $this->telefoneSanitizado();

        if (!$sanitizado) {
            return false;
        }

        // 55 + DDD (2) + dígito 9 + 8 dígitos = 13 dígitos total
        return (bool) preg_match('/^55\d{2}9\d{8}$/', $sanitizado);
    }
}

