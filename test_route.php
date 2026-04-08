<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = new \Illuminate\Http\Request();
$request->merge(['filtro' => 'baixado']);

$controller = $app->make(\App\Http\Controllers\Ativos\AtivoEquipamentoController::class);
$response = $controller->gerarInventarioPdf($request);

if (method_exists($response, 'getContent')) {
    echo "Content length: " . strlen($response->getContent()) . "\n";
} else {
    echo "Response type: " . get_class($response) . "\n";
}
