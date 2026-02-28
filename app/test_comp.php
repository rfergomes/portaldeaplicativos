<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$e = App\Models\ProtocoloEnvio::whereNotNull('id_email_externo')->orderByDesc('id')->first();
$res = [];

$endpoints = [
    "/gw/email/comprovante/{$e->id_email_externo}",
    "/gw/email/{$e->id_email_externo}/comprovante",
    "/gw/email/{$e->id_email_externo}/pdf",
    "/gw/comprovante/{$e->id_email_externo}",
    "/gw/comprovante/email/{$e->id_email_externo}",
    "/gw/email/receipt/{$e->id_email_externo}",
    "/gw/email/certidao/{$e->id_email_externo}"
];

$baseUrl = rtrim(config('services.ar_online.base_url'), '/');

foreach ($endpoints as $ep) {
    try {

        $response = Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => $e->token_usado,
            'Content-Type' => 'application/json',
        ])->get($baseUrl . $ep);

        $res[$ep] = [
            'status' => $response->status(),
            'raw' => substr($response->body(), 0, 100)
        ];
    } catch (\Exception $ex) {
        $res[$ep] = ["error" => $ex->getMessage()];
    }
}
file_put_contents('test_comp.json', json_encode($res, JSON_PRETTY_PRINT));
