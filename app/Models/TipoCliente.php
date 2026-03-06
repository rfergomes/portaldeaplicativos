<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCliente extends Model
{
    use HasFactory;

    protected $table = 'tipos_clientes';

    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
    ];

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
}

