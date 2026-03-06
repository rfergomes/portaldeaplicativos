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

    /**
     * Gera o PDF da Lista de Acomodações (Ganhadores) do Painel de Reservas.
     */
    public function gerarListaReservas(Request $request)
    {
        $request->validate([
            'colonia_id' => 'required|exists:colonias,id',
            'periodo_id' => 'required|exists:agenda_periodos,id',
        ]);

        $colonia = Colonia::with([
            'acomodacoes' => function ($q) {
                $q->where('ativo', true)->orderBy('tipo')->orderBy('identificador');
            }
        ])->findOrFail($request->colonia_id);

        $periodo = AgendaPeriodo::findOrFail($request->periodo_id);

        $reservas = \App\Models\AgendaReserva::with(['hospede.empresa'])
            ->where('colonia_id', $request->colonia_id)
            ->where('agenda_periodo_id', $request->periodo_id)
            ->whereNotNull('colonia_acomodacao_id')
            ->get()
            ->keyBy('colonia_acomodacao_id');

        $pdf = Pdf::loadView('agenda.reservas.pdf.lista_acomodacoes', compact('colonia', 'periodo', 'reservas'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("lista_reservas_{$colonia->nome}_{$periodo->descricao}.pdf");
    }

    /**
     * Gera o PDF da Lista de Espera (Suplentes) do Painel de Reservas.
     */
    public function gerarListaEspera(Request $request)
    {
        $request->validate([
            'colonia_id' => 'required|exists:colonias,id',
            'periodo_id' => 'required|exists:agenda_periodos,id',
        ]);

        $colonia = Colonia::findOrFail($request->colonia_id);
        $periodo = AgendaPeriodo::findOrFail($request->periodo_id);

        $filaEspera = \App\Models\AgendaReserva::with(['hospede.empresa'])
            ->where('colonia_id', $request->colonia_id)
            ->where('agenda_periodo_id', $request->periodo_id)
            ->whereNull('colonia_acomodacao_id')
            ->orderBy('ordem_fila')
            ->get();

        $pdf = Pdf::loadView('agenda.reservas.pdf.lista_espera', compact('colonia', 'periodo', 'filaEspera'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("lista_espera_{$colonia->nome}_{$periodo->descricao}.pdf");
    }
}
