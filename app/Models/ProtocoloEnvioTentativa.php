<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocoloEnvioTentativa extends Model
{
    use HasFactory;

    protected $table = 'protocolo_envio_tentativas';

    protected $fillable = [
        'protocolo_envio_id',
        'numero_tentativa',
        'status_resultado',
        'resposta_api',
        'executado_por_user_id',
    ];

    public function envio()
    {
        return $this->belongsTo(ProtocoloEnvio::class, 'protocolo_envio_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'executado_por_user_id');
    }
}
