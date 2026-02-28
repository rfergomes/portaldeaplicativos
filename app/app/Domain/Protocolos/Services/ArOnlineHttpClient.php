<?php

namespace App\Domain\Protocolos\Services;

use App\Domain\Protocolos\Contracts\ArOnlineClient;
use App\Domain\Protocolos\DTOs\ArOnlineSendPayload;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ArOnlineHttpClient implements ArOnlineClient
{
    private string $baseUrl;
    private ?string $token;

    public function __construct()
    {
        $config = config('services.ar_online');
        $this->baseUrl = rtrim($config['base_url'] ?? '', '/');
        $this->token = $config['token'] ?? null;
    }

    /**
     * Sobrescreve o token AR-Online para uso dinâmico por usuário.
     */
    public function setToken(?string $token): self
    {
        if ($token) {
            $this->token = $token;
        }

        return $this;
    }

    public function send(ArOnlineSendPayload $payload): string
    {
        $response = $this->client()->post($this->baseUrl . '/gw/email', $payload->toArray());

        if (!$response->successful()) {
            throw new RuntimeException('Erro ao enviar AR-Online: ' . $response->body());
        }

        $idEmail = $response->json('idEmail');

        if (!$idEmail) {
            throw new RuntimeException('Resposta da AR-Online sem idEmail.');
        }

        return $idEmail;
    }

    public function getEmailStatus(string $idEmail): array
    {
        $response = $this->client()->get($this->baseUrl . "/gw/email/{$idEmail}");

        return $this->decode($response, 'status do AR-Email');
    }

    public function getFullStatus(string $idEmail): array
    {
        $response = $this->client()->get($this->baseUrl . "/gw/full/{$idEmail}");

        return $this->decode($response, 'status completo');
    }

    public function getReceiptBase64(string $idEmail): ?string
    {
        $response = $this->client()->get($this->baseUrl . "/gw/sending-proof/{$idEmail}");

        if ($response->status() !== 200) {
            return null;
        }

        $json = $response->json();

        if (isset($json['content'])) {
            return $json['content'];
        }

        if (isset($json['message'])) {
            \Illuminate\Support\Facades\Log::warning("AR-Online Comprovante retornou mensagem para ID {$idEmail}: " . $json['message']);
        }

        return null;
    }

    public function getLaudoPdf(string $idEmail): string
    {
        $response = $this->client()->get($this->baseUrl . "/gw/email/laudo/{$idEmail}");

        if (!$response->successful()) {
            throw new RuntimeException('Erro ao buscar laudo pericial: ' . $response->body());
        }

        return $response->body();
    }

    private function client()
    {
        if (!$this->token) {
            throw new RuntimeException('Token da AR-Online não configurado (.env AR_ONLINE_TOKEN).');
        }

        return Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type' => 'application/json',
        ]);
    }

    private function decode($response, string $contexto): array
    {
        if (!$response->successful()) {
            throw new RuntimeException("Erro ao consultar {$contexto}: " . $response->body());
        }

        $json = $response->json();

        if (!is_array($json)) {
            throw new RuntimeException("Resposta inesperada ao consultar {$contexto}.");
        }

        return $json;
    }
}

