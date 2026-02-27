<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendaConvite extends Model
{
    use HasFactory;

    protected $table = 'vendas_convite';

    protected $fillable = [
        'convite_id',
        'cliente_id',
        'valor_venda',
        'status_pagamento',
        'data_venda',
    ];

    protected $casts = [
        'data_venda' => 'datetime',
    ];

    public function convite()
    {
        return $this->belongsTo(Convite::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}

