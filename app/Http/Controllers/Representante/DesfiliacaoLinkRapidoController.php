<?php

declare(strict_types=1);

namespace App\Http\Controllers\Representante;

use App\Http\Controllers\Controller;
use App\Http\Requests\GerarDesfiliacaoLinkRequest;
use App\Models\DesfiliacaoLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesfiliacaoLinkRapidoController extends Controller
{
    /**
     * Interface rápida mobile-first para o representante gerar e ver seus próprios links.
     */
    public function index(Request $request): View
    {
        $userId = auth()->id();

        $links = DesfiliacaoLink::where('user_id', $userId)
            ->with('desfiliacao')
            ->latest()
            ->paginate(15);

        return view('representante.desfiliacoes.links-rapido', compact('links'));
    }

    /**
     * Emissão rápida de link exclusivo.
     */
    public function store(GerarDesfiliacaoLinkRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $horas = (int) ($validated['horas_validade'] ?? 48);
        $tipoLink = $validated['tipo_link'] ?? 'individual';
        $limiteUsos = (int) ($validated['limite_usos'] ?? 1);
        $quantidade = (int) ($validated['quantidade'] ?? 1);
        $expiraEm = now()->addHours($horas);

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

        $linksGerados = [];

        for ($i = 0; $i < $quantidade; $i++) {
            $linksGerados[] = DesfiliacaoLink::create([
                'token' => DesfiliacaoLink::gerarToken(),
                'user_id' => auth()->id(),
                'tipo_link' => $tipoLink,
                'limite_usos' => $limiteUsos,
                'usos_atuais' => 0,
                'destinatario_nome' => $quantidade === 1 ? ($validated['destinatario_nome'] ?? null) : null,
                'destinatario_contato' => $quantidade === 1 ? ($validated['destinatario_contato'] ?? null) : null,
                'canal_envio' => 'whatsapp',
                'horas_validade' => $horas,
                'expira_em' => $expiraEm,
            ]);
        }

        $primeiroLink = $linksGerados[0];

        $redirect = redirect()->route('representante.desfiliacao.links')
            ->with('status', $quantidade > 1 
                ? "Lote de {$quantidade} links exclusivos gerado com sucesso!" 
                : ($tipoLink === 'compartilhado' 
                    ? "Link compartilhado (válido para até {$limiteUsos} usos) gerado com sucesso!" 
                    : 'Link gerado com sucesso!'))
            ->with('whatsapp_url', $primeiroLink->whatsapp_url)
            ->with('ultimo_link_id', $primeiroLink->id);

        if ($quantidade > 1) {
            $redirect->with('links_lote', collect($linksGerados)->map(fn ($l) => [
                'token' => $l->token,
                'url' => $l->url,
            ])->toArray());
        }

        return $redirect;
    }
}
