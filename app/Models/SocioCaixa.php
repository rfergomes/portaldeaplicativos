<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocioCaixa extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricula',
        'ano',
        'mes',
        'nome',
        'tipo_socio',
        'cod_emp',
        'cpf',
        'telefone',
        'valor',
        'pago',
        'data_pagamento',
        'user_id',
        'observacao',
        'postergado_ate',
        'motivo_postergacao',
    ];

    protected $casts = [
        'pago' => 'boolean',
        'data_pagamento' => 'datetime',
        'postergado_ate' => 'datetime',
        'valor' => 'decimal:2',
    ];

    public function historico()
    {
        return $this->hasMany(SocioCaixaHistorico::class, 'socio_caixa_id')->orderBy('created_at', 'desc');
    }

    public function usuarioBaixa()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
