<?php

namespace App\Models\Agenda;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AgendaHospede;
use App\Models\AgendaPeriodo;
use App\Models\Colonia;
use App\Models\ColoniaAcomodacao;
use App\Models\AgendaReserva;

class AgendaInscricao extends Model
{
    use HasFactory;

    protected $table = 'agenda_inscricoes';

    protected $fillable = [
        'colonia_id',
        'agenda_periodo_id',
        'agenda_hospede_id',
        'status',
        'ordem_espera',
        'acomodacao_id',
        'observacao',
        'reserva_id',
    ];

    public function colonia()
    {
        return $this->belongsTo(Colonia::class);
    }

    public function periodo()
    {
        return $this->belongsTo(AgendaPeriodo::class, 'agenda_periodo_id');
    }

    public function hospede()
    {
        return $this->belongsTo(AgendaHospede::class, 'agenda_hospede_id');
    }

    public function acomodacao()
    {
        return $this->belongsTo(ColoniaAcomodacao::class, 'acomodacao_id');
    }

    public function reserva()
    {
        return $this->belongsTo(AgendaReserva::class, 'reserva_id');
    }

    /**
     * Labels amigáveis para o status
     */
    public static function statusLabel(string $status): string
    {
        switch ($status) {
            case 'pendente':
                return 'Pendente';
            case 'sorteado':
                return 'Sorteado';
            case 'espera':
                return 'Lista de Espera';
            case 'cancelado':
                return 'Cancelado';
            default:
                return ucfirst($status);
        }
    }
}
