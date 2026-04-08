<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$disp = \App\Models\AtivoEquipamento::where("status", "disponivel")->count();
$em_uso = \App\Models\AtivoEquipamento::where("status", "em_uso")->count();
$manu = \App\Models\AtivoEquipamento::where("status", "manutencao")->count();
$baixado = \App\Models\AtivoEquipamento::where("status", "baixado")->count();
$sem_atr = \App\Models\AtivoEquipamento::where("status", "disponivel")->whereNull("estacao_id")->count();

echo "disp: $disp\n";
echo "em_uso: $em_uso\n";
echo "manu: $manu\n";
echo "baixado: $baixado\n";
echo "sem_atr: $sem_atr\n";
