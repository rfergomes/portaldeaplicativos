<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Colonia extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
        'capacidade_total',
        'ativo',
    ];

    public function acomodacoes()
    {
        return $this->hasMany(ColoniaAcomodacao::class);
    }
}
