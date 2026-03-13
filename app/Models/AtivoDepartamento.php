<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtivoDepartamento extends Model
{
    use HasFactory;

    protected $table = 'ativo_departamentos';

    protected $fillable = [
        'nome',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function usuarios()
    {
        return $this->hasMany(AtivoUsuario::class, 'departamento_id');
    }
}
