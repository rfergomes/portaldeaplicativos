<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\SocioCaixaOcorrencia;
use App\Models\SocioFolhaHistorico;
use App\Models\WhatsappLote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessarWhatsappLoteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $loteId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $loteId)
    {
        $this->loteId = $loteId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $lote = WhatsappLote::with(['template', 'itens'])->find($this->loteId);
        if (!$lote || !$lote->template) {
            Log::error("ProcessarWhatsappLoteJob: Lote #{$this->loteId} ou template não encontrado.");
            return;
        }

        $lote->update([
            'status' => 'processando',
            'iniciado_em' => now(),
        ]);

        $template = $lote->template;
        $itens = $lote->itens()->where('status', 'pendente')->get();

        $enviados = 0;
        $falhas = 0;

        foreach ($itens as $item) {
            try {
                $parametrosExtras = is_array($lote->parametros_extras) ? $lote->parametros_extras : [];
                $bodyArgs = array_merge([$item->destinatario_nome], $parametrosExtras);

                // Despacha o envio individual via SendKwikNotificationJob
                SendKwikNotificationJob::dispatch(
                    $item->telefone,
                    $template->nome,
                    $bodyArgs,
                    $lote->user_id
                );

                $item->update([
                    'status' => 'enviado',
                    'enviado_em' => now(),
                ]);

                // REGISTRO OBRIGATÓRIO NO HISTÓRICO DO ASSOCIADO
                if ($lote->modulo === 'caixa' && !empty($item->matricula)) {
                    SocioCaixaOcorrencia::create([
                        'matricula' => $item->matricula,
                        'user_id' => $lote->user_id,
                        'mensagem' => "[WHATSAPP LOTE] Template '{$template->nome}' enviado para {$item->telefone}. Lote #{$lote->id}",
                    ]);
                } elseif ($lote->modulo === 'folha' && !empty($item->socio_folha_id)) {
                    SocioFolhaHistorico::create([
                        'socio_folha_id' => $item->socio_folha_id,
                        'user_id' => $lote->user_id,
                        'acao' => 'whatsapp_lote',
                        'descricao' => "Mensagem de WhatsApp em lote (Template '{$template->nome}') enviada para {$item->destinatario_nome} ({$item->telefone}). Lote #{$lote->id}",
                    ]);
                }

                $enviados++;
            } catch (\Throwable $e) {
                $falhas++;
                $item->update([
                    'status' => 'falha',
                    'motivo_falha' => $e->getMessage(),
                ]);
                Log::error("ProcessarWhatsappLoteJob erro no item #{$item->id}: " . $e->getMessage());
            }
        }

        $statusFinal = 'concluido';
        if ($falhas > 0 && $enviados === 0) {
            $statusFinal = 'cancelado';
        } elseif ($falhas > 0) {
            $statusFinal = 'erro_parcial';
        }

        $lote->update([
            'total_enviados' => $enviados,
            'total_falhas' => $falhas,
            'status' => $statusFinal,
            'concluido_em' => now(),
        ]);

        Log::info("ProcessarWhatsappLoteJob: Lote #{$lote->id} finalizado. Enviados: {$enviados}, Falhas: {$falhas}.");
    }
}
