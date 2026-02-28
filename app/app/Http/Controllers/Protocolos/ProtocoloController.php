<?php

namespace App\Http\Controllers\Protocolos;

use App\Domain\Protocolos\Services\ProtocoloDispatcher;
use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Protocolo;
use App\Models\ProtocoloDestinatario;
use App\Models\TipoProtocolo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProtocoloController extends Controller
{
    public function __construct(
        private readonly ProtocoloDispatcher $dispatcher
    ) {
    }

    public function index(Request $request): View
    {
        $mes = $request->input('mes', \Carbon\Carbon::now()->month);
        $ano = $request->input('ano', \Carbon\Carbon::now()->year);
        $status = $request->input('status_envio', '');
        $termo = $request->input('termo', '');

        $query = Protocolo::with(['empresa', 'tipo', 'destinatarios', 'usuario']);

        if ($termo) {
            $query->where(function ($q) use ($termo) {
                // Main table
                $q->where('referencia_documento', 'like', "%{$termo}%")
                    ->orWhere('assunto', 'like', "%{$termo}%");

                // Relating Empresa
                $q->orWhereHas('empresa', function ($qEmp) use ($termo) {
                    $qEmp->where('razao_social', 'like', "%{$termo}%")
                        ->orWhere('nome_fantasia', 'like', "%{$termo}%")
                        ->orWhere('nome_curto', 'like', "%{$termo}%");
                });

                // Relating Destinatarios
                $q->orWhereHas('destinatarios', function ($qDest) use ($termo) {
                    $qDest->where('email', 'like', "%{$termo}%")
                        ->orWhere('nome', 'like', "%{$termo}%");
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

        $protocolos = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('protocolos.index', compact('protocolos', 'mes', 'ano', 'status', 'termo'));
    }

    public function create(): View
    {
        $empresas = Empresa::orderBy('razao_social')->get();
        $tiposProtocolo = TipoProtocolo::where('ativo', true)->orderBy('nome')->get();

        return view('protocolos.create', compact('empresas', 'tiposProtocolo'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tipo_protocolo_id' => ['nullable', 'exists:tipo_protocolos,id'],
            'empresa_id' => ['nullable', 'exists:empresas,id'],
            'referencia_documento' => ['nullable', 'string', 'max:100'],
            'assunto' => ['required', 'string', 'max:255'],
            'corpo' => ['required', 'string'],
            'destinatarios' => ['required', 'array', 'min:1'],
            'destinatarios.*.nome' => ['required', 'string', 'max:255'],
            'destinatarios.*.email' => ['required', 'email'],
            'destinatarios.*.telefone' => ['nullable', 'string', 'max:20'],
            'destinatarios.*.cpf_cnpj' => ['nullable', 'string', 'max:20'],
            'destinatarios.*.endereco' => ['nullable', 'array'],
            'anexos' => ['nullable', 'array', 'max:5'], // Max 5 arquivos
            'anexos.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg'], // Max 20MB por arquivo
        ]);

        DB::beginTransaction();

        try {
            $protocolo = Protocolo::create([
                'tipo_protocolo_id' => $data['tipo_protocolo_id'] ?? null,
                'user_id' => Auth::id(),
                'empresa_id' => $data['empresa_id'] ?? null,
                'assunto' => strtoupper($data['assunto']),
                'corpo' => $data['corpo'],
                'canal' => 'email',
                'status' => 'pendente',
                'referencia_documento' => $data['referencia_documento'] ?? null,
            ]);

            foreach ($data['destinatarios'] as $dest) {
                ProtocoloDestinatario::create([
                    'protocolo_id' => $protocolo->id,
                    'nome' => strtoupper($dest['nome']),
                    'email' => strtolower(trim($dest['email'])),
                    'telefone' => $dest['telefone'] ?? null,
                    'cpf_cnpj' => $dest['cpf_cnpj'] ?? null,
                    'endereco' => $dest['endereco'] ?? null,
                ]);
            }

            // Tratamento de anexos
            if ($request->hasFile('anexos')) {
                foreach ($request->file('anexos') as $file) {
                    $path = $file->store('protocolos/anexos', 'local');

                    \App\Models\ProtocoloAnexo::create([
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
                ->withErrors(['geral' => 'Falha ao enviar protocolo: ' . $e->getMessage()]);
        }

        return redirect()->route('protocolos.index')
            ->with('success', 'Protocolo enviado com sucesso para todos os destinatários.');
    }

    public function show(Protocolo $protocolo): View
    {
        $protocolo->load([
            'tipo',
            'empresa',
            'usuario',
            'destinatarios.envios',
            'comprovante',
        ]);

        return view('protocolos.show', compact('protocolo'));
    }

    public function syncStatus(Protocolo $protocolo): RedirectResponse
    {
        /** @var \App\Domain\Protocolos\Services\ArOnlineHttpClient $client */
        $client = app(\App\Domain\Protocolos\Contracts\ArOnlineClient::class);

        // Usa o token do criador do documento ou, fallback, do usuário logado
        $token = $protocolo->usuario?->tokenDepto?->token ?? Auth::user()?->tokenDepto?->token ?? config('services.ar_online.token');
        $client->setToken($token);

        $atualizados = 0;

        foreach ($protocolo->envios as $envio) {
            if (!$envio->id_email_externo || in_array($envio->status, ['concluido', 'falha'])) {
                continue;
            }

            try {
                $statusData = $client->getFullStatus($envio->id_email_externo);

                // Prioridade de status: lido > entregue > enviado > falha
                $prioridades = ['lido' => 4, 'entregue' => 3, 'enviado' => 2, 'falha' => 1, 'processado' => 0];
                $statusAtualPeso = $prioridades[$envio->status] ?? 0;
                $novoStatus = $envio->status;
                $dataEntrega = $envio->entregue_em;
                $dataLeitura = $envio->lido_em;

                $statusFull = $statusData['statusFull'] ?? [];

                foreach (['email', 'whatsapp', 'sms'] as $canal) {
                    if (!isset($statusFull[$canal]) || !is_array($statusFull[$canal])) {
                        continue;
                    }

                    foreach ($statusFull[$canal] as $statusItem) {
                        $label = strtolower($statusItem['label'] ?? '');
                        $dateTime = $statusItem['dateTime'] ?? null;
                        if (!$dateTime) {
                            continue;
                        }

                        // Converte dd/mm/yyyy hh:mm:ss para formato MySQL
                        $parsedDate = \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $dateTime)->format('Y-m-d H:i:s');

                        $canalStatus = match (true) {
                            str_contains($label, 'lido') || str_contains($label, 'visualizado') => 'lido',
                            str_contains($label, 'entregue') => 'entregue',
                            str_contains($label, 'enviado') => 'enviado',
                            str_contains($label, 'falha') => 'falha',
                            default => 'processado'
                        };

                        $pesoCanal = $prioridades[$canalStatus] ?? 0;

                        if ($pesoCanal > $statusAtualPeso) {
                            $novoStatus = $canalStatus;
                            $statusAtualPeso = $pesoCanal;
                        }

                        if ($canalStatus === 'entregue' && !$dataEntrega) {
                            $dataEntrega = $parsedDate;
                        }
                        if ($canalStatus === 'lido' && !$dataLeitura) {
                            $dataLeitura = $parsedDate;
                            // Se foi lido, obrigatoriamente foi entregue antes
                            if (!$dataEntrega) {
                                $dataEntrega = clone $parsedDate; // usa a mesma data no pior caso
                            }
                        }
                    }
                }

                $envio->update([
                    'status' => $novoStatus,
                    'ultima_resposta' => json_encode($statusData),
                    'entregue_em' => $dataEntrega,
                    'lido_em' => $dataLeitura,
                ]);

                $atualizados++;
            } catch (\Throwable $e) {
                // Silencia falhas individuais de consulta
            }
        }

        return back()->with('success', "Status atualizado: {$atualizados} envio(s) sincronizados.");
    }

    public function baixarComprovante(Protocolo $protocolo, \App\Models\ProtocoloEnvio $envio)
    {
        if (!$envio->id_email_externo) {
            return back()->with('error', 'Este envio ainda não possui ID externo para consulta.');
        }

        /** @var \App\Domain\Protocolos\Services\ArOnlineHttpClient $client */
        $client = app(\App\Domain\Protocolos\Contracts\ArOnlineClient::class);
        $token = $protocolo->usuario?->tokenDepto?->token ?? Auth::user()?->tokenDepto?->token ?? config('services.ar_online.token');
        $client->setToken($token);

        $base64 = $client->getReceiptBase64($envio->id_email_externo);

        if (!$base64) {
            return back()->with('error', 'Comprovante ainda não disponível ou a API não retornou o PDF base64.');
        }

        return response(base64_decode($base64), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="comprovante.pdf"',
        ]);
    }

    public function baixarLaudo(Protocolo $protocolo, \App\Models\ProtocoloEnvio $envio)
    {
        if (!$envio->id_email_externo) {
            return back()->with('error', 'Este envio ainda não possui ID externo para consulta.');
        }

        /** @var \App\Domain\Protocolos\Services\ArOnlineHttpClient $client */
        $client = app(\App\Domain\Protocolos\Contracts\ArOnlineClient::class);
        $token = $protocolo->usuario?->tokenDepto?->token ?? Auth::user()?->tokenDepto?->token ?? config('services.ar_online.token');
        $client->setToken($token);

        $pdf = $client->getLaudoPdf($envio->id_email_externo);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="laudo.pdf"',
        ]);
    }
}
