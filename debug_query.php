<?php 
require 'vendor/autoload.php'; 
$app = require_once 'bootstrap/app.php'; 
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class); 
$kernel->bootstrap(); 
try { 
    $q = \App\Models\AtivoEstacao::query()->where('departamento_id', 1)->orderBy('nome')->get(['id', 'nome']); 
    echo $q->toJson(); 
} catch (\Exception $e) { 
    echo "ERROR: " . $e->getMessage(); 
}
