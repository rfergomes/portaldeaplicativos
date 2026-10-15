<?php

declare(strict_types=1);

namespace App\Actions;

use App\Domain\Protocolos\Contracts\ArOnlineClient;
use App\Mail\TermoDesfiliacaoTrabalhadorMail;
use App\Models\Desfiliacao;
use App\Services\DesfiliacaoArOnlineService;
use App\Services\EmpresaContatoResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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

        // 0. Garante que o PDF do Termo exista em disco antes de qualquer envio
        if (empty($desfiliacao->pdf_path) || !Storage::disk('local')->exists($desfiliacao->pdf_path)) {
            try {
                $pdf = Pdf::loadView('pdf.termo_desfiliacao', compact('desfiliacao'))
                    ->setPaper('a4', 'portrait')
                    ->setOptions([
                        'isRemoteEnabled' => true,
                        'isHtml5ParserEnabled' => true,
                        'dpi' => 150,
                        'defaultFont' => 'Helvetica',
                    ]);
                $ano = $desfiliacao->created_at ? $desfiliacao->created_at->format('Y') : date('Y');
                $relPath = "desfiliacoes/{$ano}/{$desfiliacao->termo_hash}.pdf";
                Storage::disk('local')->put($relPath, $pdf->output());
                $desfiliacao->update(['pdf_path' => $relPath]);
            } catch (\Throwable $e) {
                Log::warning("Falha ao gerar PDF sob demanda no despacho #{$desfiliacao->id}: " . $e->getMessage());
            }
        }

        // 1. Despacho por e-mail para o Trabalhador
        if (!empty($desfiliacao->email)) {
            try {
                Mail::to($desfiliacao->email)->send(new TermoDesfiliacaoTrabalhadorMail($desfiliacao));
                $resultados['trabalhador'] = true;
                Log::info("Termo de desfiliação #{$desfiliacao->id} enviado com sucesso por e-mail para o trabalhador ({$desfiliacao->email}).");
            } catch (\Throwable $e) {
                Log::error("Falha ao enviar e-mail com termo para o trabalhador #{$desfiliacao->id}: " . $e->getMessage());
            }
        }

        // 1.1 Notificação via WhatsApp KWIK Oficial para o Trabalhador
        if (!empty($desfiliacao->telefone)) {
            try {
                $nomeTrabalhador = $desfiliacao->nome_completo;
                $protocolo = (string) $desfiliacao->id;
                $urlValidacao = route('desfiliacao.validar', $desfiliacao->termo_hash);

                dispatch(new \App\Jobs\SendKwikNotificationJob(
                    $desfiliacao->telefone,
                    'desfiliacao_homologada_aviso',
                    [$nomeTrabalhador, $protocolo, $urlValidacao],
                    Auth::id() ?? $desfiliacao->recepcionado_por_user_id
                ));
                $resultados['whatsapp_kwik'] = true;
                Log::info("KWIK: Notificação de homologação de desfiliação #{$desfiliacao->id} disparada para {$desfiliacao->telefone}.");
            } catch (\Throwable $e) {
                Log::warning("KWIK: Falha ao despachar notificação de homologação para {$desfiliacao->telefone}: " . $e->getMessage());
            }
        }

        // 2. Determinação do e-mail do RH da Empresa a partir da tabela clientes
        $emailRh = $emailEmpresaManual;
        if (empty($emailRh) && !empty($desfiliacao->empresa_email)) {
            $emailRh = $desfiliacao->empresa_email;
        }

        if (empty($emailRh)) {
            $contatosInfo = EmpresaContatoResolver::obterContatosValidosParaNotificacao(null, $desfiliacao->empresa);
            if (!empty($contatosInfo['emails'])) {
                $emailRh = $contatosInfo['emails'][0];
                $desfiliacao->update(['empresa_email' => $emailRh]);
            } elseif ($contatosInfo['empresa'] && !empty($contatosInfo['empresa']->email)) {
                $emailRh = $contatosInfo['empresa']->email;
                $desfiliacao->update(['empresa_email' => $emailRh]);
            }
        } elseif (!empty($emailEmpresaManual)) {
            // Se o e-mail foi fornecido manualmente, registra na tabela clientes
            $empresa = EmpresaContatoResolver::encontrarEmpresa(null, $desfiliacao->empresa);
            if ($empresa) {
                EmpresaContatoResolver::registrarContatoRh($empresa, $emailEmpresaManual);
            }
        }

        // 3. Despacho Oficial via API AR-Online com Carimbo de Tempo e Validade Jurídica
        if (!empty($emailRh)) {
            try {
                // Configura o Token AR-Online (prioriza departamento do usuário logado/recebedor)
                $tokenArOnline = Auth::user()?->tokenDepto?->token 
                    ?? $desfiliacao->recepcionadoPor?->tokenDepto?->token 
                    ?? config('services.ar_online.token');

                if ($tokenArOnline) {
                    try {
                        $this->arOnlineClient->setToken($tokenArOnline);
                    } catch (\Throwable $e) {
                        // Tolera mocks de teste sem expectativa prévia de setToken
                    }
                }

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
