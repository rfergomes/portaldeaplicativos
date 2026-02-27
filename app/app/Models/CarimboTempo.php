<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarimboTempo extends Model
{
    use HasFactory;

    protected $table = 'carimbos_tempo';

    protected $fillable = [
        'protocolo_id',
        'provedor',
        'token',
        'payload_completo',
        'carimbado_em',
    ];

    protected $casts = [
        'payload_completo' => 'array',
        'carimbado_em' => 'datetime',
    ];

    public function protocolo()
    {
        return $this->belongsTo(Protocolo::class);
    }
}

