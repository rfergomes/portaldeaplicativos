<?php

declare(strict_types=1);

namespace App\Actions;

use App\Domain\Protocolos\Contracts\ArOnlineClient;
use App\Mail\TermoDesfiliacaoTrabalhadorMail;
use App\Models\Desfiliacao;
use App\Models\EmpresaContato;
use App\Services\DesfiliacaoArOnlineService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DespacharTermoDesfiliacaoAction
{
    public function __construct(
        private readonly ArOnlineClient $arOnlineClient,
        private readonly DesfiliacaoArOnlineService $arOnlineService,
    ) {}

    /**
     * Executa o despacho da documentação de desfiliação para o trabalhador e para o empregador.
     */
    public function execute(Desfiliacao $desfiliacao, ?string $emailEmpresaManual = null): array
    {
        $resultados = [
            'trabalhador' => false,
            'empresa' => false,
            'empresa_status' => 'nao_aplicavel',
            'empresa_aronline_id' => null,
        ];

        // 1. Despacho por e-mail para o Trabalhador
        if (!empty($desfiliacao->email)) {
            try {
                Mail::to($desfiliacao->email)->send(new TermoDesfiliacaoTrabalhadorMail($desfiliacao));
                $resultados['trabalhador'] = true;
            } catch (\Throwable $e) {
                Log::error("Falha ao enviar e-mail com termo para o trabalhador #{$desfiliacao->id}: " . $e->getMessage());
            }
        }

        // 2. Determinação do e-mail do RH da Empresa
        $emailRh = $emailEmpresaManual;
        if (empty($emailRh) && !empty($desfiliacao->empresa_email)) {
            $emailRh = $desfiliacao->empresa_email;
        }

        if (empty($emailRh)) {
            $contatoEmpresa = EmpresaContato::encontrarPorCnpjOuNome(null, $desfiliacao->empresa);
            if ($contatoEmpresa && !empty($contatoEmpresa->email_rh)) {
                $emailRh = $contatoEmpresa->email_rh;
            }
        }

        // 3. Despacho Oficial via API AR-Online com Carimbo de Tempo e Validade Jurídica
        if (!empty($emailRh)) {
            try {
                $payload = $this->arOnlineService->criarPayloadNotificacaoEmpresa($desfiliacao, $emailRh);
                $idEmailArOnline = $this->arOnlineClient->send($payload);

                $desfiliacao->update([
                    'empresa_email' => $emailRh,
                    'empresa_aronline_id' => $idEmailArOnline,
                    'empresa_aronline_resposta' => 'Notificação enviada com sucesso via AR-Online.',
                    'empresa_notificada_em' => now(),
                    'empresa_envio_status' => 'enviado',
                ]);

                $resultados['empresa'] = true;
                $resultados['empresa_status'] = 'enviado';
                $resultados['empresa_aronline_id'] = $idEmailArOnline;
            } catch (\Throwable $e) {
                Log::error("Falha no envio via AR-Online para empresa de desfiliação #{$desfiliacao->id}: " . $e->getMessage());

                $desfiliacao->update([
                    'empresa_email' => $emailRh,
                    'empresa_envio_status' => 'falha',
                    'empresa_aronline_resposta' => $e->getMessage(),
                ]);

                $resultados['empresa_status'] = 'falha';
            }
        } else {
            // Empresa não possui e-mail cadastrado: retém em pendência na tela administrativa
            $desfiliacao->update([
                'empresa_envio_status' => 'pendente_contato',
                'empresa_aronline_resposta' => 'Nenhum e-mail de RH cadastrado para a empresa no catálogo corporativo.',
            ]);

            $resultados['empresa_status'] = 'pendente_contato';
        }

        return $resultados;
    }
}
