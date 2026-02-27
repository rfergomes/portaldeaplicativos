<?php

namespace App\Models;

use App\Domain\Shared\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Protocolo extends Model
{
    use HasFactory;
    use Auditable;

    protected $fillable = [
        'empresa_id',
        'assunto',
        'corpo',
        'canal',
        'status',
        'agendado_para',
    ];

    protected $casts = [
        'agendado_para' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function destinatarios()
    {
        return $this->hasMany(ProtocoloDestinatario::class);
    }

    public function anexos()
    {
        return $this->hasMany(ProtocoloAnexo::class);
    }

    public function envios()
    {
        return $this->hasMany(ProtocoloEnvio::class);
    }

    public function comprovante()
    {
        return $this->hasOne(ProtocoloComprovante::class);
    }

    public function carimbos()
    {
        return $this->hasMany(CarimboTempo::class);
    }
}

