<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtivoUsuario extends Model
{
    use HasFactory;

    protected $table = 'ativo_usuarios';

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'empresa_id',
        'departamento_id',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function departamento()
    {
        return $this->belongsTo(AtivoDepartamento::class, 'departamento_id');
    }

    public function movimentacoes()
    {
        return $this->hasMany(AtivoMovimentacao::class, 'usuario_id');
    }
}
