<?php

namespace App\Http\Controllers\Protocolos;

use App\Domain\Protocolos\Contracts\ArOnlineClient;
use App\Domain\Protocolos\DTOs\ArOnlineSendPayload;
use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Protocolo;
use App\Models\ProtocoloDestinatario;
use App\Models\ProtocoloEnvio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProtocoloController extends Controller
{
    public function index(): View
    {
        $protocolos = Protocolo::with('empresa')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('protocolos.index', [
            'protocolos' => $protocolos,
        ]);
    }

    public function create(): View
    {
        $empresas = Empresa::orderBy('razao_social')->get();

        return view('protocolos.create', [
            'empresas' => $empresas,
        ]);
    }

    public function store(Request $request, ArOnlineClient $client): RedirectResponse
    {
        $data = $request->validate([
            'empresa_id' => ['nullable', 'exists:empresas,id'],
            'assunto' => ['required', 'string', 'max:255'],
            'corpo' => ['required', 'string'],
            'destinatarios.*.nome' => ['required', 'string', 'max:255'],
            'destinatarios.*.email' => ['required', 'email'],
        ]);

        DB::beginTransaction();

        try {
            $protocolo = Protocolo::create([
                'empresa_id' => $data['empresa_id'] ?? null,
                'assunto' => $data['assunto'],
                'corpo' => $data['corpo'],
                'canal' => 'email',
                'status' => 'pendente',
            ]);

            foreach ($data['destinatarios'] as $destinatario) {
                ProtocoloDestinatario::create([
                    'protocolo_id' => $protocolo->id,
                    'nome' => $destinatario['nome'],
                    'email' => $destinatario['email'],
                ]);
            }

            // Para o MVP, enviaremos apenas para o primeiro destinatário.
            $destinoPrincipal = $protocolo->destinatarios()->first();

            $payload = new ArOnlineSendPayload(
                nameTo: $destinoPrincipal->nome,
                subject: $protocolo->assunto,
                contentHtml: $protocolo->corpo,
                emailTo: $destinoPrincipal->email,
                attachments: null,
                customId: (string) $protocolo->id,
            );

            $idEmail = $client->send($payload);

            ProtocoloEnvio::create([
                'protocolo_id' => $protocolo->id,
                'id_email_externo' => $idEmail,
                'status' => 'enviado',
            ]);

            $protocolo->update(['status' => 'enviado']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Falha ao enviar protocolo: ' . $e->getMessage()]);
        }

        return redirect()->route('protocolos.index')
            ->with('status', 'Protocolo enviado com sucesso.');
    }
}

