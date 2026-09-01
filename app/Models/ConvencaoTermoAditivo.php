<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Shared\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConvencaoTermoAditivo extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'convencao_termos_aditivos';

    protected $fillable = [
        'convencao_coletiva_id',
        'numero_termo',
        'titulo',
        'tipo',
        'data_assinatura',
        'vigencia_inicio',
        'vigencia_fim',
        'arquivo_pdf',
        'arquivo_nome_original',
        'arquivo_tamanho',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'data_assinatura' => 'date',
        'vigencia_inicio' => 'date',
        'vigencia_fim' => 'date',
        'arquivo_tamanho' => 'integer',
        'ativo' => 'boolean',
    ];

    public function convencaoColetiva(): BelongsTo
    {
        return $this->belongsTo(ConvencaoColetiva::class, 'convencao_coletiva_id');
    }

    public function clausulas(): HasMany
    {
        return $this->hasMany(ConvencaoClausula::class, 'convencao_termo_aditivo_id');
    }

    public function getArquivoTamanhoFormatadoAttribute(): ?string
    {
        if (!$this->arquivo_tamanho) {
            return null;
        }

        $bytes = $this->arquivo_tamanho;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
        }

        return number_format($bytes / 1024, 1, ',', '.') . ' KB';
    }

    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }
}
