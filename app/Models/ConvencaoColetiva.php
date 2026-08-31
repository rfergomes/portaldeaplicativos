<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Shared\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConvencaoColetiva extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'convencoes_coletivas';

    protected $fillable = [
        'titulo',
        'categoria',
        'vigencia_inicio',
        'vigencia_fim',
        'data_base',
        'abrangencia',
        'ativo',
    ];

    protected $casts = [
        'vigencia_inicio' => 'date',
        'vigencia_fim' => 'date',
        'ativo' => 'boolean',
    ];

    public function clausulas(): HasMany
    {
        return $this->hasMany(ConvencaoClausula::class, 'convencao_coletiva_id')->orderBy('ordem')->orderBy('numero');
    }

    public function clausulaLembrete(): ?ConvencaoClausula
    {
        return $this->clausulas()->where('dispara_lembrete_lista_nominal', true)->first();
    }

    public function scopeAtiva($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorCategoria($query, string $categoria)
    {
        return $query->where('categoria', strtoupper(trim($categoria)));
    }
}
