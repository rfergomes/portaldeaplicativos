<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocioFolha extends Model
{
    protected $table = 'socios_folha';

    protected $fillable = [
        'lancamento_id',
        'empresa_id',
        'regiao_id',
        'ano',
        'mes',
        'data_vencimento',
        'valor_mensalidade',
        'situacao',
        'data_autenticacao',
        'multa',
        'total',
        'vl_credit',
        'origem',
        'data_lista',
        'data_baixa',
        'valor_pago'
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'data_autenticacao' => 'date',
        'data_lista' => 'datetime',
        'data_baixa' => 'datetime',
        'valor_mensalidade' => 'decimal:2',
        'valor_pago' => 'decimal:2',
        'multa' => 'decimal:2',
        'total' => 'decimal:2',
        'vl_credit' => 'decimal:2',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function regiao()
    {
        return $this->belongsTo(Regiao::class);
    }

    public function historico()
    {
        return $this->hasMany(SocioFolhaHistorico::class, 'socio_folha_id');
    }

    public function emailHistoricos()
    {
        return $this->hasMany(SocioFolhaEmailHistorico::class, 'socio_folha_id');
    }
}
