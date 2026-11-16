<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Protocolos\Services\ProtocoloDispatcher;
use App\Models\Protocolo;
use App\Models\ProtocoloDestinatario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarOficioColetivoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Tempo limite de execução do job em segundos (30 minutos para lotes grandes).
     */
    public int $timeout = 1800;

    /**
     * Número máximo de tentativas do Job inteiro.
     */
    public int $tries = 1;

    /**
     * @param int $protocoloId
     * @param array<int>|null $destinatarioIds Lista específica de IDs para enviar (opcional)
     */
    public function __construct(
        public int $protocoloId,
        public ?array $destinatarioIds = null
    ) {
    }

    /**
     * Executa o processamento do lote do ofício coletivo.
     */
    public function handle(ProtocoloDispatcher $dispatcher): void
    {
        $protocolo = Protocolo::with(['anexos', 'usuario.tokenDepto'])->find($this->protocoloId);

        if (!$protocolo) {
            Log::error("EnviarOficioColetivoJob: Protocolo #{$this->protocoloId} não encontrado.");
            return;
        }

        Log::info("EnviarOficioColetivoJob: Iniciando processamento do Ofício #{$protocolo->id}");

        // Marca protocolo como processando/pendente se necessário
        if ($protocolo->status === 'pendente') {
            $protocolo->update(['status' => 'processando']);
        }

        // Busca os destinatários a serem enviados
        $query = ProtocoloDestinatario::with('empresa')
            ->where('protocolo_id', $protocolo->id);

        if (!empty($this->destinatarioIds)) {
            $query->whereIn('id', $this->destinatarioIds);
        } else {
            // Apenas destinatários cujo envio ainda não foi enviado com sucesso
            $query->where(function ($q) {
                $q->whereDoesntHave('envios')
                    ->orWhereHas('envios', function ($qEnvio) {
                        $qEnvio->where('status', 'pendente');
                    });
            });
        }

        $destinatarios = $query->get();

        if ($destinatarios->isEmpty()) {
            Log::info("EnviarOficioColetivoJob: Nenhum destinatário pendente para o Ofício #{$protocolo->id}.");
            $protocolo->atualizarStatusGeral();
            return;
        }

        $token = $dispatcher->resolveToken($protocolo);
        $dispatcher->getHttpClient()->setToken($token);

        // Pré-carrega anexos em memória UMA ÚNICA VEZ para evitar leituras de disco repetidas
        $attachmentsPayload = $dispatcher->prepararAnexosPayload($protocolo);

        $enviadosComSucesso = 0;
        $falhas = 0;
        $total = $destinatarios->count();

        Log::info("EnviarOficioColetivoJob: Ofício #{$protocolo->id} - Processando {$total} destinatários.");

        foreach ($destinatarios as $index => $destinatario) {
            try {
                $sucesso = $dispatcher->enviarParaDestinatario(
                    $protocolo,
                    $destinatario,
                    $token,
                    $attachmentsPayload
                );

                if ($sucesso > 0) {
                    $enviadosComSucesso++;
                } else {
                    $falhas++;
                }
            } catch (\Throwable $e) {
                $falhas++;
                Log::error("EnviarOficioColetivoJob: Erro inesperado ao enviar para destinatário #{$destinatario->id} do ofício #{$protocolo->id}: " . $e->getMessage());
            }

            // Atualiza status do protocolo a cada 10 envios ou no final
            if (($index + 1) % 10 === 0 || ($index + 1) === $total) {
                $protocolo->atualizarStatusGeral();
            }

            // Micro-pausa de 100ms para evitar rate limiting da API AR-Online
            usleep(100000);
        }

        $protocolo->atualizarStatusGeral();

        Log::info("EnviarOficioColetivoJob: Concluído Ofício #{$protocolo->id}. Sucessos: {$enviadosComSucesso}, Falhas: {$falhas}, Total: {$total}");
    }
}
