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
        'aquisicao_id',
        'marketplace_id',
        'data_compra',
        'valor_item',
        'valor_nota',
        'garantia_meses',
        'status',
        'tipo_uso',
        'localizacao_atual',
        'data_devolucao_prevista',
        'estacao_id',
        'observacao',
        'acessorios',
        'is_depreciavel',
        'valor_residual',
        'vida_util_meses',
        'metodo_depreciacao',
        'categoria_depreciacao',
    ];

    protected $casts = [
        'data_compra' => 'date',
        'data_devolucao_prevista' => 'date',
        'valor_item' => 'decimal:2',
        'valor_residual' => 'decimal:2',
        'is_depreciavel' => 'boolean',
        'vida_util_meses' => 'integer',
    ];

    public function estacao()
    {
        return $this->belongsTo(AtivoEstacao::class, 'estacao_id');
    }

    public function licencas()
    {
        return $this->belongsToMany(AtivoLicenca::class, 'ativo_licenca_equipamento', 'equipamento_id', 'licenca_id')
                    ->withPivot('atribuido_em')
                    ->withTimestamps();
    }

    public function fabricante()
    {
        return $this->belongsTo(AtivoFabricante::class, 'fabricante_id');
    }

    public function fornecedor()
    {
        return $this->belongsTo(AtivoFornecedor::class, 'fornecedor_id');
    }

    public function aquisicao()
    {
        return $this->belongsTo(AtivoAquisicao::class, 'aquisicao_id');
    }

    public function marketplace()
    {
        return $this->belongsTo(AtivoMarketplace::class, 'marketplace_id');
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

    /*
    |--------------------------------------------------------------------------
    | Depreciation Accessors (Calculated on the fly)
    |--------------------------------------------------------------------------
    */

    /**
     * Get months since purchase.
     */
    public function getMesesUsoAttribute()
    {
        if (!$this->data_compra) return 0;
        
        return $this->data_compra->diffInMonths(now());
    }

    /**
     * Get monthly depreciation value.
     */
    public function getDepreciacaoMensalAttribute()
    {
        if (!$this->is_depreciavel || !$this->valor_item || !$this->vida_util_meses) return 0;

        return ($this->valor_item - ($this->valor_residual ?? 0)) / $this->vida_util_meses;
    }

    /**
     * Get total accumulated depreciation.
     */
    public function getDepreciacaoAcumuladaAttribute()
    {
        if (!$this->is_depreciavel || !$this->vida_util_meses) return 0;

        $meses = min($this->meses_uso, $this->vida_util_meses);

        return $meses * $this->depreciacao_mensal;
    }

    /**
     * Get current book value.
     */
    public function getValorAtualAttribute()
    {
        if (!$this->is_depreciavel) return $this->valor_item;

        return max(
            $this->valor_item - $this->depreciacao_acumulada,
            $this->valor_residual ?? 0
        );
    }

    /**
     * Check if asset is fully depreciated.
     */
    public function getTotalmenteDepreciadoAttribute()
    {
        if (!$this->is_depreciavel || !$this->vida_util_meses) return false;
        
        return $this->meses_uso >= $this->vida_util_meses;
    }
}
