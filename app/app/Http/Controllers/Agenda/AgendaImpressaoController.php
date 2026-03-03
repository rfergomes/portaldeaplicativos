<?php

namespace App\Http\Controllers\Agenda;

use App\Http\Controllers\Controller;
use App\Models\Agenda\AgendaInscricao;
use App\Models\AgendaPeriodo;
use App\Models\Colonia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AgendaImpressaoController extends Controller
{
    /**
     * Gera o PDF das guias de pré-reserva (2 por página).
     */
    public function gerarGuiaPreReserva(Request $request)
    {
        $request->validate([
            'colonia_id' => 'required|exists:colonias,id',
            'periodo_id' => 'required|exists:agenda_periodos,id',
            'quantidade' => 'nullable|integer|min:1|max:100',
        ]);

        $colonia = Colonia::findOrFail($request->colonia_id);
        $periodo = AgendaPeriodo::findOrFail($request->periodo_id);
        $quantidade = $request->quantidade ?? 2; // Default 2 guias (1 folha)

        $pdf = Pdf::loadView('agenda.inscricoes.pdf.guia_pre_reserva', compact('colonia', 'periodo', 'quantidade'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("guia_pre_reserva_{$colonia->nome}_{$periodo->descricao}.pdf");
    }

    /**
     * Gera o PDF com a lista de inscritos numerada sequencialmente.
     */
    public function gerarListaInscritos(Request $request)
    {
        $request->validate([
            'colonia_id' => 'required|exists:colonias,id',
            'periodo_id' => 'required|exists:agenda_periodos,id',
        ]);

        $colonia = Colonia::findOrFail($request->colonia_id);
        $periodo = AgendaPeriodo::findOrFail($request->periodo_id);

        $inscritos = AgendaInscricao::with(['hospede.empresa'])
            ->where('colonia_id', $request->colonia_id)
            ->where('periodo_id', $request->periodo_id)
            ->orderBy('created_at', 'asc')
            ->get();

        $pdf = Pdf::loadView('agenda.inscricoes.pdf.lista_inscritos', compact('colonia', 'periodo', 'inscritos'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("lista_inscritos_{$colonia->nome}_{$periodo->descricao}.pdf");
    }
}
