<?php

namespace App\Console\Commands;

use App\Domain\Protocolos\Contracts\ArOnlineClient;
use App\Models\ProtocoloEnvio;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateProtocolosStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'protocolos:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza o status dos envios de protocolos via AR-Online';

    /**
     * Execute the console command.
     */
    public function handle(ArOnlineClient $client)
    {
        $this->info('Iniciando atualização de status de protocolos...');

        // Busca envios elegíveis que ainda não atingiram o limite de consultas
        $envios = ProtocoloEnvio::with('protocolo', 'protocolo.usuario.tokenDepto')
            ->whereIn('status', ['queued', 'enviado', 'entregue'])
            ->where('consultas_status', '<', 5)
            ->whereNotNull('id_email_externo')
            ->get();

        if ($envios->isEmpty()) {
            $this->info('Nenhum envio elegível para atualização.');
            return;
        }

        $this->info("Encontrados {$envios->count()} envios para consultar.");

        $atualizados = 0;
        $falhas = 0;

        foreach ($envios as $envio) {
            $token = $envio->token_usado
                ?? $envio->protocolo->usuario?->tokenDepto?->token
                ?? config('services.ar_online.token');

            if (!$token) {
                $this->warn("Envio #{$envio->id} sem token disponível. Pulando...");
                continue;
            }

            $client->setToken($token);

            try {
                // Incrementa contador de consulta
                $envio->increment('consultas_status');

                $statusData = $client->getFullStatus($envio->id_email_externo);

                // Prioridade de status: lido > entregue > enviado > falha
                $prioridades = ['lido' => 4, 'entregue' => 3, 'enviado' => 2, 'falha' => 1, 'processado' => 0];
                $statusAtualPeso = $prioridades[$envio->status] ?? 0;
                $novoStatus = $envio->status;
                $dataEntrega = $envio->entregue_em ? clone $envio->entregue_em : null;
                $dataLeitura = $envio->lido_em ? clone $envio->lido_em : null;

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
                        $parsedDate = Carbon::createFromFormat('d/m/Y H:i:s', $dateTime)->format('Y-m-d H:i:s');

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
                            if (!$dataEntrega) {
                                $dataEntrega = Carbon::parse($parsedDate);
                            }
                        }
                    }
                }

                // Salva os dados atualizados
                $mudou = ($envio->status !== $novoStatus ||
                    $envio->entregue_em?->format('Y-m-d H:i:s') !== ($dataEntrega instanceof Carbon ? $dataEntrega->format('Y-m-d H:i:s') : $dataEntrega) ||
                    $envio->lido_em?->format('Y-m-d H:i:s') !== ($dataLeitura instanceof Carbon ? $dataLeitura->format('Y-m-d H:i:s') : $dataLeitura));

                $envio->update([
                    'status' => $novoStatus,
                    'ultima_resposta' => json_encode($statusData),
                    'entregue_em' => $dataEntrega,
                    'lido_em' => $dataLeitura,
                ]);

                // Aciona a atualização no pai se houve mudança relevante
                if ($mudou && $envio->protocolo) {
                    $envio->protocolo->atualizarStatusGeral();
                }

                $atualizados++;
            } catch (\Throwable $e) {
                $errorStr = $e->getMessage();
                Log::warning("Falha ao atualizar status do envio #{$envio->id}: " . $errorStr);

                // Extrai o JSON contido na mensagem de erro (Ex: "Erro ao consultar status completo: {"message":"Registro não encontrado"}")
                $apiMessage = 'Falha na comunicação com a AR-Online.';
                $jsonStart = strpos($errorStr, '{');
                if ($jsonStart !== false) {
                    $jsonObj = json_decode(substr($errorStr, $jsonStart), true);
                    if ($jsonObj && isset($jsonObj['message'])) {
                        $apiMessage = $jsonObj['message'];
                    }
                }

                // Se a API retornar erros assertivos de inexistência, abortamos o envio como falho.
                if (str_contains(strtolower($apiMessage), 'não encontrado') || str_contains(strtolower($apiMessage), 'inexistente') || str_contains(strtolower($apiMessage), 'falha')) {
                    $envio->update([
                        'status' => 'falha',
                        'ultima_resposta' => json_encode(['error' => true, 'message' => $apiMessage], JSON_UNESCAPED_UNICODE)
                    ]);

                    if ($envio->protocolo) {
                        $envio->protocolo->atualizarStatusGeral();
                    }
                }

                $falhas++;
            }
        }

        $this->info("Concluído: $atualizados atualizados, $falhas falhas.");
    }
}
