<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaPeriodo extends Model
{
    use HasFactory;

    protected $fillable = [
        'descricao',
        'data_inicial',
        'data_final',
        'data_limite',
        'data_sorteio',
        'data_limite_pagamento',
        'ativo',
    ];

    protected $casts = [
        'data_inicial' => 'date',
        'data_final' => 'date',
        'data_limite' => 'date',
        'data_sorteio' => 'date',
        'data_limite_pagamento' => 'date',
        'ativo' => 'boolean',
    ];

    public function reservas()
    {
        return $this->hasMany(AgendaReserva::class);
    }
}
