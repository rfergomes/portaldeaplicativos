<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$envios = App\Models\ProtocoloEnvio::whereNotNull('id_email_externo')->orderByDesc('id')->take(2)->get();
$res = [];
foreach ($envios as $e) {
    try {
        $baseUrl = rtrim(config('services.ar_online.base_url'), '/');
        $headers = [
            'Authorization' => $e->token_usado,
            'Content-Type' => 'application/json',
        ];

        $urls = [
            'email' => "/gw/email/{$e->id_email_externo}",
            'whatsapp' => "/gw/whatsapp/{$e->id_email_externo}",
            'full' => "/gw/full/{$e->id_email_externo}"
        ];

        foreach ($urls as $type => $url) {
            $response = Illuminate\Support\Facades\Http::withHeaders($headers)->get($baseUrl . $url);
            $res[$e->id_email_externo][$type] = [
                'status' => $response->status(),
                'body' => $response->json()
            ];
        }

    } catch (\Exception $ex) {
        $res[$e->id_email_externo] = ["error" => $ex->getMessage()];
    }
}
file_put_contents('test_status_full.json', json_encode($res, JSON_PRETTY_PRINT));
