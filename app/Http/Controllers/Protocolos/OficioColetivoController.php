<?php

namespace App\Http\Controllers\Protocolos;

use App\Domain\Protocolos\Services\ProtocoloDispatcher;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Protocolo;
use App\Models\ProtocoloAnexo;
use App\Models\ProtocoloDestinatario;
use App\Models\Regiao;
use App\Models\TipoProtocolo;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OficioColetivoController extends Controller
{
    public function __construct(
        private readonly ProtocoloDispatcher $dispatcher
    ) {
    }

    public function index(Request $request): View
    {
        $mes = $request->input('mes', Carbon::now()->month);
        $ano = $request->input('ano', Carbon::now()->year);
        $status = $request->input('status_envio', '');
        $termo = $request->input('termo', '');

        $query = Protocolo::with(['tipo', 'destinatarios.empresa', 'usuario'])
            ->where('tipo_escopo', 'coletivo');

        if ($termo) {
            $query->where(function ($q) use ($termo) {
                $q->where('referencia_documento', 'like', "%{$termo}%")
                    ->orWhere('assunto', 'like', "%{$termo}%")
                    ->orWhereHas('destinatarios', function ($qDest) use ($termo) {
                        $qDest->where('email', 'like', "%{$termo}%")
                            ->orWhere('nome', 'like', "%{$termo}%")
                            ->orWhereHas('empresa', function ($qEmp) use ($termo) {
                                $qEmp->where('razao_social', 'like', "%{$termo}%")
                                    ->orWhere('nome_fantasia', 'like', "%{$termo}%");
                            });
                    });
            });
        }

        if ($mes) {
            $query->whereMonth('created_at', $mes);
        }

        if ($ano) {
            $query->whereYear('created_at', $ano);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $oficios = $query->orderByDesc('created_at')->paginate(20)->appends($request->all());

        // Métricas
        $metricsQuery = Protocolo::where('tipo_escopo', 'coletivo');
        if ($mes) $metricsQuery->whereMonth('created_at', $mes);
        if ($ano) $metricsQuery->whereYear('created_at', $ano);

        $metrics = $metricsQuery->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $totalGeral = array_sum($metrics);
        $totalSucesso = ($metrics['sucesso'] ?? 0) + ($metrics['lido'] ?? 0) + ($metrics['entregue'] ?? 0);
        $totalEnviados = ($metrics['enviado'] ?? 0) + ($metrics['queued'] ?? 0) + ($metrics['pendente'] ?? 0);
        $totalFalhas = $metrics['falha'] ?? 0;

        return view('protocolos.oficios.index', compact(
            'oficios',
            'mes',
            'ano',
            'status',
            'termo',
            'totalGeral',
            'totalSucesso',
            'totalEnviados',
            'totalFalhas'
        ));
    }

    public function create(): View
    {
        $regioes = Regiao::orderBy('nome')->get();
        $categorias = Empresa::whereNotNull('categoria')
            ->where('categoria', '!=', '')
            ->distinct()
            ->pluck('categoria');

        $tiposProtocolo = TipoProtocolo::where('ativo', true)->orderBy('nome')->get();

        return view('protocolos.oficios.create', compact('regioes', 'categorias', 'tiposProtocolo'));
    }

    public function getEmpresasFiltradas(Request $request): JsonResponse
    {
        $regiaoId = $request->input('regiao_id');
        $categoria = $request->input('categoria');
        $apenasAtivas = $request->boolean('apenas_ativas', true);
        $termo = $request->input('termo');

        $query = Empresa::with(['regiao', 'clientes' => function ($qClient) {
            $qClient->whereNotNull('email')->where('email', '!=', '');
        }]);

        if ($apenasAtivas) {
            $query->where('ativo', true);
        }

        if ($regiaoId !== null && $regiaoId !== '') {
            if ($regiaoId === 'sem_regiao' || $regiaoId === '0') {
                $query->where(function ($q) {
                    $q->whereNull('regiao_id')->orWhere('regiao_id', 0);
                });
            } else {
                $query->where('regiao_id', $regiaoId);
            }
        }

        if ($categoria) {
            $query->where('categoria', $categoria);
        }

        if ($termo) {
            $query->where(function ($q) use ($termo) {
                $q->where('razao_social', 'like', "%{$termo}%")
                    ->orWhere('nome_fantasia', 'like', "%{$termo}%")
                    ->orWhere('nome_curto', 'like', "%{$termo}%")
                    ->orWhere('cnpj', 'like', "%{$termo}%");
            });
        }

        $empresas = $query->orderBy('razao_social')->get();

        $totalEmpresas = $empresas->count();
        $totalContatos = 0;

        $formatted = $empresas->map(function ($empresa) use (&$totalContatos) {
            $contatos = $empresa->clientes->map(function ($c) {
                return [
                    'id' => $c->id,
                    'nome' => $c->nome,
                    'email' => $c->email,
                    'telefone' => $c->telefone,
                    'ativo' => (bool)$c->ativo,
                    'email_valido' => (bool)$c->email_valido,
                ];
            });

            $totalContatos += $contatos->count();

            return [
                'id' => $empresa->id,
                'razao_social' => $empresa->razao_social,
                'nome_fantasia' => $empresa->nome_fantasia,
                'cnpj' => $empresa->cnpj,
                'cidade' => $empresa->cidade,
                'estado' => $empresa->estado,
                'regiao' => $empresa->regiao?->nome,
                'contatos' => $contatos,
            ];
        });

        return response()->json([
            'total_empresas' => $totalEmpresas,
            'total_contatos' => $totalContatos,
            'empresas' => $formatted,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tipo_protocolo_id' => ['nullable', 'exists:tipo_protocolos,id'],
            'referencia_documento' => ['nullable', 'string', 'max:100'],
            'assunto' => ['required', 'string', 'max:255'],
            'corpo' => ['required', 'string'],
            'contatos' => ['required', 'array', 'min:1'], // IDs de Clientes selecionados
            'anexos' => ['nullable', 'array', 'max:5'],
            'anexos.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg'],
        ]);

        $clientes = Cliente::with('empresa')->whereIn('id', $data['contatos'])->get();

        if ($clientes->isEmpty()) {
            return back()->withInput()->withErrors(['contatos' => 'Nenhum contato válido selecionado.']);
        }

        DB::beginTransaction();

        try {
            $protocolo = Protocolo::create([
                'tipo_protocolo_id' => $data['tipo_protocolo_id'] ?? null,
                'user_id' => Auth::id(),
                'empresa_id' => null, // Coletivo
                'assunto' => strtoupper($data['assunto']),
                'corpo' => $data['corpo'],
                'canal' => 'email',
                'tipo_escopo' => 'coletivo',
                'status' => 'pendente',
                'referencia_documento' => $data['referencia_documento'] ?? null,
            ]);

            foreach ($clientes as $cliente) {
                ProtocoloDestinatario::create([
                    'protocolo_id' => $protocolo->id,
                    'empresa_id' => $cliente->empresa_id,
                    'cliente_id' => $cliente->id,
                    'nome' => strtoupper($cliente->nome),
                    'email' => strtolower(trim($cliente->email)),
                    'telefone' => $cliente->telefone ?? null,
                    'cpf_cnpj' => $cliente->documento ?? null,
                ]);
            }

            // Tratamento de anexos
            if ($request->hasFile('anexos')) {
                foreach ($request->file('anexos') as $file) {
                    $path = $file->store('protocolos/anexos', 'local');

                    ProtocoloAnexo::create([
                        'protocolo_id' => $protocolo->id,
                        'nome_original' => $file->getClientOriginalName(),
                        'caminho_armazenado' => $path,
                        'tamanho_bytes' => $file->getSize(),
                        'hash' => hash_file('sha256', $file->getRealPath()),
                    ]);
                }
            }

            $protocolo->load(['destinatarios', 'anexos']);

            $this->dispatcher->dispatch($protocolo);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Falha ao disparar Ofício Coletivo: ' . $e->getMessage()]);
        }

        return redirect()->route('protocolos.oficios.show', $protocolo->id)
            ->with('success', "Ofício Coletivo disparado com sucesso para {$clientes->count()} destinatários.");
    }

    public function show(Protocolo $protocolo): View
    {
        if ($protocolo->tipo_escopo !== 'coletivo') {
            return redirect()->route('protocolos.show', $protocolo->id);
        }

        $protocolo->load([
            'tipo',
            'usuario',
            'anexos',
            'destinatarios.empresa',
            'destinatarios.envios',
        ]);

        $regioes = Regiao::orderBy('nome')->get();
        $categorias = Empresa::whereNotNull('categoria')
            ->where('categoria', '!=', '')
            ->distinct()
            ->pluck('categoria');

        // Contatos que já receberam este protocolo
        $jaNotificadosClienteIds = $protocolo->destinatarios->pluck('cliente_id')->filter()->toArray();

        return view('protocolos.oficios.show', compact('protocolo', 'regioes', 'categorias', 'jaNotificadosClienteIds'));
    }

    public function dispararLote(Request $request, Protocolo $protocolo): RedirectResponse
    {
        if ($protocolo->tipo_escopo !== 'coletivo') {
            return back()->with('error', 'Apenas ofícios coletivos permitem disparos em lote.');
        }

        $data = $request->validate([
            'novos_contatos' => ['required', 'array', 'min:1'],
        ]);

        // Filtrar apenas contatos que ainda não foram notificados neste protocolo
        $jaNotificados = $protocolo->destinatarios->pluck('cliente_id')->filter()->toArray();
        $novosIds = array_diff($data['novos_contatos'], $jaNotificados);

        if (empty($novosIds)) {
            return back()->with('error', 'Todos os contatos selecionados já foram notificados anteriormente neste Ofício.');
        }

        $clientes = Cliente::with('empresa')->whereIn('id', $novosIds)->get();

        DB::beginTransaction();

        try {
            $novosDestinatarios = [];
            foreach ($clientes as $cliente) {
                $dest = ProtocoloDestinatario::create([
                    'protocolo_id' => $protocolo->id,
                    'empresa_id' => $cliente->empresa_id,
                    'cliente_id' => $cliente->id,
                    'nome' => strtoupper($cliente->nome),
                    'email' => strtolower(trim($cliente->email)),
                    'telefone' => $cliente->telefone ?? null,
                    'cpf_cnpj' => $cliente->documento ?? null,
                ]);
                $novosDestinatarios[] = $dest;
            }

            // Recarrega destinatários e anexos para o dispatcher
            $protocolo->load(['anexos']);
            $protocolo->setRelation('destinatarios', collect($novosDestinatarios));

            $this->dispatcher->dispatch($protocolo);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Falha ao disparar novo lote do Ofício: ' . $e->getMessage());
        }

        return back()->with('success', "Novo lote disparado com sucesso para " . count($novosDestinatarios) . " destinatário(s).");
    }
}
