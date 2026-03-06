<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocoloEnvio extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocolo_id',
        'destinatario_id',
        'canal',
        'id_email_externo',
        'status',
        'ultima_resposta',
        'enviado_em',
        'entregue_em',
        'lido_em',
        'token_usado',
    ];

    protected $casts = [
        'enviado_em' => 'datetime',
        'entregue_em' => 'datetime',
        'lido_em' => 'datetime',
    ];

    public function protocolo()
    {
        return $this->belongsTo(Protocolo::class);
    }

    public function destinatario()
    {
        return $this->belongsTo(ProtocoloDestinatario::class, 'destinatario_id');
    }

    /**
     * Rótulo legível do status.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'queued' => 'Na Fila',
            'enviado' => 'Enviado',
            'entregue' => 'Entregue',
            'lido' => 'Lido',
            'falha' => 'Falha',
            'concluido' => 'Concluído',
            default => ucfirst($this->status),
        };
    }

    /**
     * Cor Bootstrap do status.
     */
    public function statusCor(): string
    {
        return match ($this->status) {
            'queued' => 'secondary',
            'enviado' => 'primary',
            'entregue' => 'info',
            'lido' => 'success',
            'falha' => 'danger',
            'concluido' => 'success',
            default => 'secondary',
        };
    }
}
