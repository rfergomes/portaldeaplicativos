<?php

namespace App\Models;

use App\Domain\Shared\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;
    use Auditable;

    protected $fillable = [
        'nome',
        'data_inicio',
        'data_fim',
        'local',
        'valor_inteira',
        'valor_meia',
        'encerrado',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'encerrado' => 'boolean',
    ];

    public function lotes()
    {
        return $this->hasMany(LoteConvite::class);
    }

    public function convites()
    {
        return $this->hasMany(Convite::class);
    }

    public function vendas()
    {
        return $this->hasManyThrough(VendaConvite::class, Convite::class);
    }

    public function convidados()
    {
        return $this->hasManyThrough(Convidado::class, Convite::class);
    }
}

