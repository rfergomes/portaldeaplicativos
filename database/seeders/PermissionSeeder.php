<?php

namespace Database\Seeders;

use App\Models\Permissao;
use App\Models\Perfil;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $modulos = [
            'empresas' => ['Visualizar', 'Criar', 'Editar', 'Excluir'],
            'clientes' => ['Visualizar', 'Criar', 'Editar', 'Excluir'],
            'regioes' => ['Visualizar', 'Criar', 'Editar', 'Excluir'],
            'tipos_clientes' => ['Visualizar', 'Criar', 'Editar', 'Excluir'],
            'eventos' => ['Visualizar', 'Criar', 'Editar', 'Excluir', 'Relatorio'],
            'protocolos' => ['Visualizar', 'Criar', 'Editar', 'Excluir', 'Finalizar', 'Sincronizar'],
            'protocolos_tipos' => ['Visualizar', 'Criar', 'Editar', 'Excluir'],
            'colonias' => ['Visualizar', 'Criar', 'Editar', 'Excluir'],
            'acomodacoes' => ['Visualizar', 'Criar', 'Editar', 'Excluir'],
            'periodos' => ['Visualizar', 'Criar', 'Editar', 'Excluir', 'GerarSemanas'],
            'hospedes' => ['Visualizar', 'Criar', 'Editar', 'Excluir'],
            'reservas' => ['Visualizar', 'Criar', 'Editar', 'Excluir', 'Promover'],
            'inscricoes' => ['Visualizar', 'Criar', 'Editar', 'Excluir'],
            'usuarios' => ['Visualizar', 'Criar', 'Editar', 'Excluir', 'Administrar'],
            'ativos' => ['Visualizar', 'Criar', 'Editar', 'Excluir'],
        ];

        $allPermissionIds = [];

        foreach ($modulos as $modulo => $acoes) {
            foreach ($acoes as $acao) {
                $chave = strtolower($modulo . '.' . str_replace(' ', '_', $acao));
                $nome = $acao . ' ' . ucfirst(str_replace('_', ' ', $modulo));

                $permissao = Permissao::firstOrCreate(
                    ['chave' => $chave],
                    ['nome' => $nome, 'descricao' => "Permite $acao no módulo $modulo"]
                );

                $allPermissionIds[] = $permissao->id;
            }
        }

        // Atualizar perfil Administrador
        $admin = Perfil::where('nome', 'Administrador')->first();
        if ($admin) {
            $admin->permissoes()->sync($allPermissionIds);
        }

        // Atualizar perfil Operador
        $operador = Perfil::where('nome', 'Operador')->first();
        if ($operador) {
            $operadorPermIds = Permissao::where(function($q) {
                $q->where('chave', 'like', '%.visualizar')
                  ->orWhere('chave', 'like', '%.criar')
                  ->orWhere('chave', 'like', '%.editar')
                  ->orWhere('chave', 'like', '%.notificar_whatsapp');
            })
            ->where('chave', 'not like', 'usuarios.%') // Não permite gerenciar usuários
            ->where('chave', 'not like', 'ativos.%')  // NÃO permite gerenciar ativos automaticamente
            ->pluck('id');
            
            $operador->permissoes()->sync($operadorPermIds);
        }

        // Atualizar perfil Consultor
        $consultor = Perfil::where('nome', 'Consultor')->first();
        if ($consultor) {
            $consultorPermIds = Permissao::where('chave', 'like', '%.visualizar')
                ->where('chave', 'not like', 'ativos.%') // NÃO permite visualizar ativos automaticamente
                ->pluck('id');
            $consultor->permissoes()->sync($consultorPermIds);
        }

        // Permissão legado para manter compatibilidade com middlewares atuais
        Permissao::firstOrCreate(['chave' => 'administrar_usuarios'], [
            'nome' => 'Administrar Usuários (Legado)',
            'descricao' => 'Permissão mantida para compatibilidade de rotas'
        ]);

        if ($admin) {
            $admin->permissoes()->attach(Permissao::where('chave', 'administrar_usuarios')->first()->id);
        }
    }
}
