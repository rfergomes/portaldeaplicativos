<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocioFolhaEmailHistorico extends Model
{
    protected $table = 'socio_folha_email_historicos';

    protected $fillable = [
        'socio_folha_id',
        'cliente_id',
        'email_destinatario',
        'assunto',
        'tipo_envio',
        'status',
        'bounce_code',
        'bounce_description',
        'opened_at'
    ];

    public function socioFolha()
    {
        return $this->belongsTo(SocioFolha::class, 'socio_folha_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
