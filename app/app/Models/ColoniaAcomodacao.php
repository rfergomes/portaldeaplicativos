<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColoniaAcomodacao extends Model
{
    use HasFactory;

    protected $fillable = [
        'colonia_id',
        'tipo',
        'identificador',
        'ativo',
    ];

    public function colonia()
    {
        return $this->belongsTo(Colonia::class);
    }
}
