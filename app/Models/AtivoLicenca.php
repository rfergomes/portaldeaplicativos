<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtivoLicenca extends Model
{
    use HasFactory;

    protected $table = 'ativo_licencas';

    protected $fillable = [
        'nome',
        'chave',
        'tipo_licenca',
        'data_validade',
        'fabricante_id',
        'quantidade_seats',
        'observacao',
    ];

    protected $casts = [
        'data_validade' => 'date',
    ];

    public function fabricante()
    {
        return $this->belongsTo(AtivoFabricante::class, 'fabricante_id');
    }

    public function equipamentos()
    {
        return $this->belongsToMany(AtivoEquipamento::class, 'ativo_licenca_equipamento', 'licenca_id', 'equipamento_id')
                    ->withPivot('atribuido_em')
                    ->withTimestamps();
    }
}
