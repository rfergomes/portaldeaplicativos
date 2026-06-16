<?php

namespace App\Services;

use App\Models\User;
use App\Models\DemandaHistorico;
use App\Models\ProtocoloEnvio;
use Carbon\Carbon;

class ProductivityService
{
    /**
     * Retorna o extrato de produtividade de usuários, aplicando filtro de equipe se necessário.
     * 
     * @param array|null $userIds Array de IDs para filtrar (ex: equipe do gerente) ou null para todos
     * @param Carbon $inicio
     * @param Carbon $fim
     */
    public function getExtratoProdutividade($userIds = null, $inicio = null, $fim = null)
    {
        $inicio = $inicio ?? Carbon::now()->startOfMonth();
        $fim = $fim ?? Carbon::now()->endOfMonth();

        $query = User::query()->select('id', 'name', 'username');

        if ($userIds) {
            $query->whereIn('id', $userIds);
        }

        $users = $query->get();
        $extrato = [];

        foreach ($users as $user) {
            // Conta quantas demandas este usuário moveu para 'executada'
            $demandasResolvidas = DemandaHistorico::where('user_id', $user->id)
                ->where('acao', 'LIKE', '%executada%') // assumindo que a string da acao contém a palavra executada ou alterou status para executada
                ->whereBetween('created_at', [$inicio, $fim])
                ->count();

            // Conta quantos protocolos o usuário enviou
            $protocolosEnviados = ProtocoloEnvio::where('remetente_id', $user->id)
                ->whereBetween('created_at', [$inicio, $fim])
                ->count();

            // Ações financeiras (pode ser expandido lendo SocioCaixaHistorico)
            $acoesFinanceiras = 0; // Placeholder por enquanto

            if ($demandasResolvidas > 0 || $protocolosEnviados > 0 || $acoesFinanceiras > 0) {
                $extrato[] = [
                    'id' => $user->id,
                    'nome' => $user->name,
                    'setor' => 'Geral', // Pode buscar do token_depto ou perfil
                    'demandas_resolvidas' => $demandasResolvidas,
                    'protocolos_enviados' => $protocolosEnviados,
                    'acoes_financeiras' => $acoesFinanceiras,
                    'total_entregas' => $demandasResolvidas + $protocolosEnviados + $acoesFinanceiras
                ];
            }
        }

        // Ordena por maior produtividade (total de entregas)
        usort($extrato, function($a, $b) {
            return $b['total_entregas'] <=> $a['total_entregas'];
        });

        return $extrato;
    }

    /**
     * Dados para o gráfico de Burn-down de um usuário ou equipe (Hoje)
     */
    public function getBurndownHoje($userId = null)
    {
        // Esta lógica deve cruzar as demandas atribuídas vs demandas resolvidas no dia
        // Implementação simplificada:
        return [
            'demandado' => 10,
            'entregue' => 7,
            'pendente' => 3
        ];
    }
}
