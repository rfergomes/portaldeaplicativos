<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AtivoMovimentacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\AtivoMovimentacao::with(['equipamento', 'usuario.empresa', 'responsavel']);

        if ($request->filled('equipamento_id')) {
            $query->where('equipamento_id', $request->equipamento_id);
        }
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('destino')) {
            $query->where('destino', 'like', '%' . $request->destino . '%');
        }
        if ($request->filled('responsavel_id')) {
            $query->where('responsavel_id', $request->responsavel_id);
        }
        if ($request->filled('data_inicio')) {
            $query->whereDate('data_movimentacao', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('data_movimentacao', '<=', $request->data_fim);
        }

        if ($request->export === 'pdf') {
            $movimentacoes = $query->orderBy('data_movimentacao', 'desc')->get();
            $pdf = Pdf::loadView('ativos.movimentacoes.pdf', compact('movimentacoes'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('historico_movimentacoes_' . now()->format('d_m_Y_H_i_s') . '.pdf');
        }

        $movimentacoes = $query->orderBy('data_movimentacao', 'desc')->paginate(20);
        $equipamentos = \App\Models\AtivoEquipamento::select('id', 'descricao', 'modelo')->orderBy('descricao')->get();
        $usuarios = \App\Models\AtivoUsuario::select('id', 'nome')->orderBy('nome')->get();
        $operadores = \App\Models\User::select('id', 'name')->orderBy('name')->get();

        // Estatísticas
        $totalMovimentacoes = \App\Models\AtivoMovimentacao::count();
        $movimentacoesMes = \App\Models\AtivoMovimentacao::whereMonth('data_movimentacao', date('m'))->whereYear('data_movimentacao', date('Y'))->count();
        $emprestimosRealizados = \App\Models\AtivoMovimentacao::where('tipo', 'emprestimo')->count();
        $baixasExecutadas = \App\Models\AtivoMovimentacao::where('tipo', 'baixa')->count();

        return view('ativos.movimentacoes.index', compact('movimentacoes', 'equipamentos', 'usuarios', 'operadores', 'totalMovimentacoes', 'movimentacoesMes', 'emprestimosRealizados', 'baixasExecutadas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipamento_id'          => 'required|exists:ativo_equipamentos,id',
            'tipo'                    => 'required|in:cessao,emprestimo,devolucao,manutencao,transferencia,baixa',
            'usuario_id'              => 'required_if:tipo,cessao,emprestimo|nullable|exists:ativo_usuarios,id',
            'data_previsao_devolucao' => 'required_if:tipo,emprestimo|nullable|date',
            'data_retirada'           => 'nullable|date',
            'local_manutencao'        => 'required_if:tipo,manutencao|nullable|string|max:255',
            'contato_manutencao'      => 'nullable|string|max:255',
            'destino_departamento_id' => 'required_if:tipo,transferencia|nullable|exists:ativo_departamentos,id',
            'destino_estacao_id'      => 'nullable|exists:ativo_estacoes,id',
            'destino'                 => 'nullable|string|max:255',
            'acessorios'              => 'nullable|string',
            'observacao'              => 'required_if:tipo,baixa|nullable|string',
        ]);

        return \DB::transaction(function () use ($validated) {
            $equipamento = \App\Models\AtivoEquipamento::lockForUpdate()->findOrFail($validated['equipamento_id']);

            $origem = $equipamento->localizacao_atual;
            $status = $equipamento->status;
            $tipoUso = $equipamento->tipo_uso;
            $localizacao = $equipamento->localizacao_atual;
            $cessaoId = null;
            $ultimaMovimentacao = $equipamento->ultimaMovimentacao ? clone $equipamento->ultimaMovimentacao : null;

            // Criar cessão se necessário
            if ($validated['tipo'] === 'cessao') {
                $ultimoId = \App\Models\AtivoCessao::max('id') ?? 0;
                $codigo = 'CSN' . str_pad($ultimoId + 1, 3, '0', STR_PAD_LEFT);
                $cessao = \App\Models\AtivoCessao::create([
                    'usuario_id'    => $validated['usuario_id'],
                    'data_cessao'   => now(),
                    'codigo_cessao' => $codigo,
                    'observacoes'   => $validated['observacao'] ?? null,
                ]);
                $cessaoId = $cessao->id;
            }

            // Atualizar status, tipo_uso e localização do equipamento
            switch ($validated['tipo']) {
                case 'cessao':
                    $status = 'em_uso';
                    $tipoUso = 'cessão';
                    $localizacao = \App\Models\AtivoUsuario::find($validated['usuario_id'])->nome ?? 'Desconhecido';
                    break;

                case 'emprestimo':
                    $status = 'em_uso';
                    $tipoUso = 'empréstimo';
                    $localizacao = \App\Models\AtivoUsuario::find($validated['usuario_id'])->nome ?? 'Desconhecido';
                    break;

                case 'devolucao':
                    $status = 'disponivel';
                    $tipoUso = null;
                    $localizacao = 'Estoque';
                    break;

                case 'manutencao':
                    $status = 'manutencao';
                    $tipoUso = null;
                    $localizacao = $validated['local_manutencao'] ?? 'Manutenção';
                    break;

                case 'transferencia':
                    $status = 'em_uso';
                    $tipoUso = null; // Limpa cessão/empréstimo anterior

                    $departamento = \App\Models\AtivoDepartamento::find($validated['destino_departamento_id']);
                    $estacao = isset($validated['destino_estacao_id'])
                        ? \App\Models\AtivoEstacao::find($validated['destino_estacao_id'])
                        : null;

                    if ($estacao && $departamento) {
                        $localizacao = $departamento->nome . ' / ' . $estacao->nome;

                        // REGRA: TI / ESTOQUE -> Disponível
                        $deptoNome = strtoupper(trim($departamento->nome));
                        $estacaoNome = strtoupper(trim($estacao->nome));

                        if (($deptoNome === 'TI' || $deptoNome === 'TI - TECNOLOGIA DA INFORMAÇÃO') && $estacaoNome === 'ESTOQUE') {
                            $status = 'disponivel';
                        }
                    } elseif ($departamento) {
                        $localizacao = $departamento->nome;
                    } else {
                        $localizacao = $validated['destino'] ?? $localizacao;
                    }

                    // Atualizar estação do equipamento
                    $equipamento->estacao_id = $validated['destino_estacao_id'] ?? null;
                    break;

                case 'baixa':
                    $status = 'baixado';
                    $tipoUso = null;
                    $localizacao = 'Baixado';
                    break;
            }

            // Registrar Movimentação
            $novaMovimentacao = \App\Models\AtivoMovimentacao::create([
                'equipamento_id'          => $equipamento->id,
                'usuario_id'              => $validated['usuario_id'] ?? null,
                'tipo'                    => $validated['tipo'],
                'data_movimentacao'       => now(),
                'data_previsao_devolucao' => $validated['data_previsao_devolucao'] ?? null,
                'data_retirada'           => $validated['data_retirada'] ?? null,
                'local_manutencao'        => $validated['local_manutencao'] ?? null,
                'contato_manutencao'      => $validated['contato_manutencao'] ?? null,
                'destino_departamento_id' => $validated['destino_departamento_id'] ?? null,
                'destino_estacao_id'      => $validated['destino_estacao_id'] ?? null,
                'responsavel_id'          => auth()->id(),
                'cessao_id'               => $cessaoId,
                'origem'                  => $origem,
                'destino'                 => $localizacao,
                'acessorios'              => $validated['acessorios'] ?? null,
                'observacao'              => $validated['observacao'] ?? null,
            ]);

            // Atualizar Equipamento
            $equipamento->update([
                'status'                => $status,
                'tipo_uso'              => $tipoUso,
                'localizacao_atual'     => $localizacao,
                'data_devolucao_prevista' => $validated['data_previsao_devolucao'] ?? null,
            ]);

            $msg = match($validated['tipo']) {
                'cessao'       => 'Cessão registrada com sucesso!',
                'emprestimo'   => 'Empréstimo registrado com sucesso!',
                'devolucao'    => 'Devolução registrada com sucesso!',
                'manutencao'   => 'Envio para manutenção registrado com sucesso!',
                'transferencia' => 'Transferência interna registrada com sucesso!',
                'baixa'        => 'Baixa de equipamento registrada com sucesso!',
                default        => 'Movimentação registrada com sucesso!',
            };

            $cessao_pdf_id = null;

            if ($validated['tipo'] === 'emprestimo') {
                $usuario = \App\Models\AtivoUsuario::find($validated['usuario_id']);
                if ($usuario && !empty($usuario->telefone)) {
                    $nomeUsuario = explode(' ', trim($usuario->nome))[0];
                    $descricaoEquip = $equipamento->descricao . ' (' . $equipamento->identificador . ')';
                    $acessoriosText = $novaMovimentacao->acessorios ?? 'Nenhum';
                    $dataDevolucaoTexto = $novaMovimentacao->data_previsao_devolucao 
                        ? \Carbon\Carbon::parse($novaMovimentacao->data_previsao_devolucao)->format('d/m/Y')
                        : 'Não informada';

                    dispatch(new \App\Jobs\SendKwikNotificationJob(
                        $usuario->telefone,
                        'equipamento_retirada',
                        [$nomeUsuario, $descricaoEquip, $acessoriosText, $dataDevolucaoTexto]
                    ));
                }
            } elseif ($validated['tipo'] === 'devolucao' && $ultimaMovimentacao) {
                if ($ultimaMovimentacao->tipo === 'emprestimo') {
                    $usuarioAntigo = \App\Models\AtivoUsuario::find($ultimaMovimentacao->usuario_id);
                    if ($usuarioAntigo && !empty($usuarioAntigo->telefone)) {
                        $nomeUsuario = explode(' ', trim($usuarioAntigo->nome))[0];
                        $descricaoEquip = $equipamento->descricao . ' (' . $equipamento->identificador . ')';
                        $dataDevolucao = now()->format('d/m/Y');
                        $horaDevolucao = now()->format('H:i');

                        dispatch(new \App\Jobs\SendKwikNotificationJob(
                            $usuarioAntigo->telefone,
                            'equipamento_devolucao',
                            [$nomeUsuario, $descricaoEquip, $dataDevolucao, $horaDevolucao]
                        ));
                    }
                } elseif ($ultimaMovimentacao->tipo === 'cessao') {
                    // Trigger PDF return term for cession
                    $cessao_pdf_id = $novaMovimentacao->id;
                }
            }

            return redirect()->back()
                ->with('success', $msg)
                ->with('cessao_id', $cessaoId)
                ->with('devolucao_id', $cessao_pdf_id)
                ->with('mov_tipo', $validated['tipo'])
                ->with('mov_equipamento_id', $equipamento->id);
        });
    }

    public function pdfDevolucao(string $id)
    {
        $movimentacao = \App\Models\AtivoMovimentacao::with(['equipamento.fabricante', 'cessao.usuario.empresa', 'responsavel'])
            ->findOrFail($id);

        if ($movimentacao->tipo !== 'devolucao') {
            return redirect()->back()->with('error', 'Este registro não é uma devolução.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('ativos.equipamentos.pdf_devolucao', compact('movimentacao'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("termo_devolucao_equipamento_{$movimentacao->equipamento->identificador}.pdf");
    }
}
