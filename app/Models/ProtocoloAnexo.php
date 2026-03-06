<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocoloAnexo extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocolo_id',
        'nome_original',
        'caminho_armazenado',
        'tamanho_bytes',
        'hash',
    ];

    public function protocolo()
    {
        return $this->belongsTo(Protocolo::class);
    }
}

