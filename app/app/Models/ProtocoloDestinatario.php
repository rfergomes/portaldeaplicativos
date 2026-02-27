<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocoloDestinatario extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocolo_id',
        'nome',
        'email',
        'telefone',
        'metadados',
    ];

    protected $casts = [
        'metadados' => 'array',
    ];

    public function protocolo()
    {
        return $this->belongsTo(Protocolo::class);
    }
}

