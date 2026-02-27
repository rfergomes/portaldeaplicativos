<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Convidado extends Model
{
    use HasFactory;

    protected $fillable = [
        'convite_id',
        'nome',
        'documento',
        'presente',
    ];

    protected $casts = [
        'presente' => 'boolean',
    ];

    public function convite()
    {
        return $this->belongsTo(Convite::class);
    }
}

