<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Whatsapp\Services\KwikTemplateSyncService;
use App\Http\Requests\WhatsappTemplateRequest;
use App\Models\WhatsappLote;
use App\Models\WhatsappTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsappTemplateController extends Controller
{
    /**
     * Sincroniza os templates de WhatsApp homologados na Meta a partir da API Kwik.
     */
    public function sincronizar(KwikTemplateSyncService $syncService): RedirectResponse
    {
        $resultado = $syncService->sincronizar(auth()->id());

        if (!empty($resultado->erros)) {
            return redirect()->route('whatsapp-templates.index')
                ->with('error', 'Falha na sincronização: ' . implode('; ', $resultado->erros));
        }

        return redirect()->route('whatsapp-templates.index')
            ->with('success', $resultado->resumoMensagem());
    }
    /**
     * Listagem dos templates de WhatsApp cadastrados.
     */
    public function index(Request $request): View
    {
        $query = WhatsappTemplate::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('busca')) {
            $busca = $request->input('busca');
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('descricao', 'like', "%{$busca}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('ativo', $request->input('status') === 'ativo');
        }

        $templates = $query->paginate(15)->appends($request->all());

        return view('whatsapp_templates.index', compact('templates'));
    }

    /**
     * Cadastro de novo template.
     */
    public function store(WhatsappTemplateRequest $request): RedirectResponse
    {
        $dados = $request->validated();
        $dados['user_id'] = auth()->id();
        $dados['ativo'] = $request->has('ativo') ? (bool) $request->input('ativo') : true;

        if (is_string($dados['parametros_esperados'] ?? null)) {
            $linhas = array_filter(array_map('trim', explode(',', $dados['parametros_esperados'])));
            $dados['parametros_esperados'] = array_values($linhas);
        }

        WhatsappTemplate::create($dados);

        return redirect()->route('whatsapp-templates.index')
            ->with('success', 'Template de WhatsApp cadastrado com sucesso!');
    }

    /**
     * Atualização de template existente.
     */
    public function update(WhatsappTemplateRequest $request, WhatsappTemplate $whatsappTemplate): RedirectResponse
    {
        $dados = $request->validated();
        $dados['ativo'] = $request->has('ativo') ? (bool) $request->input('ativo') : false;

        if (is_string($dados['parametros_esperados'] ?? null)) {
            $linhas = array_filter(array_map('trim', explode(',', $dados['parametros_esperados'])));
            $dados['parametros_esperados'] = array_values($linhas);
        }

        $whatsappTemplate->update($dados);

        return redirect()->route('whatsapp-templates.index')
            ->with('success', 'Template de WhatsApp atualizado com sucesso!');
    }

    /**
     * Inativação ou exclusão de template.
     */
    public function destroy(WhatsappTemplate $whatsappTemplate): RedirectResponse
    {
        // Se já tiver lotes associados, inativa para manter integridade
        if ($whatsappTemplate->lotes()->exists()) {
            $whatsappTemplate->update(['ativo' => false]);
            return redirect()->route('whatsapp-templates.index')
                ->with('info', 'Template inativado com sucesso (não pode ser excluído pois possui disparos registrados).');
        }

        $whatsappTemplate->delete();

        return redirect()->route('whatsapp-templates.index')
            ->with('success', 'Template excluído com sucesso!');
    }

    /**
     * Retorna a lista de templates ativos em JSON para carregar nas modais.
     */
    public function ativos(): JsonResponse
    {
        $templates = WhatsappTemplate::ativos()
            ->orderBy('nome')
            ->get(['id', 'nome', 'descricao', 'corpo_exemplo', 'parametros_esperados']);

        return response()->json($templates);
    }

    /**
     * Consulta status e progresso de um lote específico.
     */
    public function statusLote(int $id): JsonResponse
    {
        $lote = WhatsappLote::with('template')->findOrFail($id);

        return response()->json([
            'lote_id' => $lote->id,
            'modulo' => $lote->modulo,
            'status' => $lote->status,
            'total_destinatarios' => $lote->total_destinatarios,
            'total_enviados' => $lote->total_enviados,
            'total_falhas' => $lote->total_falhas,
            'percentual' => $lote->percentualConcluido(),
            'template_nome' => $lote->template?->nome,
            'iniciado_em' => $lote->iniciado_em?->format('d/m/Y H:i:s'),
            'concluido_em' => $lote->concluido_em?->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Listagem do histórico de lotes de WhatsApp.
     */
    public function lotes(Request $request): View
    {
        $query = WhatsappLote::with(['template', 'user'])->orderBy('created_at', 'desc');

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $lotes = $query->paginate(15)->appends($request->all());

        return view('whatsapp_templates.lotes', compact('lotes'));
    }
}
