<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaReserva extends Model
{
    use HasFactory;

    protected $fillable = [
        'agenda_periodo_id',
        'colonia_id',
        'colonia_acomodacao_id',
        'agenda_hospede_id',
        'bloqueio_nota',
        'status',
        'ordem_fila',
    ];

    public function periodo()
    {
        return $this->belongsTo(AgendaPeriodo::class, 'agenda_periodo_id');
    }

    public function colonia()
    {
        return $this->belongsTo(Colonia::class);
    }

    public function acomodacao()
    {
        return $this->belongsTo(ColoniaAcomodacao::class, 'colonia_acomodacao_id');
    }

    public function hospede()
    {
        return $this->belongsTo(AgendaHospede::class, 'agenda_hospede_id');
    }
}
