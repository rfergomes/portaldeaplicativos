<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$token = config('services.kwik.token');
$agentEmail = config('services.kwik.agent_email');
$fromNumber = config('services.kwik.from_number');

echo "Kwik Configs:\n";
echo "Token: " . (empty($token) ? "EMPTY" : substr($token, 0, 5) . "...") . "\n";
echo "Agent Email: " . $agentEmail . "\n";
echo "From Number: " . $fromNumber . "\n";

if (empty($token) || empty($agentEmail) || empty($fromNumber)) {
    echo "ERROR: Missing configuration.\n";
    exit(1);
}

// Test destination number (use user's or a dummy)
$dest = "+5519994426262"; // Using the number from the log
$template = "nova_demanda_externa";
$args = [
    "Rodrigo Lima", // {{1}}
    "Admin", // {{2}}
    "Demanda Teste", // {{3}}
    "Detalhes da demanda teste", // {{4}}
    "08/06/2026 18:00", // {{5}}
    "Admin" // {{6}}
];

echo "Sending request...\n";
try {
    $response = \Illuminate\Support\Facades\Http::withHeaders([
        'Authorization' => 'Token ' . $token,
        'Content-Type'  => 'application/json'
    ])->post('https://kwik.app.br/api/api/public/v1/notification/', [
        'agent_email' => $agentEmail,
        'from'        => $fromNumber,
        'to'          => $dest,
        'template'    => $template,
        'body'        => $args
    ]);

    echo "Status code: " . $response->status() . "\n";
    echo "Body: " . $response->body() . "\n";
    print_r($response->json());
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
