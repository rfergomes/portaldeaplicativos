<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'nome',
        'descricao',
        'corpo_exemplo',
        'parametros_esperados',
        'ativo',
        'status_meta',
        'categoria',
        'language',
        'namespace',
        'rejected_reason',
        'sincronizado_em',
        'user_id',
    ];

    protected $casts = [
        'parametros_esperados' => 'array',
        'ativo' => 'boolean',
        'sincronizado_em' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(WhatsappLote::class, 'whatsapp_template_id');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
