<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendKwikNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $telefone;
    public $template;
    public $bodyArgs;

    /**
     * Create a new job instance.
     */
    public function __construct(string $telefone, string $template, array $bodyArgs)
    {
        $this->telefone = $telefone;
        $this->template = $template;
        $this->bodyArgs = $bodyArgs;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (empty($this->telefone)) {
            Log::warning('Kwik API Error: Telefone não fornecido para o envio da notificação.');
            return;
        }

        // Limpeza do número
        $telefoneLimpo = preg_replace('/\D/', '', $this->telefone);
        if (strlen($telefoneLimpo) >= 10 && strlen($telefoneLimpo) <= 11) {
            $telefoneLimpo = '+55' . $telefoneLimpo;
        }

        $token = env('KWIK_API_TOKEN');
        $agentEmail = env('KWIK_AGENT_EMAIL');
        $fromNumber = env('KWIK_FROM_NUMBER');

        if (empty($token) || empty($agentEmail) || empty($fromNumber)) {
            Log::error('Kwik API Error: Configurações ausentes no .env');
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $token,
                'Content-Type'  => 'application/json'
            ])->post('https://kwik.app.br/api/api/public/v1/notification/', [
                'agent_email' => $agentEmail,
                'from'        => $fromNumber,
                'to'          => $telefoneLimpo,
                'template'    => $this->template,
                'body'        => $this->bodyArgs
            ]);

            if (!$response->successful() && $response->status() != 201) {
                Log::error('Kwik API Error ao enviar notificação: ' . $response->body());
            } else {
                Log::info("Kwik API: Notificação de modelo '{$this->template}' enviada para $telefoneLimpo");
            }
        } catch (\Exception $e) {
            Log::error('Kwik API Connection Failed: ' . $e->getMessage());
        }
    }
}
