<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtivoLicenca extends Model
{
    use HasFactory;

    protected $table = 'ativo_licencas';

    protected $fillable = [
        'aquisicao_id',
        'nome',
        'chave',
        'tipo_licenca',
        'data_validade',
        'fabricante_id',
        'fornecedor_id',
        'marketplace_id',
        'numero_nf',
        'chave_acesso',
        'data_aquisicao',
        'valor_total',
        'valor_frete',
        'quantidade_seats',
        'observacao',
    ];

    protected $casts = [
        'data_validade' => 'date',
        'data_aquisicao' => 'date',
        'valor_total' => 'decimal:2',
        'valor_frete' => 'decimal:2',
    ];

    public function fabricante()
    {
        return $this->belongsTo(AtivoFabricante::class, 'fabricante_id');
    }

    public function fornecedor()
    {
        return $this->belongsTo(AtivoFornecedor::class, 'fornecedor_id');
    }

    public function marketplace()
    {
        return $this->belongsTo(AtivoMarketplace::class, 'marketplace_id');
    }

    public function aquisicao()
    {
        return $this->belongsTo(AtivoAquisicao::class, 'aquisicao_id');
    }

    public function equipamentos()
    {
        return $this->belongsToMany(AtivoEquipamento::class, 'ativo_licenca_equipamento', 'licenca_id', 'equipamento_id')
                    ->withPivot('atribuido_em')
                    ->withTimestamps();
    }
}
