<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Database Config Host: " . config('database.connections.mysql.host') . "\n";
echo "Database Config DB: " . config('database.connections.mysql.database') . "\n";
echo "Database Config User: " . config('database.connections.mysql.username') . "\n";

try {
    $dbName = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
    echo "Connected DB Name: " . $dbName . "\n";
    
    $usersCount = \App\Models\User::count();
    echo "Users count: " . $usersCount . "\n";
    
    $perfisCount = \App\Models\Perfil::count();
    echo "Perfis count: " . $perfisCount . "\n";
    
    $permissoesCount = \App\Models\Permissao::count();
    echo "Permissoes count: " . $permissoesCount . "\n";
    
    $users = \App\Models\User::all();
    foreach ($users as $u) {
        echo " - User: {$u->name} ({$u->email})\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
