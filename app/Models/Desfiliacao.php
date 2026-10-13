<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desfiliacao extends Model
{
    use HasFactory;

    protected $table = 'desfiliacoes';

    protected $fillable = [
        'nome_completo',
        'cpf',
        'matricula',
        'empresa',
        'email',
        'telefone',
        'motivo',
        'status',
        'ip_solicitacao',
        'ip_confirmacao',
        'user_agent_confirmacao',
        'email_verificado_em',
        'confirmado_em',
        'assinatura_base64',
        'termo_hash',
        'pdf_path',
    ];

    protected $casts = [
        'email_verificado_em' => 'datetime',
        'confirmado_em' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function isConfirmada(): bool
    {
        return $this->status === 'confirmada';
    }

    /**
     * Calcula o hash SHA-256 exclusivo de validação pública e auditoria probatória.
     */
    public function gerarHashUnico(): string
    {
        $dados = implode('|', [
            (string) $this->id,
            preg_replace('/\D/', '', (string) $this->cpf),
            strtolower((string) $this->email),
            $this->confirmado_em ? $this->confirmado_em->toIso8601String() : now()->toIso8601String(),
            (string) config('app.key'),
        ]);

        return hash('sha256', $dados);
    }

    /**
     * Retorna o CPF formatado no padrão 000.000.000-00.
     */
    public function getCpfFormatadoAttribute(): string
    {
        $digits = preg_replace('/\D/', '', (string) $this->cpf);
        if (strlen($digits) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
        }
        return (string) $this->cpf;
    }

    /**
     * Retorna o telefone formatado.
     */
    public function getTelefoneFormatadoAttribute(): string
    {
        $digits = preg_replace('/\D/', '', (string) $this->telefone);
        if (strlen($digits) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $digits);
        } elseif (strlen($digits) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $digits);
        }
        return (string) $this->telefone;
    }

    /**
     * Verifica se já existe solicitação pendente para o CPF nas últimas 24 horas.
     */
    public static function hasSolicitacaoPendenteRecente(string $cpf): bool
    {
        $cpfLimpo = preg_replace('/\D/', '', $cpf);
        return static::where(function ($q) use ($cpf, $cpfLimpo) {
            $q->where('cpf', $cpf)->orWhere('cpf', $cpfLimpo);
        })
        ->where('status', 'pendente')
        ->where('created_at', '>=', now()->subHours(24))
        ->exists();
    }
}
