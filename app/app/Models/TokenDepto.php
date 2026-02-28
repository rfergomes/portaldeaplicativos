<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenDepto extends Model
{
    use HasFactory;

    protected $fillable = [
        'departamento',
        'email',
        'token',
    ];

    protected function casts(): array
    {
        return [
            'token' => 'encrypted',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
