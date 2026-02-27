<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sessao extends Model
{
    use HasFactory;

    protected $table = 'sessoes';

    protected $fillable = [
        'user_id',
        'ip',
        'user_agent',
        'token',
        'ultima_atividade',
        'revogada',
    ];

    protected $casts = [
        'ultima_atividade' => 'datetime',
        'revogada' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

