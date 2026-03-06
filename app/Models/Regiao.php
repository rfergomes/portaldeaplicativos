<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regiao extends Model
{
    use HasFactory;

    protected $table = 'regioes';

    protected $fillable = [
        'nome',
        'area_adm',
        'ativo',
    ];

    public function empresas()
    {
        return $this->hasMany(Empresa::class);
    }
}
