<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoProtocolo extends Model
{
    use HasFactory;

    protected $table = 'tipo_protocolos';

    protected $fillable = [
        'nome',
        'icone',
        'cor',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function protocolos()
    {
        return $this->hasMany(Protocolo::class);
    }

    /**
     * Retorna a badge HTML com ícone e cor do tipo.
     */
    public function badgeHtml(): string
    {
        return sprintf(
            '<span class="badge text-bg-%s rounded-pill shadow-sm px-2"><i class="%s me-1"></i>%s</span>',
            e($this->cor),
            e($this->icone),
            e($this->nome)
        );
    }
}
