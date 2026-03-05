<?php

namespace App\Http\Controllers\Agenda;

use App\Http\Controllers\Controller;
use App\Models\Agenda\AgendaReservaHistorico;
use Illuminate\Http\Request;

class AgendaReservaController extends Controller
{
    public function index(Request $request)
    {
        // 1. Carregar Períodos e Colônias para os Filtros
        $periodos = \App\Models\AgendaPeriodo::where('ativo', true)->orderBy('data_inicial', 'desc')->get();
        $colonias = \App\Models\Colonia::where('ativo', true)->orderBy('nome')->get();

        $periodoSelecionado = $request->get('periodo_id');
        $coloniaSelecionada = $request->get('colonia_id');

        $reservas = collect();
        $acomodacoes = collect();
        $filaEspera = collect();

        // Calcular Estatísticas
        $estatisticas = [
            'total' => 0,
            'reservado' => 0,
            'confirmado' => 0,
            'pago' => 0,
            'bloqueado' => 0,
            'livre' => 0,
            'espera' => 0,
        ];

        // 2. Se o usuário filtrou, carregar os dados específicos
        if ($periodoSelecionado && $coloniaSelecionada) {
            $colonia = \App\Models\Colonia::with([
                'acomodacoes' => function ($q) {
                    $q->where('ativo', true)
                        ->orderByRaw('CAST(identificador AS UNSIGNED) ASC')
                        ->orderBy('identificador');
                }
            ])->findOrFail($coloniaSelecionada);

            $acomodacoes = $colonia->acomodacoes;

            // Buscar Reservas para esta Colônia neste Período
            $todasReservas = \App\Models\AgendaReserva::with(['hospede', 'acomodacao'])
                ->where('agenda_periodo_id', $periodoSelecionado)
                ->where('colonia_id', $coloniaSelecionada)
                ->orderBy('ordem_fila')
                ->get();

            // Separar o que é Acomodação Fixa e o que é Fila de Espera
            $reservas = $todasReservas->whereNotNull('colonia_acomodacao_id')->keyBy('colonia_acomodacao_id');
            $filaEspera = $todasReservas->whereNull('colonia_acomodacao_id')->values();

            // Calcular Estatísticas
            $estatisticas['total'] = $acomodacoes->count();
            $estatisticas['espera'] = $filaEspera->count();

            foreach ($reservas as $res) {
                if ($res->status == 'pago') {
                    $estatisticas['pago']++;
                } elseif ($res->status == 'confirmado') {
                    $estatisticas['confirmado']++;
                } elseif ($res->status == 'reservado') {
                    $estatisticas['reservado']++;
                } else {
                    $estatisticas['bloqueado']++;
                }
            }
            $estatisticas['livre'] = $estatisticas['total'] - ($estatisticas['pago'] + $estatisticas['confirmado'] + $estatisticas['reservado'] + $estatisticas['bloqueado']);
        }

        // 3. Trazer Empresas para o dropdown de adição na planilha
        $empresas = \App\Models\Empresa::orderBy('razao_social')->get();

        return view('agenda.reservas.index', compact(
            'periodos',
            'colonias',
            'periodoSelecionado',
            'coloniaSelecionada',
            'acomodacoes',
            'reservas',
            'filaEspera',
            'empresas',
            'estatisticas'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'agenda_periodo_id' => 'required|exists:agenda_periodos,id',
            'colonia_id' => 'required|exists:colonias,id',
            'colonia_acomodacao_id' => 'nullable|exists:colonia_acomodacaos,id',
            'bloqueio_nota' => 'nullable|string|max:255',
            'status' => 'required|string',
            'nome_hospede' => 'nullable|string|max:255|required_without:bloqueio_nota',
            'telefone_hospede' => 'nullable|string|max:20',
            'email_hospede' => 'nullable|email|max:255',
            'empresa_id' => 'nullable|exists:empresas,id',
        ]);

        $hospedeId = null;

        if (empty($validated['bloqueio_nota']) && !empty($validated['nome_hospede'])) {
            $hospede = \App\Models\AgendaHospede::firstOrCreate(
                ['nome' => $validated['nome_hospede'], 'telefone' => $validated['telefone_hospede']],
                ['email' => $validated['email_hospede'], 'empresa_id' => $validated['empresa_id']]
            );
            $hospedeId = $hospede->id;

            if ($hospede->email !== $validated['email_hospede'] || $hospede->empresa_id != $validated['empresa_id']) {
                $hospede->update([
                    'email' => $validated['email_hospede'] ?? $hospede->email,
                    'empresa_id' => $validated['empresa_id'] ?? $hospede->empresa_id
                ]);
            }
        }

        $ordemFila = null;
        $status = $validated['status'];

        // Se houver nota de bloqueio, o status deve ser obrigatoriamente bloqueado
        if (!empty($validated['bloqueio_nota'])) {
            $status = 'bloqueado';
        } elseif ($status === 'bloqueado') {
            // Se for hóspede mas selecionou bloqueado por erro, volta para reservado
            $status = 'reservado';
        }

        if (empty($validated['colonia_acomodacao_id'])) {
            $ultimaOrdem = \App\Models\AgendaReserva::where('agenda_periodo_id', $validated['agenda_periodo_id'])
                ->where('colonia_id', $validated['colonia_id'])
                ->whereNull('colonia_acomodacao_id')
                ->max('ordem_fila') ?? 0;

            $ordemFila = $ultimaOrdem + 1;
            $status = 'fila_espera';
        }

        \App\Models\AgendaReserva::create([
            'agenda_periodo_id' => $validated['agenda_periodo_id'],
            'colonia_id' => $validated['colonia_id'],
            'colonia_acomodacao_id' => $validated['colonia_acomodacao_id'],
            'agenda_hospede_id' => $hospedeId,
            'bloqueio_nota' => $validated['bloqueio_nota'] ?? null,
            'status' => $status,
            'ordem_fila' => $ordemFila
        ]);

        return redirect()->route('agenda.reservas.index', [
            'periodo_id' => $validated['agenda_periodo_id'],
            'colonia_id' => $validated['colonia_id'],
        ])->with('success', 'Registro adicionado com sucesso!');
    }

    public function update(Request $request, string $id)
    {
        $reserva = \App\Models\AgendaReserva::with('hospede')->findOrFail($id);

        $validated = $request->validate([
            'bloqueio_nota' => 'nullable|string|max:255',
            'status' => 'required|string',
            'nome_hospede' => 'nullable|string|max:255|required_without:bloqueio_nota',
            'telefone_hospede' => 'nullable|string|max:20',
            'email_hospede' => 'nullable|email|max:255',
            'empresa_id' => 'nullable|exists:empresas,id',
        ]);

        $hospedeId = null;

        if (empty($validated['bloqueio_nota']) && !empty($validated['nome_hospede'])) {
            $hospede = \App\Models\AgendaHospede::firstOrCreate(
                ['nome' => $validated['nome_hospede'], 'telefone' => $validated['telefone_hospede']],
                ['email' => $validated['email_hospede'], 'empresa_id' => $validated['empresa_id']]
            );
            $hospedeId = $hospede->id;

            if ($hospede->email !== $validated['email_hospede'] || $hospede->empresa_id != $validated['empresa_id']) {
                $hospede->update([
                    'email' => $validated['email_hospede'] ?? $hospede->email,
                    'empresa_id' => $validated['empresa_id'] ?? $hospede->empresa_id
                ]);
            }
        }

        $status = $validated['status'];
        if (!empty($validated['bloqueio_nota'])) {
            $status = 'bloqueado';
        } elseif ($status === 'bloqueado') {
            $status = 'reservado';
        }

        $reserva->update([
            'agenda_hospede_id' => $hospedeId,
            'bloqueio_nota' => $validated['bloqueio_nota'] ?? null,
            'status' => $status,
        ]);

        return redirect()->route('agenda.reservas.index', [
            'periodo_id' => $reserva->agenda_periodo_id,
            'colonia_id' => $reserva->colonia_id,
        ])->with('success', 'Reserva atualizada com sucesso!');
    }

    /**
     * Promove um hóspede da Fila de Espera para uma acomodação específica.
     */
    public function promoverVaga(Request $request, string $id)
    {
        $reserva = \App\Models\AgendaReserva::findOrFail($id);

        $request->validate([
            'colonia_acomodacao_id' => 'required|exists:colonia_acomodacaos,id',
        ]);

        $jaOcupada = \App\Models\AgendaReserva::where('agenda_periodo_id', $reserva->agenda_periodo_id)
            ->where('colonia_id', $reserva->colonia_id)
            ->where('colonia_acomodacao_id', $request->colonia_acomodacao_id)
            ->exists();

        if ($jaOcupada) {
            return redirect()->route('agenda.reservas.index', [
                'periodo_id' => $reserva->agenda_periodo_id,
                'colonia_id' => $reserva->colonia_id,
            ])->with('error', 'Esta acomodação já está ocupada. Escolha outra vaga disponível.');
        }

        $reserva->update([
            'colonia_acomodacao_id' => $request->colonia_acomodacao_id,
            'status' => 'reservado',
            'ordem_fila' => null,
        ]);

        return redirect()->route('agenda.reservas.index', [
            'periodo_id' => $reserva->agenda_periodo_id,
            'colonia_id' => $reserva->colonia_id,
        ])->with('success', 'Hóspede promovido para a acomodação com sucesso!');
    }

    /**
     * Exclui reserva registrando motivo no histórico.
     */
    public function excluirComMotivo(Request $request, string $id)
    {
        $request->validate([
            'motivo' => 'required|string|min:3|max:1000',
        ]);

        $reserva = \App\Models\AgendaReserva::with(['hospede.empresa', 'acomodacao', 'periodo', 'colonia'])->findOrFail($id);

        // Salvar snapshot no histórico antes de excluir
        AgendaReservaHistorico::create([
            'colonia_id' => $reserva->colonia_id,
            'colonia_nome' => $reserva->colonia?->nome ?? '—',
            'periodo_id' => $reserva->agenda_periodo_id,
            'periodo_descricao' => $reserva->periodo?->descricao ?? '—',
            'periodo_data_inicial' => $reserva->periodo?->data_inicial,
            'periodo_data_final' => $reserva->periodo?->data_final,
            'hospede_nome' => $reserva->hospede?->nome ?? null,
            'hospede_telefone' => $reserva->hospede?->telefone ?? null,
            'hospede_email' => $reserva->hospede?->email ?? null,
            'acomodacao_identificador' => $reserva->acomodacao?->identificador ?? null,
            'acomodacao_tipo' => $reserva->acomodacao?->tipo ?? null,
            'status_reserva' => $reserva->status,
            'bloqueio_nota' => $reserva->bloqueio_nota,
            'motivo' => $request->motivo,
            'excluido_por' => auth()->id(),
            'excluido_por_nome' => auth()->user()?->name ?? 'Sistema',
        ]);

        $periodo_id = $reserva->agenda_periodo_id;
        $colonia_id = $reserva->colonia_id;

        $reserva->delete();

        return redirect()->route('agenda.reservas.index', [
            'periodo_id' => $periodo_id,
            'colonia_id' => $colonia_id,
        ])->with('success', 'Reserva excluída e registrada no histórico!');
    }

    public function destroy(string $id)
    {
        $reserva = \App\Models\AgendaReserva::findOrFail($id);

        $periodo_id = $reserva->agenda_periodo_id;
        $colonia_id = $reserva->colonia_id;

        $reserva->delete();

        return redirect()->route('agenda.reservas.index', [
            'periodo_id' => $periodo_id,
            'colonia_id' => $colonia_id,
        ])->with('success', 'Reserva/Vaga liberada com sucesso.');
    }
}
