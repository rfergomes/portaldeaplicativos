<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DesfiliacaoLink extends Model
{
    use HasFactory;

    protected $table = 'desfiliacao_links';

    protected $fillable = [
        'token',
        'user_id',
        'destinatario_nome',
        'destinatario_contato',
        'canal_envio',
        'horas_validade',
        'expira_em',
        'utilizado',
        'utilizado_em',
        'cancelado_em',
        'desfiliacao_id',
    ];

    protected $casts = [
        'expira_em' => 'datetime',
        'utilizado' => 'boolean',
        'utilizado_em' => 'datetime',
        'cancelado_em' => 'datetime',
        'horas_validade' => 'integer',
    ];

    /**
     * Relação com o usuário que gerou o link.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relação com a desfiliação resultante.
     */
    public function desfiliacao(): BelongsTo
    {
        return $this->belongsTo(Desfiliacao::class);
    }

    /**
     * Gera um token alfanumérico seguro único de 64 caracteres.
     */
    public static function gerarToken(): string
    {
        do {
            $token = Str::random(64);
        } while (static::where('token', $token)->exists());

        return $token;
    }

    /**
     * Verifica se o link é válido para utilização no momento.
     */
    public function isValido(): bool
    {
        return !$this->utilizado && $this->cancelado_em === null && $this->expira_em->isFuture();
    }

    /**
     * Verifica se o link já expirou sem ser utilizado.
     */
    public function isExpirado(): bool
    {
        return !$this->utilizado && $this->cancelado_em === null && $this->expira_em->isPast();
    }

    /**
     * Verifica se o link foi cancelado manualmente.
     */
    public function isCancelado(): bool
    {
        return $this->cancelado_em !== null;
    }

    /**
     * URL completa de acesso do trabalhador.
     */
    public function getUrlAttribute(): string
    {
        return url("/desfiliacao/solicitar/{$this->token}");
    }

    /**
     * Gera URL de compartilhamento pré-formatada via WhatsApp Web/App (wa.me).
     */
    public function getWhatsappUrlAttribute(): string
    {
        $telefoneLimpo = preg_replace('/\D/', '', (string) $this->destinatario_contato);
        if ($telefoneLimpo && !str_starts_with($telefoneLimpo, '55')) {
            $telefoneLimpo = '55' . $telefoneLimpo;
        }

        $saudacao = $this->destinatario_nome ? "Olá {$this->destinatario_nome}," : "Olá,";
        $mensagem = "{$saudacao}\n\nConforme sua solicitação junto ao Sindicato Químicos Unificados, segue o seu link exclusivo e seguro para preenchimento do formulário de desfiliação:\n\n{$this->url}\n\n*Atenção:* Este link é de uso único e expira em " . $this->expira_em->format('d/m/Y \à\s H:i') . ".";

        $urlBase = $telefoneLimpo ? "https://wa.me/{$telefoneLimpo}" : "https://wa.me/";

        return $urlBase . '?text=' . rawurlencode($mensagem);
    }

    /**
     * Scope para links válidos.
     */
    public function scopeValidos(Builder $query): Builder
    {
        return $query->where('utilizado', false)
            ->whereNull('cancelado_em')
            ->where('expira_em', '>', now());
    }

    /**
     * Scope para links expirados.
     */
    public function scopeExpirados(Builder $query): Builder
    {
        return $query->where('utilizado', false)
            ->whereNull('cancelado_em')
            ->where('expira_em', '<=', now());
    }
}
