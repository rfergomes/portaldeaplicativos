<?php

declare(strict_types=1);

use App\Models\Perfil;
use App\Models\Permissao;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissoesData = [
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

        // Vincular ao perfil Administrador
        $admin = Perfil::where('nome', 'Administrador')->first();
        if ($admin) {
            $admin->permissoes()->syncWithoutDetaching(array_values($permissoesIds));
        }

        // Vincular ao perfil Operador
        $operador = Perfil::where('nome', 'Operador')->first();
        if ($operador) {
            $operador->permissoes()->syncWithoutDetaching([
                $permissoesIds['desfiliacoes.links.gerar'],
                $permissoesIds['desfiliacoes.recusar'],
            ]);
        }

        // Vincular ao perfil Representante Sindical se existir ou criar
        $representante = Perfil::firstOrCreate(
            ['nome' => 'Representante Sindical'],
            ['descricao' => 'Acesso simplificado para dirigentes e representantes sindicais de base']
        );
        $representante->permissoes()->syncWithoutDetaching([
            $permissoesIds['desfiliacoes.links.gerar.proprios'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permissao::whereIn('chave', [
            'desfiliacoes.links.gerar',
            'desfiliacoes.links.gerar.proprios',
            'desfiliacoes.recusar',
        ])->delete();
    }
};
