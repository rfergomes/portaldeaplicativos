<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'token_depto_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function perfis()
    {
        return $this->belongsToMany(Perfil::class, 'usuario_perfil');
    }

    public function temPermissao(string $chave): bool
    {
        return $this->perfis()->whereHas('permissoes', function ($query) use ($chave) {
            $query->where('chave', $chave);
        })->exists();
    }

    public function temPerfil(string $nome): bool
    {
        return $this->perfis()->where('nome', $nome)->exists();
    }

    public function tokenDepto()
    {
        return $this->belongsTo(TokenDepto::class);
    }
}

