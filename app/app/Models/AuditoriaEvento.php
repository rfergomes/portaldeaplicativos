<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditoriaEvento extends Model
{
    use HasFactory;

    protected $table = 'auditoria_eventos';

    protected $fillable = [
        'entidade',
        'operacao',
        'entidade_id',
        'user_id',
        'dados_antes',
        'dados_depois',
        'ocorreu_em',
    ];

    protected $casts = [
        'dados_antes' => 'array',
        'dados_depois' => 'array',
        'ocorreu_em' => 'datetime',
    ];
}

