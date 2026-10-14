<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Perfil;
use App\Models\Permissao;
use Illuminate\Database\Seeder;

class DesfiliacaoPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissoesData = [
            [
                'chave' => 'desfiliacoes.visualizar',
                'nome' => 'Visualizar Desfiliações',
                'descricao' => 'Permite visualizar o painel, histórico e detalhes probatórios de desfiliações sindicais',
            ],
            [
                'chave' => 'desfiliacoes.recepcionar',
                'nome' => 'Recepcionar / Dar Ciência',
                'descricao' => 'Permite recepcionar formalmente a carta de desfiliação com emissão de carimbo institucional',
            ],
            [
                'chave' => 'desfiliacoes.baixar_abaco',
                'nome' => 'Baixar no ERP Ábaco (Efetivar)',
                'descricao' => 'Permite registrar providências de baixa do sócio no ERP Ábaco e efetivar a desfiliação',
            ],
            [
                'chave' => 'desfiliacoes.links.gerar',
                'nome' => 'Gerenciar Links Exclusivos de Desfiliação',
                'descricao' => 'Permite gerar, listar, cancelar e compartilhar links exclusivos temporais de desfiliação',
            ],
            [
                'chave' => 'desfiliacoes.links.gerar.proprios',
                'nome' => 'Gerar Links Rápidos (Representante / Diretor)',
                'descricao' => 'Permite emitir links rápidos e visualizar somente os próprios links gerados para atendimento em fábrica',
            ],
            [
                'chave' => 'desfiliacoes.recusar',
                'nome' => 'Recusar Solicitação com Justificativa',
                'descricao' => 'Permite indeferir formalmente solicitações de desfiliação divergentes no Ábaco com justificativa obrigatória',
            ],
        ];

        $permissoesIds = [];
        foreach ($permissoesData as $item) {
            $permissao = Permissao::firstOrCreate(
                ['chave' => $item['chave']],
                ['nome' => $item['nome'], 'descricao' => $item['descricao']]
            );
            $permissoesIds[$item['chave']] = $permissao->id;
        }

        $admin = Perfil::where('nome', 'Administrador')->first();
        if ($admin) {
            $admin->permissoes()->syncWithoutDetaching(array_values($permissoesIds));
        }

        $operador = Perfil::where('nome', 'Operador')->first();
        if ($operador) {
            $operador->permissoes()->syncWithoutDetaching([
                $permissoesIds['desfiliacoes.visualizar'],
                $permissoesIds['desfiliacoes.recepcionar'],
                $permissoesIds['desfiliacoes.links.gerar'],
                $permissoesIds['desfiliacoes.recusar'],
            ]);
        }

        $representante = Perfil::firstOrCreate(
            ['nome' => 'Representante Sindical'],
            ['descricao' => 'Acesso simplificado para dirigentes e representantes sindicais de base']
        );
        $representante->permissoes()->syncWithoutDetaching([
            $permissoesIds['desfiliacoes.links.gerar.proprios'],
        ]);
    }
}
