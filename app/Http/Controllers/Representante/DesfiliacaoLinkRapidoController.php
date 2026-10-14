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

        $link = DesfiliacaoLink::create([
            'token' => DesfiliacaoLink::gerarToken(),
            'user_id' => auth()->id(),
            'destinatario_nome' => $validated['destinatario_nome'] ?? null,
            'destinatario_contato' => $validated['destinatario_contato'] ?? null,
            'canal_envio' => 'whatsapp',
            'horas_validade' => $horas,
            'expira_em' => now()->addHours($horas),
        ]);

        return redirect()->route('representante.desfiliacao.links')
            ->with('status', 'Link gerado com sucesso!')
            ->with('whatsapp_url', $link->whatsapp_url)
            ->with('ultimo_link_id', $link->id);
    }
}
