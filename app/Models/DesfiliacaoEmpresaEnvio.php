<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesfiliacaoEmpresaEnvio extends Model
{
    use HasFactory;

    protected $table = 'desfiliacao_empresa_envios';

    protected $fillable = [
        'desfiliacao_id',
        'cliente_id',
        'email',
        'nome_contato',
        'aronline_id',
        'status',
        'resposta_webhook',
        'notificada_em',
        'entregue_em',
        'lido_em',
    ];

    protected $casts = [
        'notificada_em' => 'datetime',
        'entregue_em' => 'datetime',
        'lido_em' => 'datetime',
    ];

    public function desfiliacao()
    {
        return $this->belongsTo(Desfiliacao::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
