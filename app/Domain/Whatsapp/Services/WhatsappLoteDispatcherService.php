<?php

declare(strict_types=1);

namespace App\Domain\Whatsapp\Services;

use App\Domain\Whatsapp\DTOs\WhatsappLotePayloadDTO;
use App\Jobs\ProcessarWhatsappLoteJob;
use App\Models\Empresa;
use App\Models\SocioCaixa;
use App\Models\SocioFolha;
use App\Models\WhatsappLote;
use App\Models\WhatsappLoteItem;
use App\Models\WhatsappTemplate;
use Illuminate\Support\Facades\DB;

class WhatsappLoteDispatcherService
{
    /**
     * Sanitiza e valida um número de telefone no formato internacional E.164 (+55...).
     */
    public function formatarTelefone(?string $telefone): ?string
    {
        if (empty($telefone)) {
            return null;
        }

        $limpo = preg_replace('/\D/', '', $telefone);
        if (!$limpo) {
            return null;
        }

        // Se já vier com código do país 55
        if (str_starts_with($limpo, '55') && (strlen($limpo) === 12 || strlen($limpo) === 13)) {
            return '+' . $limpo;
        }

        // DDD (2 dígitos) + 8 ou 9 dígitos = 10 ou 11 dígitos
        if (strlen($limpo) === 10 || strlen($limpo) === 11) {
            return '+55' . $limpo;
        }

        return null;
    }

    /**
     * Monta a query base de Sócio Caixa aplicando os filtros recebidos.
     */
    public function querySocioCaixa(array $filtros)
    {
        $query = SocioCaixa::query();

        if (!empty($filtros['ano'])) {
            $query->where('ano', $filtros['ano']);
        }
        if (!empty($filtros['matricula'])) {
            $query->where('matricula', 'like', '%' . $filtros['matricula'] . '%');
        }
        if (!empty($filtros['nome'])) {
            $query->where('nome', 'like', '%' . $filtros['nome'] . '%');
        }
        if (!empty($filtros['tipo'])) {
            $query->where('tipo_socio', $filtros['tipo']);
        }

        $query->select('matricula', 'nome', 'tipo_socio')
            ->selectRaw('MAX(telefone) as telefone')
            ->selectRaw('COUNT(CASE WHEN (pago = 0 AND (postergado_ate IS NULL OR postergado_ate <= NOW())) THEN 1 END) as total_abertos')
            ->groupBy('matricula', 'nome', 'tipo_socio');

        $minAbertos = isset($filtros['min_abertos']) && $filtros['min_abertos'] !== '' ? (int) $filtros['min_abertos'] : null;
        $maxAbertos = isset($filtros['max_abertos']) && $filtros['max_abertos'] !== '' ? (int) $filtros['max_abertos'] : null;

        if ($minAbertos !== null) {
            $query->havingRaw('COUNT(CASE WHEN (pago = 0 AND (postergado_ate IS NULL OR postergado_ate <= NOW())) THEN 1 END) >= ?', [$minAbertos]);
        }
        if ($maxAbertos !== null) {
            $query->havingRaw('COUNT(CASE WHEN (pago = 0 AND (postergado_ate IS NULL OR postergado_ate <= NOW())) THEN 1 END) <= ?', [$maxAbertos]);
        }

        return $query;
    }

    /**
     * Calcula o resumo prévio para Sócio Caixa.
     */
    public function previewCaixa(array $filtros): array
    {
        $socios = $this->querySocioCaixa($filtros)->get();

        $totalSocios = $socios->count();
        $totalAptos = 0;
        $totalSemTelefone = 0;

        foreach ($socios as $socio) {
            $tel = $this->formatarTelefone($socio->telefone);
            if ($tel) {
                $totalAptos++;
            } else {
                $totalSemTelefone++;
            }
        }

        return [
            'total_socios' => $totalSocios,
            'total_aptos' => $totalAptos,
            'total_sem_telefone' => $totalSemTelefone,
        ];
    }

    /**
     * Cria e enfileira o lote de disparo para Sócio Caixa.
     */
    public function criarLoteCaixa(WhatsappLotePayloadDTO $dto): WhatsappLote
    {
        $socios = $this->querySocioCaixa($dto->filtros)->get();

        return DB::transaction(function () use ($dto, $socios) {
            $template = WhatsappTemplate::findOrFail($dto->templateId);

            $lote = WhatsappLote::create([
                'modulo' => 'caixa',
                'whatsapp_template_id' => $template->id,
                'user_id' => $dto->userId,
                'filtros_aplicados' => $dto->filtros,
                'parametros_extras' => $dto->parametrosExtras,
                'total_destinatarios' => 0,
                'status' => 'pendente',
            ]);

            $totalAptos = 0;

            foreach ($socios as $socio) {
                $telFormatado = $this->formatarTelefone($socio->telefone);
                if (!$telFormatado) {
                    continue;
                }

                WhatsappLoteItem::create([
                    'whatsapp_lote_id' => $lote->id,
                    'matricula' => $socio->matricula,
                    'destinatario_nome' => $socio->nome,
                    'telefone' => $telFormatado,
                    'status' => 'pendente',
                ]);

                $totalAptos++;
            }

            $lote->update(['total_destinatarios' => $totalAptos]);

            if ($totalAptos > 0) {
                ProcessarWhatsappLoteJob::dispatch($lote->id);
            } else {
                $lote->update(['status' => 'concluido', 'concluido_em' => now()]);
            }

            return $lote;
        });
    }

    /**
     * Monta a query base de Sócio Folha aplicando os filtros recebidos.
     */
    public function querySocioFolha(array $filtros)
    {
        $query = SocioFolha::with(['empresa.clientes' => function ($q) {
            $q->where('ativo', true)->whereNotNull('telefone')->where('telefone', '!=', '');
        }]);

        if (!empty($filtros['regiao_id'])) {
            $query->where('regiao_id', $filtros['regiao_id']);
        }
        if (!empty($filtros['empresa_id'])) {
            $query->where('empresa_id', $filtros['empresa_id']);
        }
        if (!empty($filtros['ano'])) {
            $query->where('ano', $filtros['ano']);
        }
        if (!empty($filtros['mes'])) {
            $query->where('mes', $filtros['mes']);
        }
        if (!empty($filtros['situacao'])) {
            if ($filtros['situacao'] === 'PAGO') {
                $query->where('situacao', 'PAGO');
            } elseif ($filtros['situacao'] === 'ABERTO') {
                $query->where('situacao', '!=', 'PAGO');
            }
        }
        if (!empty($filtros['lista_baixa'])) {
            if ($filtros['lista_baixa'] === 'ENTREGUE') {
                $query->whereNotNull('data_lista')->whereNotNull('data_baixa');
            } elseif ($filtros['lista_baixa'] === 'PENDENTE') {
                $query->where(function ($q) {
                    $q->whereNull('data_lista')->orWhereNull('data_baixa');
                });
            }
        }

        return $query;
    }

    /**
     * Calcula o resumo prévio para Sócio Folha.
     */
    public function previewFolha(array $filtros): array
    {
        $lancamentos = $this->querySocioFolha($filtros)->get();

        $empresasProcessadas = [];
        $totalAptos = 0;
        $totalSemTelefone = 0;

        foreach ($lancamentos as $folha) {
            if (!$folha->empresa_id || isset($empresasProcessadas[$folha->empresa_id])) {
                continue;
            }
            $empresasProcessadas[$folha->empresa_id] = true;

            $empresa = $folha->empresa;
            if (!$empresa) {
                continue;
            }

            $telefonesValidos = [];

            // 1. Busca contatos ativos da empresa
            if ($empresa->relationLoaded('clientes') && $empresa->clientes->isNotEmpty()) {
                foreach ($empresa->clientes as $cliente) {
                    $tel = $this->formatarTelefone($cliente->telefone);
                    if ($tel && !in_array($tel, $telefonesValidos, true)) {
                        $telefonesValidos[] = $tel;
                    }
                }
            }

            // 2. Fallback para telefone corporativo da empresa
            if (empty($telefonesValidos) && !empty($empresa->telefone)) {
                $telEmpresa = $this->formatarTelefone($empresa->telefone);
                if ($telEmpresa) {
                    $telefonesValidos[] = $telEmpresa;
                }
            }

            if (!empty($telefonesValidos)) {
                $totalAptos += count($telefonesValidos);
            } else {
                $totalSemTelefone++;
            }
        }

        return [
            'total_empresas' => count($empresasProcessadas),
            'total_aptos' => $totalAptos,
            'total_sem_telefone' => $totalSemTelefone,
        ];
    }

    /**
     * Cria e enfileira o lote de disparo para Sócio Folha.
     */
    public function criarLoteFolha(WhatsappLotePayloadDTO $dto): WhatsappLote
    {
        $lancamentos = $this->querySocioFolha($dto->filtros)->get();

        return DB::transaction(function () use ($dto, $lancamentos) {
            $template = WhatsappTemplate::findOrFail($dto->templateId);

            $lote = WhatsappLote::create([
                'modulo' => 'folha',
                'whatsapp_template_id' => $template->id,
                'user_id' => $dto->userId,
                'filtros_aplicados' => $dto->filtros,
                'parametros_extras' => $dto->parametrosExtras,
                'total_destinatarios' => 0,
                'status' => 'pendente',
            ]);

            $totalAptos = 0;
            $empresasProcessadas = [];

            foreach ($lancamentos as $folha) {
                if (!$folha->empresa_id || isset($empresasProcessadas[$folha->empresa_id])) {
                    continue;
                }
                $empresasProcessadas[$folha->empresa_id] = true;
                $empresa = $folha->empresa;
                if (!$empresa) {
                    continue;
                }

                $destinatariosEmpresa = [];

                if ($empresa->relationLoaded('clientes') && $empresa->clientes->isNotEmpty()) {
                    foreach ($empresa->clientes as $cliente) {
                        $tel = $this->formatarTelefone($cliente->telefone);
                        if ($tel && !isset($destinatariosEmpresa[$tel])) {
                            $destinatariosEmpresa[$tel] = $cliente->nome ?: $empresa->razao_social;
                        }
                    }
                }

                if (empty($destinatariosEmpresa) && !empty($empresa->telefone)) {
                    $tel = $this->formatarTelefone($empresa->telefone);
                    if ($tel) {
                        $destinatariosEmpresa[$tel] = $empresa->nome_fantasia ?: $empresa->razao_social;
                    }
                }

                foreach ($destinatariosEmpresa as $telefone => $nome) {
                    WhatsappLoteItem::create([
                        'whatsapp_lote_id' => $lote->id,
                        'socio_folha_id' => $folha->id,
                        'empresa_id' => $empresa->id,
                        'destinatario_nome' => $nome,
                        'telefone' => $telefone,
                        'status' => 'pendente',
                    ]);
                    $totalAptos++;
                }
            }

            $lote->update(['total_destinatarios' => $totalAptos]);

            if ($totalAptos > 0) {
                ProcessarWhatsappLoteJob::dispatch($lote->id);
            } else {
                $lote->update(['status' => 'concluido', 'concluido_em' => now()]);
            }

            return $lote;
        });
    }
}
