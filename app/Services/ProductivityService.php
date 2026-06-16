<?php

namespace App\Services;

use App\Models\User;
use App\Models\DemandaHistorico;
use App\Models\Demanda;
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
                ->where('acao', 'devolutiva') 
                ->where('descricao', 'LIKE', '%EXECUTADA%')
                ->whereBetween('created_at', [$inicio, $fim])
                ->count();

            // Conta quantos protocolos o usuário enviou
            $protocolosEnviados = \App\Models\Protocolo::where('user_id', $user->id)
                ->whereBetween('created_at', [$inicio, $fim])
                ->count();

            // Ações financeiras (Baixas de listagens)
            $acoesFinanceiras = \App\Models\SocioFolhaHistorico::where('user_id', $user->id)
                ->whereIn('acao', ['marcou_lista_ok', 'marcou_baixa_ok'])
                ->whereBetween('created_at', [$inicio, $fim])
                ->count();

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
     * Dados para o gráfico de Burn-down de um usuário (Hoje)
     */
    public function getBurndownHoje($userId = null)
    {
        if (!$userId) return null;

        $hoje = Carbon::today();

        // Demandas que o usuário resolveu HOJE
        $entregue = DemandaHistorico::where('user_id', $userId)
            ->where('acao', 'devolutiva')
            ->where('descricao', 'LIKE', '%EXECUTADA%')
            ->whereDate('created_at', $hoje)
            ->count();

        // Demandas que ainda estão abertas/aguardando na fila deste usuário
        $pendente = Demanda::where('tipo_responsavel', 'usuario')
            ->where('responsavel_usuario_id', $userId)
            ->whereIn('status', [Demanda::STATUS_ABERTA, Demanda::STATUS_AGUARDANDO])
            ->count();

        // A carga diária dele (o que tinha + o que já fez)
        $demandado = $pendente + $entregue;

        return [
            'demandado' => $demandado,
            'entregue' => $entregue,
            'pendente' => $pendente
        ];
    }
}
