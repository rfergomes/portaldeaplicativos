<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtivoFabricante extends Model
{
    use HasFactory;

    protected $table = 'ativo_fabricantes';

    protected $fillable = [
        'nome',
        'site',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function equipamentos()
    {
        return $this->hasMany(AtivoEquipamento::class, 'fabricante_id');
    }
}
