<?php

namespace App\Http\Controllers;

use App\Models\Demanda;
use App\Models\DemandaChecklist;
use App\Models\DemandaAnexo;
use App\Models\DemandaHistorico;
use App\Models\User;
use App\Models\Cliente;
use App\Jobs\SendKwikNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemandaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Demanda::with(['criador', 'responsavelUsuario']);

        // 2. Aplicar filtros de visão (Responsabilidade)
        $visao = $request->input('visao', 'todas');
        if ($visao === 'minhas') {
            $query->where('tipo_responsavel', 'usuario')
                  ->where('responsavel_usuario_id', auth()->id());
        } elseif ($visao === 'criadas_por_mim') {
            $query->where('criador_id', auth()->id());
        } elseif ($visao === 'todas') {
            // Se não tem permissão para gerenciar, limita às delegações dele
            if (!auth()->user()->temPermissao('demandas.gerenciar')) {
                $query->where(function($q) {
                    $q->where('criador_id', auth()->id())
                      ->orWhere(function($sub) {
                          $sub->where('tipo_responsavel', 'usuario')
                              ->where('responsavel_usuario_id', auth()->id());
                      });
                });
            }
        }

        // 3. Aplicar filtros de status/prazo
        $status = $request->input('status');
        if ($status) {
            if ($status === 'vencidas') {
                $query->whereIn('status', [Demanda::STATUS_ABERTA, Demanda::STATUS_AGUARDANDO])
                      ->whereNotNull('prazo')
                      ->where('prazo', '<', now());
            } else {
                $query->where('status', $status);
            }
        }

        // 4. Aplicar filtros de prioridade
        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->prioridade);
        }

        // 5. Aplicar busca por texto
        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function($q) use ($busca) {
                $q->where('titulo', 'like', "%{$busca}%")
                  ->orWhere('descricao', 'like', "%{$busca}%")
                  ->orWhere('responsavel_nome', 'like', "%{$busca}%");
            });
        }

        $demandas = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->all());

        // 6. Contadores de Estatísticas (considerando as restrições de visibilidade do usuário)
        $statsQuery = Demanda::query();
        if (!auth()->user()->temPermissao('demandas.gerenciar')) {
            $statsQuery->where(function($q) {
                $q->where('criador_id', auth()->id())
                  ->orWhere(function($sub) {
                      $sub->where('tipo_responsavel', 'usuario')
                          ->where('responsavel_usuario_id', auth()->id());
                  });
            });
        }

        $stats = [
            'abertas' => (clone $statsQuery)->where('status', Demanda::STATUS_ABERTA)
                ->where(function ($q) {
                    $q->whereNull('prazo')->orWhere('prazo', '>=', now());
                })->count(),
            'aguardando' => (clone $statsQuery)->where('status', Demanda::STATUS_AGUARDANDO)->count(),
            'vencidas' => (clone $statsQuery)->whereIn('status', [Demanda::STATUS_ABERTA, Demanda::STATUS_AGUARDANDO])
                ->whereNotNull('prazo')
                ->where('prazo', '<', now())->count(),
            'executadas' => (clone $statsQuery)->where('status', Demanda::STATUS_EXECUTADA)->count(),
        ];

        return view('demandas.index', compact('demandas', 'stats'));
    }

    public function create()
    {
        if (!auth()->user()->temPermissao('demandas.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $usuarios = User::orderBy('name')->get();
        $clientes = Cliente::where('ativo', true)->orderBy('nome')->get();

        return view('demandas.create', compact('usuarios', 'clientes'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->temPermissao('demandas.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'prazo' => 'nullable|date',
            'prioridade' => 'required|in:baixa,media,alta,urgente',
            'tipo_responsavel' => 'required|in:usuario,externo',
            'responsavel_usuario_id' => 'required_if:tipo_responsavel,usuario|nullable|exists:users,id',
            'responsavel_nome' => 'required_if:tipo_responsavel,externo|nullable|string|max:255',
            'responsavel_telefone' => 'required_if:tipo_responsavel,externo|nullable|string|max:20',
            'responsavel_email' => 'nullable|email|max:255',
            'checklist_items' => 'nullable|array',
            'checklist_items.*' => 'nullable|string|max:255',
            'anexos.*' => 'nullable|file|max:10240',
        ]);

        DB::beginTransaction();
        try {
            // Determinar status inicial
            // External demands start as "aguardando"
            $status = Demanda::STATUS_ABERTA;
            if ($request->tipo_responsavel === 'externo') {
                $status = Demanda::STATUS_AGUARDANDO;
            }

            // Controle de leitura inicial pelo responsável
            $lida = false;
            if ($request->tipo_responsavel === 'usuario' && $request->responsavel_usuario_id == auth()->id()) {
                $lida = true;
            }

            $demanda = Demanda::create([
                'titulo' => $request->titulo,
                'descricao' => $request->descricao,
                'prazo' => $request->prazo,
                'prioridade' => $request->prioridade,
                'criador_id' => auth()->id(),
                'tipo_responsavel' => $request->tipo_responsavel,
                'responsavel_usuario_id' => $request->tipo_responsavel === 'usuario' ? $request->responsavel_usuario_id : null,
                'lida_pelo_responsavel' => $lida,
                'responsavel_nome' => $request->tipo_responsavel === 'externo' ? $request->responsavel_nome : null,
                'responsavel_telefone' => $request->tipo_responsavel === 'externo' ? preg_replace('/\D/', '', $request->responsavel_telefone) : null,
                'responsavel_email' => $request->tipo_responsavel === 'externo' ? $request->responsavel_email : null,
                'status' => $status,
            ]);

            // Checklist de Sub-tarefas
            if ($request->filled('checklist_items')) {
                foreach ($request->checklist_items as $itemText) {
                    if (!empty(trim($itemText))) {
                        $demanda->checklists()->create(['item' => $itemText]);
                    }
                }
            }

            // Upload de Anexos
            if ($request->hasFile('anexos')) {
                foreach ($request->file('anexos') as $file) {
                    $caminho = $file->store('demandas', 'public');
                    $demanda->anexos()->create([
                        'caminho' => $caminho,
                        'nome_original' => $file->getClientOriginalName(),
                        'tipo_origem' => 'criador',
                    ]);
                }
            }

            // Criar registro de histórico
            $demanda->historicos()->create([
                'user_id' => auth()->id(),
                'acao' => 'criada',
                'descricao' => "Demanda cadastrada e delegada a " . ($request->tipo_responsavel === 'usuario' ? User::find($request->responsavel_usuario_id)->name : $request->responsavel_nome) . ".",
            ]);

            // Disparar Notificação por WhatsApp se for Externo
            if ($request->tipo_responsavel === 'externo') {
                $prazoFormatted = $demanda->prazo ? $demanda->prazo->format('d/m/Y H:i') : 'Não definido';
                $descricaoCurta = Str::limit($demanda->descricao, 100);
                $criadorName = auth()->user()->nickname ?: auth()->user()->name;

                SendKwikNotificationJob::dispatch(
                    $demanda->responsavel_telefone,
                    'nova_demanda_externa',
                    [
                        $demanda->responsavel_nome, // {{1}} - Responsável
                        $criadorName,                 // {{2}} - Criador
                        $demanda->titulo,             // {{3}} - Assunto
                        $descricaoCurta,             // {{4}} - Detalhes
                        $prazoFormatted,             // {{5}} - Prazo
                        $criadorName                 // {{6}} - Retorno
                    ],
                    auth()->id()
                );

                $demanda->historicos()->create([
                    'user_id' => auth()->id(),
                    'acao' => 'notificada_whatsapp',
                    'descricao' => "Disparada notificação por WhatsApp para o responsável externo: {$demanda->responsavel_nome} ({$demanda->responsavel_telefone}).",
                ]);
            }

            DB::commit();
            return redirect()->route('demandas.index')->with('success', 'Demanda criada e delegada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Erro ao salvar demanda: ' . $e->getMessage());
        }
    }

    public function show(Demanda $demanda)
    {
        // Restrição de acesso
        if (!auth()->user()->temPermissao('demandas.gerenciar') && 
            $demanda->criador_id !== auth()->id() && 
            ($demanda->tipo_responsavel !== 'usuario' || $demanda->responsavel_usuario_id !== auth()->id())) {
            abort(403, 'Acesso não autorizado a esta demanda.');
        }

        // Marcar como lida se o usuário logado for o responsável
        if ($demanda->tipo_responsavel === 'usuario' && $demanda->responsavel_usuario_id == auth()->id() && !$demanda->lida_pelo_responsavel) {
            $demanda->update(['lida_pelo_responsavel' => true]);
        }

        $demanda->load(['criador', 'responsavelUsuario', 'checklists', 'anexos', 'historicos.user']);
        $usuarios = User::orderBy('name')->get();

        return view('demandas.show', compact('demanda', 'usuarios'));
    }

    public function edit(Demanda $demanda)
    {
        if (!auth()->user()->temPermissao('demandas.criar')) {
            abort(403);
        }

        // Apenas criador ou gestor edita
        if (!auth()->user()->temPermissao('demandas.gerenciar') && $demanda->criador_id !== auth()->id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $usuarios = User::orderBy('name')->get();
        $clientes = Cliente::where('ativo', true)->orderBy('nome')->get();

        return view('demandas.edit', compact('demanda', 'usuarios', 'clientes'));
    }

    public function update(Request $request, Demanda $demanda)
    {
        if (!auth()->user()->temPermissao('demandas.criar')) {
            abort(403);
        }

        if (!auth()->user()->temPermissao('demandas.gerenciar') && $demanda->criador_id !== auth()->id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'prazo' => 'nullable|date',
            'prioridade' => 'required|in:baixa,media,alta,urgente',
        ]);

        DB::beginTransaction();
        try {
            $demanda->update([
                'titulo' => $request->titulo,
                'descricao' => $request->descricao,
                'prazo' => $request->prazo,
                'prioridade' => $request->prioridade,
            ]);

            $demanda->historicos()->create([
                'user_id' => auth()->id(),
                'acao' => 'alterada',
                'descricao' => "Demanda alterada pelo autor " . auth()->user()->name . ".",
            ]);

            DB::commit();
            return redirect()->route('demandas.show', $demanda)->with('success', 'Demanda atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao atualizar demanda: ' . $e->getMessage());
        }
    }

    public function devolutiva(Request $request, Demanda $demanda)
    {
        // Autorizado para Criador, Responsável ou Gestor
        $podeDevalutar = auth()->user()->temPermissao('demandas.gerenciar') || 
            $demanda->criador_id === auth()->id() || 
            ($demanda->tipo_responsavel === 'usuario' && $demanda->responsavel_usuario_id === auth()->id());

        if (!$podeDevalutar) {
            abort(403, 'Ação não autorizada.');
        }

        $request->validate([
            'status' => 'required|in:executada,nao_executada',
            'motivo_devolutiva' => 'required|string',
            'anexos.*' => 'nullable|file|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $demanda->update([
                'status' => $request->status,
                'motivo_devolutiva' => $request->motivo_devolutiva,
                'devolutiva_em' => now(),
            ]);

            // Se houver anexos na devolutiva (comprovantes)
            if ($request->hasFile('anexos')) {
                foreach ($request->file('anexos') as $file) {
                    $caminho = $file->store('demandas', 'public');
                    $demanda->anexos()->create([
                        'caminho' => $caminho,
                        'nome_original' => $file->getClientOriginalName(),
                        'tipo_origem' => 'devolutiva',
                    ]);
                }
            }

            $statusNome = $request->status === Demanda::STATUS_EXECUTADA ? 'EXECUTADA' : 'NÃO EXECUTADA';
            $demanda->historicos()->create([
                'user_id' => auth()->id(),
                'acao' => 'devolutiva',
                'descricao' => "Devolutiva registrada como {$statusNome} por " . auth()->user()->name . ". Justificativa: " . $request->motivo_devolutiva,
            ]);

            DB::commit();
            return redirect()->route('demandas.show', $demanda)->with('success', 'Devolutiva registrada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao registrar devolutiva: ' . $e->getMessage());
        }
    }

    public function reencaminhar(Request $request, Demanda $demanda)
    {
        // Apenas criador ou gestor reencaminha
        if (!auth()->user()->temPermissao('demandas.gerenciar') && $demanda->criador_id !== auth()->id()) {
            abort(403, 'Ação não autorizada.');
        }

        $request->validate([
            'tipo_responsavel' => 'required|in:usuario,externo',
            'responsavel_usuario_id' => 'required_if:tipo_responsavel,usuario|nullable|exists:users,id',
            'responsavel_nome' => 'required_if:tipo_responsavel,externo|nullable|string|max:255',
            'responsavel_telefone' => 'required_if:tipo_responsavel,externo|nullable|string|max:20',
            'responsavel_email' => 'nullable|email|max:255',
            'motivo_reencaminhamento' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $antigoResponsavel = $demanda->tipo_responsavel === 'usuario' 
                ? ($demanda->responsavelUsuario ? $demanda->responsavelUsuario->name : 'N/D') 
                : $demanda->responsavel_nome;

            $status = $request->tipo_responsavel === 'externo' ? Demanda::STATUS_AGUARDANDO : Demanda::STATUS_ABERTA;

            $demanda->update([
                'tipo_responsavel' => $request->tipo_responsavel,
                'responsavel_usuario_id' => $request->tipo_responsavel === 'usuario' ? $request->responsavel_usuario_id : null,
                'responsavel_nome' => $request->tipo_responsavel === 'externo' ? $request->responsavel_nome : null,
                'responsavel_telefone' => $request->tipo_responsavel === 'externo' ? preg_replace('/\D/', '', $request->responsavel_telefone) : null,
                'responsavel_email' => $request->tipo_responsavel === 'externo' ? $request->responsavel_email : null,
                'lida_pelo_responsavel' => false,
                'status' => $status,
            ]);

            $novoResponsavel = $request->tipo_responsavel === 'usuario'
                ? User::find($request->responsavel_usuario_id)->name
                : $request->responsavel_nome;

            $demanda->historicos()->create([
                'user_id' => auth()->id(),
                'acao' => 'encaminhada',
                'descricao' => "Demanda reencaminhada de {$antigoResponsavel} para {$novoResponsavel} por " . auth()->user()->name . ". Motivo: " . $request->motivo_reencaminhamento,
            ]);

            // Se reencaminhado para externo, dispara WhatsApp
            if ($request->tipo_responsavel === 'externo') {
                $prazoFormatted = $demanda->prazo ? $demanda->prazo->format('d/m/Y H:i') : 'Não definido';
                $descricaoCurta = Str::limit($demanda->descricao, 100);
                $criadorName = auth()->user()->nickname ?: auth()->user()->name;

                SendKwikNotificationJob::dispatch(
                    $demanda->responsavel_telefone,
                    'nova_demanda_externa',
                    [
                        $demanda->responsavel_nome, // {{1}} - Responsável
                        $criadorName,                 // {{2}} - Criador
                        $demanda->titulo,             // {{3}} - Assunto
                        $descricaoCurta,             // {{4}} - Detalhes
                        $prazoFormatted,             // {{5}} - Prazo
                        $criadorName                 // {{6}} - Retorno
                    ],
                    auth()->id()
                );

                $demanda->historicos()->create([
                    'user_id' => auth()->id(),
                    'acao' => 'notificada_whatsapp',
                    'descricao' => "Disparada notificação por WhatsApp para o novo responsável externo: {$demanda->responsavel_nome} ({$demanda->responsavel_telefone}).",
                ]);
            }

            DB::commit();
            return redirect()->route('demandas.show', $demanda)->with('success', 'Demanda reencaminhada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao reencaminhar demanda: ' . $e->getMessage());
        }
    }

    public function toggleChecklist(Request $request, Demanda $demanda, DemandaChecklist $checklist)
    {
        // Autorização
        $podeMarcar = auth()->user()->temPermissao('demandas.gerenciar') || 
            $demanda->criador_id === auth()->id() || 
            ($demanda->tipo_responsavel === 'usuario' && $demanda->responsavel_usuario_id === auth()->id());

        if (!$podeMarcar) {
            return response()->json(['success' => false, 'message' => 'Ação não autorizada.'], 403);
        }

        $request->validate([
            'concluido' => 'required|boolean'
        ]);

        $checklist->update(['concluido' => $request->concluido]);

        $estado = $request->concluido ? 'concluído' : 'não concluído';
        $demanda->historicos()->create([
            'user_id' => auth()->id(),
            'acao' => 'alterada',
            'descricao' => "Item do checklist '{$checklist->item}' marcado como {$estado} por " . auth()->user()->name . ".",
        ]);

        return response()->json([
            'success' => true,
            'progresso' => $demanda->progresso_checklist
        ]);
    }

    public function destroy(Demanda $demanda)
    {
        if (!auth()->user()->temPermissao('demandas.gerenciar')) {
            abort(403, 'Ação não autorizada.');
        }

        // Excluir arquivos anexos associados
        foreach ($demanda->anexos as $anexo) {
            Storage::disk('public')->delete($anexo->caminho);
        }

        $demanda->delete();

        return redirect()->route('demandas.index')->with('success', 'Demanda excluída permanentemente.');
    }
}
