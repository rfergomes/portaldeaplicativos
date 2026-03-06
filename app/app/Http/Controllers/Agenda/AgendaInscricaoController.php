<?php

namespace App\Http\Controllers\Agenda;

use App\Http\Controllers\Controller;
use App\Models\Agenda\AgendaInscricao;
use App\Models\AgendaHospede;
use App\Models\AgendaPeriodo;
use App\Models\AgendaReserva;
use App\Models\Colonia;
use App\Models\Empresa;
use Illuminate\Http\Request;

class AgendaInscricaoController extends Controller
{
    /**
     * Exibe o Painel de Sorteio / Inscrições com filtros.
     */
    public function index(Request $request)
    {
        $colonias = Colonia::where('ativo', true)->orderBy('nome')->get();
        $periodos = AgendaPeriodo::where('ativo', true)->orderBy('data_inicial', 'desc')->get();
        $empresas = Empresa::orderBy('razao_social')->get();

        $coloniaSelecionada = $request->get('colonia_id');
        $periodoSelecionado = $request->get('periodo_id');

        $inscricoes = collect();
        $acomodacoesLivres = collect();
        $colonia = null;
        $periodo = null;

        if ($coloniaSelecionada && $periodoSelecionado) {
            $colonia = Colonia::with([
                'acomodacoes' => function ($q) {
                    $q->where('ativo', true)->orderBy('tipo')->orderBy('identificador');
                }
            ])->findOrFail($coloniaSelecionada);

            $periodo = AgendaPeriodo::findOrFail($periodoSelecionado);

            $inscricoes = AgendaInscricao::with(['hospede.empresa', 'acomodacao', 'reserva'])
                ->where('colonia_id', $coloniaSelecionada)
                ->where('agenda_periodo_id', $periodoSelecionado)
                ->orderBy('status')
                ->orderBy('ordem_espera')
                ->orderBy('created_at')
                ->get();

            // Carregar acomodações que ainda não têm reserva para o dropdown do sorteio
            $reservasExistentes = AgendaReserva::where('agenda_periodo_id', $periodoSelecionado)
                ->where('colonia_id', $coloniaSelecionada)
                ->whereNotNull('colonia_acomodacao_id')
                ->pluck('colonia_acomodacao_id');

            $acomodacoesLivres = $colonia->acomodacoes->whereNotIn('id', $reservasExistentes)->values();
        }

        return view('agenda.inscricoes.index', compact(
            'colonias',
            'periodos',
            'empresas',
            'coloniaSelecionada',
            'periodoSelecionado',
            'inscricoes',
            'acomodacoesLivres',
            'colonia',
            'periodo'
        ));
    }

    /**
     * Registra uma nova inscrição.
     */
    public function store(Request $request)
    {
        $request->validate([
            'colonia_id' => 'required|exists:colonias,id',
            'periodo_id' => 'required|exists:agenda_periodos,id',
            'nome_hospede' => 'required|string|max:255',
            'telefone_hospede' => 'nullable|string|max:50',
            'email_hospede' => 'nullable|email|max:255',
            'empresa_id' => 'nullable|exists:empresas,id',
            'observacao' => 'nullable|string',
        ]);

        // Criar ou localizar hóspede
        $hospede = AgendaHospede::firstOrCreate(
            ['nome' => $request->nome_hospede, 'telefone' => $request->telefone_hospede],
            [
                'email' => $request->email_hospede,
                'empresa_id' => $request->empresa_id,
            ]
        );

        // Se o hóspede já existia, atualizar dados de contato se vieram no form
        if (!$hospede->wasRecentlyCreated) {
            $hospede->update(array_filter([
                'email' => $request->email_hospede,
                'empresa_id' => $request->empresa_id,
            ]));
        }

        AgendaInscricao::create([
            'colonia_id' => $request->colonia_id,
            'agenda_periodo_id' => $request->periodo_id,
            'agenda_hospede_id' => $hospede->id,
            'status' => 'pendente',
            'observacao' => $request->observacao,
        ]);

        return redirect()->route('agenda.inscricoes.index', [
            'colonia_id' => $request->colonia_id,
            'periodo_id' => $request->periodo_id,
        ])->with('success', "Inscrição de {$hospede->nome} registrada!");
    }

    /**
     * Atualiza o resultado do sorteio de uma inscrição.
     * Se "sorteado" → cria uma AgendaReserva automaticamente.
     * Se "espera"   → coloca na fila de espera (sem acomodação).
     */
    public function update(Request $request, AgendaInscricao $inscricao)
    {
        $request->validate([
            'status' => 'required|in:pendente,sorteado,espera,cancelado',
            'acomodacao_id' => 'nullable|exists:colonia_acomodacaos,id',
            'observacao' => 'nullable|string',
        ]);

        $status = $request->status;

        // Garantir resolução dos campos-chave: usar o modelo ou a query string como fallback
        $periodoId = $inscricao->agenda_periodo_id ?? (int) $request->query('periodo_id');
        $coloniaId = $inscricao->colonia_id ?? (int) $request->query('colonia_id');
        $hospedeId = $inscricao->agenda_hospede_id;

        if (!$periodoId || !$coloniaId) {
            return redirect()->back()->with('error', 'Período ou Colônia não identificados. Verifique os dados da inscrição.');
        }

        // Remover reserva anterior gerada por esta inscrição (se houver mudança)
        if ($inscricao->reserva_id) {
            AgendaReserva::find($inscricao->reserva_id)?->delete();
            $inscricao->reserva_id = null;
        }

        $reservaId = null;

        if ($status === 'sorteado' && $request->acomodacao_id) {
            // Transpor: cria pré-reserva automática
            $reserva = AgendaReserva::create([
                'agenda_periodo_id' => $periodoId,
                'colonia_id' => $coloniaId,
                'agenda_hospede_id' => $hospedeId,
                'colonia_acomodacao_id' => $request->acomodacao_id,
                'status' => 'reservado',
            ]);
            $reservaId = $reserva->id;
        } elseif ($status === 'espera') {
            // Coloca na fila de espera: reserva sem acomodação fixa
            $ordemFila = AgendaReserva::where('agenda_periodo_id', $periodoId)
                ->where('colonia_id', $coloniaId)
                ->whereNull('colonia_acomodacao_id')
                ->max('ordem_fila') + 1;

            $reserva = AgendaReserva::create([
                'agenda_periodo_id' => $periodoId,
                'colonia_id' => $coloniaId,
                'agenda_hospede_id' => $hospedeId,
                'colonia_acomodacao_id' => null,
                'status' => 'reservado',
                'ordem_fila' => $ordemFila,
            ]);
            $reservaId = $reserva->id;
        }

        $inscricao->update([
            'status' => $status,
            'acomodacao_id' => $status === 'sorteado' ? $request->acomodacao_id : null,
            'observacao' => $request->observacao ?? $inscricao->observacao,
            'reserva_id' => $reservaId,
            'ordem_espera' => $status === 'espera'
                ? ($inscricao->ordem_espera ?? AgendaInscricao::where('colonia_id', $coloniaId)->where('agenda_periodo_id', $periodoId)->where('status', 'espera')->count() + 1)
                : null,
        ]);

        return redirect()->route('agenda.inscricoes.index', [
            'colonia_id' => $coloniaId,
            'periodo_id' => $periodoId,
        ])->with('success', 'Resultado do sorteio atualizado!');
    }

    /**
     * Remove uma inscrição (e opcionalmente a reserva gerada).
     */
    public function destroy(Request $request, AgendaInscricao $inscricao)
    {
        $coloniaId = $inscricao->colonia_id ?? $request->query('colonia_id');
        $periodoId = $inscricao->agenda_periodo_id ?? $request->query('periodo_id');

        // Remover reserva vinculada se existir
        if ($inscricao->reserva_id) {
            AgendaReserva::find($inscricao->reserva_id)?->delete();
        }

        $inscricao->delete();

        return redirect()->route('agenda.inscricoes.index', [
            'colonia_id' => $coloniaId,
            'periodo_id' => $periodoId,
        ])->with('success', 'Inscrição removida!');
    }
}
