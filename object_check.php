<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/ativos/equipamentos/pdf/inventario?filtro=baixado', 'GET');
$filtro = $request->get('filtro', 'fisico');
var_dump($filtro);
echo ($filtro === 'baixado' ? 'MATCHES' : 'DOES NOT MATCH');
