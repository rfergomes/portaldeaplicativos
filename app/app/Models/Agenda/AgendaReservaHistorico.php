<?php

namespace App\Models\Agenda;

use Illuminate\Database\Eloquent\Model;

class AgendaReservaHistorico extends Model
{
    protected $table = 'agenda_reserva_historicos';

    protected $fillable = [
        'colonia_id',
        'colonia_nome',
        'periodo_id',
        'periodo_descricao',
        'periodo_data_inicial',
        'periodo_data_final',
        'hospede_nome',
        'hospede_telefone',
        'hospede_email',
        'acomodacao_identificador',
        'acomodacao_tipo',
        'status_reserva',
        'bloqueio_nota',
        'motivo',
        'excluido_por',
        'excluido_por_nome',
    ];

    protected $casts = [
        'periodo_data_inicial' => 'date',
        'periodo_data_final' => 'date',
    ];
}
