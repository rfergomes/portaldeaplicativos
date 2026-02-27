<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocoloEnvio extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocolo_id',
        'id_email_externo',
        'status',
        'ultima_resposta',
        'enviado_em',
        'entregue_em',
        'lido_em',
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
}

