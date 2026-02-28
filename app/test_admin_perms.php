<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = App\Models\User::where('email', 'admin@portal.com')->first();
    if ($user) {
        $perfilAdmin = App\Models\Perfil::firstOrCreate(
            ['nome' => 'Administrador'],
            ['descricao' => 'Acesso total ao sistema']
        );
        $user->perfis()->sync([$perfilAdmin->id]);

        $permissoesIniciais = [
            'administrar_usuarios',
            'gerenciar_eventos',
            'ver_eventos',
            'gerenciar_cadastros',
            'ver_cadastros',
            'gerenciar_protocolos',
            'ver_protocolos'
        ];

        $ids = [];
        foreach ($permissoesIniciais as $p) {
            $permissao = App\Models\Permissao::firstOrCreate(
                ['chave' => $p],
                ['nome' => ucfirst(str_replace('_', ' ', $p)), 'descricao' => "Permite $p"]
            );
            $ids[] = $permissao->id;
        }

        $perfilAdmin->permissoes()->sync($ids);

        echo "SUCESSO: Perfil 'Administrador' atualizado com as chaves corretas.\n";
    }
} catch (\Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
