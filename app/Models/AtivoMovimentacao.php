<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtivoMovimentacao extends Model
{
    use HasFactory;

    protected $table = 'ativo_movimentacoes';

    protected $fillable = [
        'equipamento_id',
        'usuario_id',
        'tipo',
        'data_movimentacao',
        'data_previsao_devolucao',
        'data_devolucao_real',
        'responsavel_id',
        'cessao_id',
        'origem',
        'destino',
        'observacao',
        'valor_orcamento',
        'dados_cedente',
        'data_retirada',
    ];

    protected $casts = [
        'data_movimentacao' => 'datetime',
        'data_previsao_devolucao' => 'date',
        'data_devolucao_real' => 'datetime',
        'valor_orcamento' => 'decimal:2',
        'data_retirada' => 'date',
    ];

    public function equipamento()
    {
        return $this->belongsTo(AtivoEquipamento::class, 'equipamento_id');
    }

    public function usuario()
    {
        return $this->belongsTo(AtivoUsuario::class, 'usuario_id');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function cessao()
    {
        return $this->belongsTo(AtivoCessao::class, 'cessao_id');
    }

    public function anexos()
    {
        return $this->hasMany(AtivoAnexo::class, 'movimentacao_id');
    }
}
