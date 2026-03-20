<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtivoEstacao extends Model
{
    use HasFactory;

    protected $table = 'ativo_estacoes';

    protected $fillable = [
        'departamento_id',
        'nome',
        'descricao',
    ];

    public function departamento()
    {
        return $this->belongsTo(AtivoDepartamento::class, 'departamento_id');
    }

    public function equipamentos()
    {
        return $this->hasMany(AtivoEquipamento::class, 'estacao_id');
    }
}
