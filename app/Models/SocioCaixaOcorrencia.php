<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocioCaixaOcorrencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricula',
        'user_id',
        'mensagem',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
