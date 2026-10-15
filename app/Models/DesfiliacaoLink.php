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
        'tipo_link',
        'destinatario_nome',
        'destinatario_contato',
        'canal_envio',
        'horas_validade',
        'limite_usos',
        'usos_atuais',
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
        'limite_usos' => 'integer',
        'usos_atuais' => 'integer',
    ];

    /**
     * Relação com o usuário que gerou o link.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relação com a desfiliação resultante direta (legada/individual).
     */
    public function desfiliacao(): BelongsTo
    {
        return $this->belongsTo(Desfiliacao::class);
    }

    /**
     * Relação com todas as desfiliações originadas através deste link (1 para N).
     */
    public function desfiliacoes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Desfiliacao::class, 'desfiliacao_link_id');
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
        return $this->cancelado_em === null 
            && $this->expira_em->isFuture() 
            && $this->usos_atuais < $this->limite_usos;
    }

    /**
     * Verifica se o link esgotou a cota máxima de utilizações permitidas.
     */
    public function isEsgotado(): bool
    {
        return $this->usos_atuais >= $this->limite_usos;
    }

    /**
     * Quantidade de utilizações restantes disponíveis na cota.
     */
    public function getVagasRestantesAttribute(): int
    {
        return max(0, $this->limite_usos - $this->usos_atuais);
    }

    /**
     * Verifica se é um link compartilhado (com cota superior a 1 uso).
     */
    public function isCompartilhado(): bool
    {
        return $this->tipo_link === 'compartilhado' || $this->limite_usos > 1;
    }

    /**
     * Verifica se o link foi emitido sem identificação prévia.
     */
    public function isAnonimo(): bool
    {
        return empty($this->destinatario_nome) && empty($this->destinatario_contato);
    }

    /**
     * Verifica se o link já expirou sem ter atingido a cota máxima.
     */
    public function isExpirado(): bool
    {
        return $this->cancelado_em === null 
            && $this->expira_em->isPast() 
            && $this->usos_atuais < $this->limite_usos;
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

        if ($this->isCompartilhado()) {
            $mensagem = "{$saudacao}\n\nConforme solicitado junto ao Sindicato Químicos Unificados, segue o link seguro para formalização do requerimento de desfiliação sindical (válido para até {$this->limite_usos} utilizações):\n\n{$this->url}\n\n*Atenção:* Este link expira em " . $this->expira_em->format('d/m/Y \à\s H:i') . ".";
        } else {
            $mensagem = "{$saudacao}\n\nConforme sua solicitação junto ao Sindicato Químicos Unificados, segue o seu link exclusivo e seguro para preenchimento do formulário de desfiliação:\n\n{$this->url}\n\n*Atenção:* Este link é de uso único e expira em " . $this->expira_em->format('d/m/Y \à\s H:i') . ".";
        }

        $urlBase = $telefoneLimpo ? "https://wa.me/{$telefoneLimpo}" : "https://wa.me/";

        return $urlBase . '?text=' . rawurlencode($mensagem);
    }

    /**
     * Scope para links válidos.
     */
    public function scopeValidos(Builder $query): Builder
    {
        return $query->whereNull('cancelado_em')
            ->where('expira_em', '>', now())
            ->whereColumn('usos_atuais', '<', 'limite_usos');
    }

    /**
     * Scope para links expirados.
     */
    public function scopeExpirados(Builder $query): Builder
    {
        return $query->whereNull('cancelado_em')
            ->where('expira_em', '<=', now())
            ->whereColumn('usos_atuais', '<', 'limite_usos');
    }
}
