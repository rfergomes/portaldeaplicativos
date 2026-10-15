<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GerarDesfiliacaoLinkRequest;
use App\Models\DesfiliacaoLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesfiliacaoLinkAdminController extends Controller
{
    /**
     * Listagem de links gerados com filtros.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $busca = $request->query('busca');

        $query = DesfiliacaoLink::with(['user', 'desfiliacao'])->latest();

        if ($status === 'disponivel') {
            $query->validos();
        } elseif ($status === 'utilizado') {
            $query->where('utilizado', true);
        } elseif ($status === 'expirado') {
            $query->expirados();
        } elseif ($status === 'cancelado') {
            $query->whereNotNull('cancelado_em');
        }

        if ($busca) {
            $query->where(function ($q) use ($busca) {
                $q->where('destinatario_nome', 'like', "%{$busca}%")
                    ->orWhere('destinatario_contato', 'like', "%{$busca}%")
                    ->orWhere('token', 'like', "%{$busca}%");
            });
        }

        $links = $query->paginate(20)->withQueryString();

        return view('desfiliacoes.links.index', compact('links', 'status', 'busca'));
    }

    /**
     * Geração de um ou múltiplos links exclusivos.
     */
    public function store(GerarDesfiliacaoLinkRequest $request)
    {
        $validated = $request->validated();
        $horas = (int) ($validated['horas_validade'] ?? 48);
        $tipoLink = $validated['tipo_link'] ?? 'individual';
        $limiteUsos = (int) ($validated['limite_usos'] ?? 1);
        $quantidade = (int) ($validated['quantidade'] ?? 1);

        if ($tipoLink === 'compartilhado' || $limiteUsos > 1) {
            $tipoLink = 'compartilhado';
            $limiteUsos = max(2, $limiteUsos);
            $quantidade = 1;
        } elseif ($quantidade > 1 || $tipoLink === 'lote') {
            $tipoLink = 'lote';
            $limiteUsos = 1;
        } else {
            $tipoLink = 'individual';
            $limiteUsos = 1;
            $quantidade = 1;
        }

        $expiraEm = now()->addHours($horas);
        $linksGerados = [];

        for ($i = 0; $i < $quantidade; $i++) {
            $link = DesfiliacaoLink::create([
                'token' => DesfiliacaoLink::gerarToken(),
                'user_id' => auth()->id(),
                'tipo_link' => $tipoLink,
                'limite_usos' => $limiteUsos,
                'usos_atuais' => 0,
                'destinatario_nome' => $quantidade === 1 ? ($validated['destinatario_nome'] ?? null) : null,
                'destinatario_contato' => $quantidade === 1 ? ($validated['destinatario_contato'] ?? null) : null,
                'canal_envio' => $validated['canal_envio'] ?? 'manual',
                'horas_validade' => $horas,
                'expira_em' => $expiraEm,
            ]);

            // Disparo automático via KWIK caso o canal selecionado seja KWIK Oficial
            if ($link->canal_envio === 'kwik' && !empty($link->destinatario_contato)) {
                try {
                    $nome = $link->destinatario_nome ?: 'Trabalhador(a)';
                    $url = $link->url;
                    $validade = $link->expira_em->format('d/m/Y \à\s H:i');

                    dispatch(new \App\Jobs\SendKwikNotificationJob(
                        $link->destinatario_contato,
                        'desfiliacao_link_exclusivo',
                        [$nome, $url, $validade],
                        auth()->id()
                    ));
                    \Illuminate\Support\Facades\Log::info("KWIK: Disparo do link de desfiliação #{$link->id} enviado para {$link->destinatario_contato}");
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("KWIK: Falha ao despachar link #{$link->id} para {$link->destinatario_contato}: " . $e->getMessage());
                }
            }

            $linksGerados[] = $link;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Link(s) exclusivo(s) gerado(s) com sucesso.',
                'links' => collect($linksGerados)->map(fn ($l) => [
                    'id' => $l->id,
                    'token' => $l->token,
                    'tipo_link' => $l->tipo_link,
                    'limite_usos' => $l->limite_usos,
                    'usos_atuais' => $l->usos_atuais,
                    'vagas_restantes' => $l->vagas_restantes,
                    'url' => $l->url,
                    'whatsapp_url' => $l->whatsapp_url,
                    'expira_em' => $l->expira_em->format('d/m/Y H:i'),
                ]),
            ]);
        }

        return redirect()->route('admin.desfiliacao.links.index')
            ->with('status', 'Link exclusivo gerado com sucesso!')
            ->with('ultimo_link_id', $linksGerados[0]->id);
    }

    /**
     * Cancelar manualmente um link ainda não utilizado.
     */
    public function cancelar(int $id): RedirectResponse
    {
        $link = DesfiliacaoLink::findOrFail($id);

        if ($link->utilizado) {
            return back()->with('error', 'Este link já foi utilizado e não pode ser cancelado.');
        }

        $link->update(['cancelado_em' => now()]);

        return back()->with('status', 'Link cancelado com sucesso.');
    }
}
