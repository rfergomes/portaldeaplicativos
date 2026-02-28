<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'admin@portal.com')->first();
if ($user) {
    echo "Usuario encontrado!\n";
    echo "ID: {$user->id}\n";
    echo "Email: {$user->email}\n";
    echo "Password Hash: {$user->password}\n";

    // Força a senha para 'password' para garantir que podemos logar
    $user->password = \Illuminate\Support\Facades\Hash::make('password');
    $user->save();
    echo "Senha redefinida para 'password'.\n";
} else {
    echo "Usuario admin@portal.com NAO ENCONTRADO!\n";
    // Tenta criar o usuario
    $user = App\Models\User::create([
        'name' => 'Admin',
        'email' => 'admin@portal.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
        //'perfil_id' => 1, // Assumindo que 1 seja admin, ajuste se necessario
    ]);
    echo "Usuario admin@portal.com criado com a senha 'password'.\n";
}
