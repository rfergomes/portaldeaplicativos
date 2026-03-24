<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtivoAquisicao extends Model
{
    use HasFactory;

    protected $table = 'ativo_aquisicoes';

    protected $fillable = [
        'numero_nf',
        'chave_acesso',
        'data_aquisicao',
        'fornecedor_id',
        'marketplace_id',
        'valor_frete',
        'valor_total',
        'observacao',
    ];

    protected $casts = [
        'data_aquisicao' => 'date',
        'valor_frete' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(AtivoFornecedor::class, 'fornecedor_id');
    }

    public function marketplace()
    {
        return $this->belongsTo(AtivoMarketplace::class, 'marketplace_id');
    }

    public function equipamentos()
    {
        return $this->hasMany(AtivoEquipamento::class, 'aquisicao_id');
    }

    public function anexos()
    {
        return $this->hasMany(AtivoAnexo::class, 'aquisicao_id');
    }

    public function licencas()
    {
        return $this->hasMany(AtivoLicenca::class, 'aquisicao_id');
    }
}
