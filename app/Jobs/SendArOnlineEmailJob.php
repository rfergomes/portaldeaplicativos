<?php

namespace App\Jobs;

use App\Models\ArOnlineNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendArOnlineEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email;
    public $nome;
    public $assunto;
    public $conteudo;
    public $senderId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $email, string $nome, string $assunto, string $conteudo, ?int $senderId = null)
    {
        $this->email = $email;
        $this->nome = $nome;
        $this->assunto = $assunto;
        $this->conteudo = $conteudo;
        $this->senderId = $senderId;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new \Illuminate\Queue\Middleware\RateLimited('aronline-api')];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (empty($this->email)) {
            Log::warning('AR Online (Email) API Error: E-mail não fornecido.');
            return;
        }

        $baseUrl = env('AR_ONLINE_BASE_URL', 'https://api.ar-online.com.br');
        $token = env('AR_ONLINE_TOKEN');
        
        if ($this->senderId) {
            $user = \App\Models\User::with('tokenDepto')->find($this->senderId);
            if ($user && $user->tokenDepto && !empty($user->tokenDepto->token)) {
                $deptToken = $user->tokenDepto->token;
                // Verificação: AR Online Email exige o novo padrão JWT (eyJ...)
                if (str_starts_with($deptToken, 'eyJ')) {
                    $token = $deptToken;
                    Log::info("AR Online API: Usando token JWT do departamento '{$user->tokenDepto->departamento}'.");
                } else {
                    Log::info("AR Online API: Token do departamento é legado (Kwik). Mantendo token JWT do .env para AR Online.");
                }
            }
        }

        if (empty($token)) {
            Log::error('AR Online (Email) API Error: Token ausente.');
            return;
        }

        $authHeader = (str_starts_with($token, 'eyJ')) ? $token : 'Token ' . $token;

        try {
            $response = Http::withHeaders([
                'Authorization' => $authHeader,
                'Content-Type'  => 'application/json'
            ])->post($baseUrl . '/gw/email', [
                'nameTo'  => $this->nome,
                'to'      => $this->email,
                'subject' => $this->assunto,
                'content' => $this->conteudo,
            ]);

            if (!$response->successful()) {
                Log::error('AR Online (Email) API Error: ' . $response->body());
            } else {
                $data = $response->json();
                $notificationId = $data['idEmail'] ?? $data['id'] ?? null;

                if ($notificationId) {
                    ArOnlineNotification::updateOrCreate(
                        ['notification_id' => $notificationId],
                        [
                            'phone' => $this->email, // Using phone column to store email for now, or I should have a 'destinatario' column
                            'template' => 'email_autenticado',
                            'status' => 'sent',
                        ]
                    );
                }

                Log::info("AR Online (Email): E-mail autenticado enviado para {$this->email}. ID: $notificationId");
            }
        } catch (\Exception $e) {
            Log::error('AR Online (Email) API Connection Failed: ' . $e->getMessage());
        }
    }
}
