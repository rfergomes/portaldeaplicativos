<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappLoteItem extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_lote_itens';

    protected $fillable = [
        'whatsapp_lote_id',
        'matricula',
        'socio_folha_id',
        'empresa_id',
        'destinatario_nome',
        'telefone',
        'status',
        'notification_id',
        'motivo_falha',
        'enviado_em',
    ];

    protected $casts = [
        'enviado_em' => 'datetime',
    ];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(WhatsappLote::class, 'whatsapp_lote_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
