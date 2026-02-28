<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$envios = App\Models\ProtocoloEnvio::whereNotNull('id_email_externo')->orderByDesc('id')->take(2)->get();
foreach ($envios as $e) {
    echo "ID: " . $e->id . "\n";
    print_r(json_decode($e->ultima_resposta, true));

    // Also try fetch live status
    try {
        $client = app(\App\Domain\Protocolos\Contracts\ArOnlineClient::class);
        $client->setToken(config('services.ar_online.token'));
        $apiResponse = $client->getEmailStatus($e->id_email_externo);
        echo "LIVE API:\n";
        print_r($apiResponse);
    } catch (\Exception $ex) {
        echo "API Error: " . $ex->getMessage() . "\n";
    }
}
