<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtivoMarketplace extends Model
{
    use HasFactory;

    protected $table = 'ativo_marketplaces';

    protected $fillable = [
        'nome',
        'site',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];
}
