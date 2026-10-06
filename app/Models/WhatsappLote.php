<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappLote extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_lotes';

    protected $fillable = [
        'modulo',
        'whatsapp_template_id',
        'user_id',
        'filtros_aplicados',
        'parametros_extras',
        'total_destinatarios',
        'total_enviados',
        'total_falhas',
        'status',
        'mensagem_erro',
        'iniciado_em',
        'concluido_em',
    ];

    protected $casts = [
        'filtros_aplicados' => 'array',
        'parametros_extras' => 'array',
        'total_destinatarios' => 'integer',
        'total_enviados' => 'integer',
        'total_falhas' => 'integer',
        'iniciado_em' => 'datetime',
        'concluido_em' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsappTemplate::class, 'whatsapp_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(WhatsappLoteItem::class, 'whatsapp_lote_id');
    }

    public function percentualConcluido(): float
    {
        if ($this->total_destinatarios === 0) {
            return 100.0;
        }

        $processados = $this->total_enviados + $this->total_falhas;
        return round(($processados / $this->total_destinatarios) * 100, 1);
    }
}
