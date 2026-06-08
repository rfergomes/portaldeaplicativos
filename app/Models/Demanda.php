<?php

namespace App\Models;

use App\Domain\Shared\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Demanda extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'demandas';

    protected $fillable = [
        'titulo',
        'descricao',
        'prazo',
        'prioridade',
        'criador_id',
        'tipo_responsavel',
        'responsavel_usuario_id',
        'lida_pelo_responsavel',
        'responsavel_nome',
        'responsavel_telefone',
        'responsavel_email',
        'status',
        'motivo_devolutiva',
        'observacoes',
        'devolutiva_em',
    ];

    protected $casts = [
        'prazo' => 'datetime',
        'devolutiva_em' => 'datetime',
        'lida_pelo_responsavel' => 'boolean',
    ];

    // Constantes de Ciclo de Vida
    const STATUS_ABERTA = 'aberta';
    const STATUS_AGUARDANDO = 'aguardando';
    const STATUS_EXECUTADA = 'executada';
    const STATUS_NAO_EXECUTADA = 'nao_executada';
    const STATUS_CANCELADA = 'cancelada';

    // Constantes de Prioridade
    const PRIORIDADE_BAIXA = 'baixa';
    const PRIORIDADE_MEDIA = 'media';
    const PRIORIDADE_ALTA = 'alta';
    const PRIORIDADE_URGENTE = 'urgente';

    // Relacionamentos
    public function criador()
    {
        return $this->belongsTo(User::class, 'criador_id');
    }

    public function responsavelUsuario()
    {
        return $this->belongsTo(User::class, 'responsavel_usuario_id');
    }

    public function checklists()
    {
        return $this->hasMany(DemandaChecklist::class, 'demanda_id');
    }

    public function anexos()
    {
        return $this->hasMany(DemandaAnexo::class, 'demanda_id');
    }

    public function historicos()
    {
        return $this->hasMany(DemandaHistorico::class, 'demanda_id')->orderBy('created_at', 'desc');
    }

    // Helpers
    public function isVencida(): bool
    {
        return $this->prazo && $this->prazo->isPast() && in_array($this->status, [self::STATUS_ABERTA, self::STATUS_AGUARDANDO]);
    }

    public function getProgressoChecklistAttribute(): int
    {
        $total = $this->checklists()->count();
        if ($total === 0) {
            return 0;
        }
        $concluidos = $this->checklists()->where('concluido', true)->count();
        return (int) round(($concluidos / $total) * 100);
    }

    // Scopes
    public function scopeAbertas($query)
    {
        return $query->where('status', self::STATUS_ABERTA)
            ->where(function ($q) {
                $q->whereNull('prazo')->orWhere('prazo', '>=', now());
            });
    }

    public function scopeAguardando($query)
    {
        return $query->where('status', self::STATUS_AGUARDANDO);
    }

    public function scopeVencidas($query)
    {
        return $query->whereIn('status', [self::STATUS_ABERTA, self::STATUS_AGUARDANDO])
            ->whereNotNull('prazo')
            ->where('prazo', '<', now());
    }

    public function scopeExecutadas($query)
    {
        return $query->where('status', self::STATUS_EXECUTADA);
    }

    public function scopeNaoExecutadas($query)
    {
        return $query->where('status', self::STATUS_NAO_EXECUTADA);
    }
}
