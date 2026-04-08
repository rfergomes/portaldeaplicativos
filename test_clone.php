<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$queryBase = \App\Models\AtivoEquipamento::query();
$query = clone $queryBase;
$query->where('status', 'manutencao');
echo "Manutencao count: " . $query->count() . "\n";
echo "Base count: " . $queryBase->count() . "\n";
