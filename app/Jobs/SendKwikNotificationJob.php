<?php

namespace App\Jobs;

use App\Models\KwikNotification;
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
    public $senderId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $telefone, string $template, array $bodyArgs, ?int $senderId = null)
    {
        $this->telefone = $telefone;
        $this->template = $template;
        $this->bodyArgs = $bodyArgs;
        $this->senderId = $senderId;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        // Kwik API generic rate limit (adjust if known, keeping safe for now)
        return [new \Illuminate\Queue\Middleware\RateLimited('kwik-api')];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (empty($this->telefone)) {
            Log::warning('Kwik API Error: Telefone não fornecido.');
            return;
        }

        $telefoneLimpo = preg_replace('/\D/', '', $this->telefone);
        if (strlen($telefoneLimpo) >= 10 && strlen($telefoneLimpo) <= 11) {
            $telefoneLimpo = '+55' . $telefoneLimpo;
        }

        $token = env('KWIK_API_TOKEN');
        $agentEmail = env('KWIK_AGENT_EMAIL');
        $fromNumber = env('KWIK_FROM_NUMBER');
        
        // Fallback para token de departamento se necessário
        if ($this->senderId) {
            $user = \App\Models\User::with('tokenDepto')->find($this->senderId);
            if ($user && $user->tokenDepto && !empty($user->tokenDepto->token)) {
                $deptToken = $user->tokenDepto->token;
                // Verificação: Se o token do depto for um JWT (AR Online), não serve para o Kwik v1
                if (!str_starts_with($deptToken, 'eyJ')) {
                    $token = $deptToken;
                    Log::info("Kwik API: Usando token do departamento '{$user->tokenDepto->departamento}'.");
                } else {
                    Log::info("Kwik API: Token do departamento é do tipo AR-ONLINE (JWT). Mantendo token do .env para o Kwik.");
                }
            }
        }

        if (empty($token) || empty($agentEmail) || empty($fromNumber)) {
            Log::error('Kwik API Error: Configurações ausentes (KWIK_API_TOKEN, KWIK_AGENT_EMAIL ou KWIK_FROM_NUMBER).');
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
                Log::error('Kwik API Error ao enviar: ' . $response->body());
            } else {
                $data = $response->json();
                $notificationId = $data['id'] ?? $data['notificationID'] ?? null;

                if ($notificationId) {
                    KwikNotification::updateOrCreate(
                        ['notification_id' => $notificationId],
                        [
                            'phone' => $telefoneLimpo,
                            'template' => $this->template,
                            'status' => 'sent',
                        ]
                    );
                }

                Log::info("Kwik API: Notificação '{$this->template}' enviada para $telefoneLimpo. ID: $notificationId");
            }
        } catch (\Exception $e) {
            Log::error('Kwik API Connection Failed: ' . $e->getMessage());
        }
    }
}
