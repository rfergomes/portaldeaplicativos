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
        'data_vencimento',
        'pago',
        'data_pagamento',
        'user_id',
        'observacao',
        'postergado_ate',
        'motivo_postergacao',
        'inativado_abaco',
        'inativado_abaco_em',
    ];

    protected $casts = [
        'pago' => 'boolean',
        'data_vencimento' => 'date',
        'data_pagamento' => 'datetime',
        'postergado_ate' => 'datetime',
        'inativado_abaco' => 'boolean',
        'inativado_abaco_em' => 'datetime',
        'valor' => 'decimal:2',
    ];

    public function isVencido(): bool
    {
        return !$this->pago && $this->data_vencimento && $this->data_vencimento->isPast();
    }

    public function diasAtraso(): int
    {
        if (!$this->isVencido()) {
            return 0;
        }
        return (int) $this->data_vencimento->diffInDays(now()->startOfDay());
    }

    public function scopeAtivos($query)
    {
        return $query->where('inativado_abaco', false);
    }

    public function scopeInativadosAbaco($query)
    {
        return $query->where('inativado_abaco', true);
    }

    public function historico()
    {
        return $this->hasMany(SocioCaixaHistorico::class, 'socio_caixa_id')->orderBy('created_at', 'desc');
    }

    public function usuarioBaixa()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
