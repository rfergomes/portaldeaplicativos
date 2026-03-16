<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtivoEquipamento extends Model
{
    use HasFactory;

    protected $table = 'ativo_equipamentos';

    protected $fillable = [
        'descricao',
        'modelo',
        'numero_serie',
        'fabricante_id',
        'fornecedor_id',
        'data_compra',
        'valor_item',
        'valor_nota',
        'garantia_meses',
        'status',
        'tipo_uso',
        'localizacao_atual',
        'data_devolucao_prevista',
        'observacao',
    ];

    protected $casts = [
        'data_compra' => 'date',
        'data_devolucao_prevista' => 'date',
        'valor_item' => 'decimal:2',
    ];

    public function fabricante()
    {
        return $this->belongsTo(AtivoFabricante::class, 'fabricante_id');
    }

    public function fornecedor()
    {
        return $this->belongsTo(AtivoFornecedor::class, 'fornecedor_id');
    }

    public function movimentacoes()
    {
        return $this->hasMany(AtivoMovimentacao::class, 'equipamento_id');
    }

    public function anexos()
    {
        return $this->hasMany(AtivoAnexo::class, 'equipamento_id');
    }

    public function ultimaMovimentacao()
    {
        return $this->hasOne(AtivoMovimentacao::class, 'equipamento_id')->latestOfMany();
    }
}
