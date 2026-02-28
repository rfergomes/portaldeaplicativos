<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Criar Permissões
        $permissoes = [
            ['chave' => 'ver_eventos', 'nome' => 'Visualizar Eventos', 'descricao' => 'Permite ver a lista de eventos'],
            ['chave' => 'criar_eventos', 'nome' => 'Criar Eventos', 'descricao' => 'Permite cadastrar novos eventos'],
            ['chave' => 'ver_protocolos', 'nome' => 'Visualizar Protocolos', 'descricao' => 'Permite ver a lista de protocolos'],
            ['chave' => 'criar_protocolos', 'nome' => 'Criar Protocolos', 'descricao' => 'Permite abrir novos protocolos'],
            ['chave' => 'administrar_usuarios', 'nome' => 'Administrar Usuários', 'descricao' => 'Permite gerenciar usuários e perfis'],
        ];

        foreach ($permissoes as $permData) {
            \App\Models\Permissao::firstOrCreate(
                ['chave' => $permData['chave']],
                ['nome' => $permData['nome'], 'descricao' => $permData['descricao']]
            );
        }

        // 2. Criar Perfis
        $perfis = [
            ['nome' => 'Administrador', 'descricao' => 'Acesso total ao sistema'],
            ['nome' => 'Operador', 'descricao' => 'Acesso às funcionalidades operacionais'],
            ['nome' => 'Consultor', 'descricao' => 'Acesso apenas para visualização'],
        ];

        foreach ($perfis as $perfilData) {
            $perfil = \App\Models\Perfil::firstOrCreate(
                ['nome' => $perfilData['nome']],
                ['descricao' => $perfilData['descricao'], 'ativo' => true]
            );

            // Associar permissões aos perfis
            if ($perfil->nome === 'Administrador') {
                $perfil->permissoes()->sync(\App\Models\Permissao::all()->pluck('id'));
            } elseif ($perfil->nome === 'Operador') {
                $perfil->permissoes()->sync(\App\Models\Permissao::whereIn('chave', ['ver_eventos', 'criar_eventos', 'ver_protocolos', 'criar_protocolos'])->pluck('id'));
            } elseif ($perfil->nome === 'Consultor') {
                $perfil->permissoes()->sync(\App\Models\Permissao::whereIn('chave', ['ver_eventos', 'ver_protocolos'])->pluck('id'));
            }
        }

        // 3. Criar Usuários e associar aos Perfis
        $users = [
            [
                'username' => 'admin',
                'name' => 'Administrador de Teste',
                'email' => 'admin@portal.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'perfil' => 'Administrador'
            ],
            [
                'username' => 'operador',
                'name' => 'Operador de Teste',
                'email' => 'operador@portal.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'perfil' => 'Operador'
            ],
            [
                'username' => 'consultor',
                'name' => 'Consultor de Teste',
                'email' => 'consultor@portal.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'perfil' => 'Consultor'
            ],
        ];

        foreach ($users as $userData) {
            $perfilNome = $userData['perfil'];
            unset($userData['perfil']);

            $user = \App\Models\User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $perfil = \App\Models\Perfil::where('nome', $perfilNome)->first();
            if ($perfil && !$user->perfis()->where('perfil_id', $perfil->id)->exists()) {
                $user->perfis()->attach($perfil->id);
            }
        }
    }
}
