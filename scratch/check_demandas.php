<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $demandas = \App\Models\Demanda::orderBy('id', 'desc')->take(10)->get();
    echo "Last 10 demands:\n";
    foreach ($demandas as $d) {
        echo "ID: {$d->id} | Titulo: {$d->titulo} | Status: {$d->status} | Tipo Resp: {$d->tipo_responsavel} | Resp User ID: {$d->responsavel_usuario_id} | Resp Nome: {$d->responsavel_nome} | Lida: " . ($d->lida_pelo_responsavel ? 'YES' : 'NO') . " | Criador ID: {$d->criador_id}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
