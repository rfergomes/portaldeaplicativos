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
        'recepcionado_por_user_id',
        'recepcionado_em',
        'observacao_recepcao',
        'efetivado_por_user_id',
        'efetivado_em',
        'protocolo_abaco',
        'observacao_efetivacao',
    ];

    protected $casts = [
        'email_verificado_em' => 'datetime',
        'confirmado_em' => 'datetime',
        'recepcionado_em' => 'datetime',
        'efetivado_em' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function recepcionadoPor()
    {
        return $this->belongsTo(User::class, 'recepcionado_por_user_id');
    }

    public function efetivadoPor()
    {
        return $this->belongsTo(User::class, 'efetivado_por_user_id');
    }

    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function isConfirmada(): bool
    {
        return $this->status === 'confirmada';
    }

    public function isRecepcionada(): bool
    {
        return $this->status === 'recepcionada';
    }

    public function isEfetivada(): bool
    {
        return $this->status === 'efetivada';
    }

    /**
     * Retorna se a desfiliação já foi assinada e concluída pelo trabalhador,
     * estando formalmente válida independentemente da etapa de tramitação interna
     * (confirmada, recepcionada pelo sindicato ou baixada no Ábaco).
     */
    public function isAssinada(): bool
    {
        return in_array($this->status, ['confirmada', 'recepcionada', 'efetivada'], true);
    }

    public function scopePendentesRecepcao($query)
    {
        return $query->where('status', 'confirmada');
    }

    public function scopePendentesBaixaAbaco($query)
    {
        return $query->where('status', 'recepcionada');
    }

    public function scopePendentesInternas($query)
    {
        return $query->whereIn('status', ['confirmada', 'recepcionada']);
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
