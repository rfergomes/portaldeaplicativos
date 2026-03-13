<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtivoFornecedor extends Model
{
    use HasFactory;

    protected $table = 'ativo_fornecedores';

    protected $fillable = [
        'nome',
        'cnpj',
        'email',
        'telefone',
        'contato',
        'endereco',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function equipamentos()
    {
        return $this->hasMany(AtivoEquipamento::class, 'fornecedor_id');
    }
}
