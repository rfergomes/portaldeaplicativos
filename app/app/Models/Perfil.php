<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_perfil');
    }

    public function permissoes()
    {
        return $this->belongsToMany(Permissao::class, 'perfil_permissao');
    }
}

