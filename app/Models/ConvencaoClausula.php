<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Shared\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConvencaoClausula extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'convencao_clausulas';

    protected $fillable = [
        'convencao_coletiva_id',
        'numero',
        'titulo',
        'categoria_clausula',
        'texto',
        'vigencia_inicio',
        'vigencia_fim',
        'dispara_lembrete_lista_nominal',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'vigencia_inicio' => 'date',
        'vigencia_fim' => 'date',
        'dispara_lembrete_lista_nominal' => 'boolean',
        'ordem' => 'integer',
        'ativo' => 'boolean',
    ];

    public function convencao(): BelongsTo
    {
        return $this->belongsTo(ConvencaoColetiva::class, 'convencao_coletiva_id');
    }

    public function scopeAtiva($query)
    {
        return $query->where('ativo', true);
    }
}
