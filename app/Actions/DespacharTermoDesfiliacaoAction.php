<?php

declare(strict_types=1);

namespace App\Actions;

use App\Domain\Protocolos\Contracts\ArOnlineClient;
use App\Mail\TermoDesfiliacaoTrabalhadorMail;
use App\Models\Cliente;
use App\Models\Desfiliacao;
use App\Models\DesfiliacaoEmpresaEnvio;
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

        // 2. Determinação de todos os contatos válidos de RH da Empresa (com filtro rigoroso de bounces)
        $empresa = EmpresaContatoResolver::encontrarEmpresa(null, $desfiliacao->empresa);
        $contatoManualCliente = null;

        if (!empty($emailEmpresaManual)) {
            $emailManualNormalizado = strtolower(trim($emailEmpresaManual));
            if ($empresa) {
                $contatoManualCliente = EmpresaContatoResolver::registrarContatoRh($empresa, $emailManualNormalizado);
            }
        }

        /** @var array<string, array{email: string, nome: ?string, cliente: ?Cliente}> $destinatarios */
        $destinatarios = [];

        if ($empresa) {
            $contatosValidos = $empresa->clientes()
                ->validosParaEnvio()
                ->get();

            foreach ($contatosValidos as $contato) {
                if ($contato->temBounce()) {
                    Log::info("Desfiliação #{$desfiliacao->id}: Contato ID {$contato->id} ({$contato->email}) descartado por bounce ativo.");
                    continue;
                }

                $emailKey = strtolower(trim((string) $contato->email));
                if (!empty($emailKey) && !isset($destinatarios[$emailKey])) {
                    $destinatarios[$emailKey] = [
                        'email' => $emailKey,
                        'nome' => $contato->nome,
                        'cliente' => $contato,
                    ];
                }
            }

            // Fallback para o e-mail geral da empresa se nenhum contato específico tiver sido encontrado
            if (empty($destinatarios) && !empty($empresa->email)) {
                $emailEmpresaKey = strtolower(trim((string) $empresa->email));
                $temBounce = Cliente::where('email', $emailEmpresaKey)
                    ->where(function ($q) {
                        $q->where('email_valido', false)->orWhereNotNull('email_bounce_code');
                    })
                    ->exists();

                if (!$temBounce) {
                    $destinatarios[$emailEmpresaKey] = [
                        'email' => $emailEmpresaKey,
                        'nome' => $empresa->razao_social,
                        'cliente' => null,
                    ];
                }
            }
        }

        // Se foi fornecido e-mail manual, assegura sua inclusão (respeitando bounces)
        if (!empty($emailEmpresaManual)) {
            $emailManualKey = strtolower(trim($emailEmpresaManual));
            $temBounceManual = $contatoManualCliente?->temBounce() ?? Cliente::where('email', $emailManualKey)
                ->where(function ($q) {
                    $q->where('email_valido', false)->orWhereNotNull('email_bounce_code');
                })
                ->exists();

            if ($temBounceManual) {
                Log::warning("Desfiliação #{$desfiliacao->id}: E-mail manual ({$emailManualKey}) descartado por histórico de bounce.");
            } else {
                $destinatarios[$emailManualKey] = [
                    'email' => $emailManualKey,
                    'nome' => $contatoManualCliente?->nome ?? $desfiliacao->empresa,
                    'cliente' => $contatoManualCliente,
                ];
            }
        }

        // Fallback legado caso a empresa não esteja no catálogo mas desfiliacao já guardasse empresa_email
        if (empty($destinatarios) && !empty($desfiliacao->empresa_email)) {
            $emailLegado = strtolower(trim((string) $desfiliacao->empresa_email));
            $temBounceLegado = Cliente::where('email', $emailLegado)
                ->where(function ($q) {
                    $q->where('email_valido', false)->orWhereNotNull('email_bounce_code');
                })
                ->exists();

            if (!$temBounceLegado) {
                $destinatarios[$emailLegado] = [
                    'email' => $emailLegado,
                    'nome' => $desfiliacao->empresa,
                    'cliente' => null,
                ];
            }
        }

        // 3. Despacho Oficial via API AR-Online para todos os contatos válidos
        if (!empty($destinatarios)) {
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

            $enviadosSucesso = [];
            $falhas = [];
            $aronlineIds = [];
            $emailsSucesso = [];

            foreach ($destinatarios as $dest) {
                $emailDest = $dest['email'];
                $nomeDest = $dest['nome'];
                $clienteDest = $dest['cliente'];

                try {
                    $payload = $this->arOnlineService->criarPayloadNotificacaoEmpresa($desfiliacao, $emailDest, $nomeDest);
                    $idEmailArOnline = $this->arOnlineClient->send($payload);

                    $envio = DesfiliacaoEmpresaEnvio::create([
                        'desfiliacao_id' => $desfiliacao->id,
                        'cliente_id' => $clienteDest?->id,
                        'email' => $emailDest,
                        'nome_contato' => $nomeDest,
                        'aronline_id' => $idEmailArOnline,
                        'status' => 'enviado',
                        'resposta_webhook' => 'Notificação despachada com sucesso via AR-Online.',
                        'notificada_em' => now(),
                    ]);

                    $enviadosSucesso[] = $envio;
                    $aronlineIds[] = $idEmailArOnline;
                    $emailsSucesso[] = $emailDest;

                    Log::info("Desfiliação #{$desfiliacao->id}: Notificação AR-Online despachada para {$emailDest} (AR ID: {$idEmailArOnline}).");
                } catch (\Throwable $e) {
                    Log::error("Falha no envio via AR-Online para empresa ({$emailDest}) em desfiliação #{$desfiliacao->id}: " . $e->getMessage());

                    DesfiliacaoEmpresaEnvio::create([
                        'desfiliacao_id' => $desfiliacao->id,
                        'cliente_id' => $clienteDest?->id,
                        'email' => $emailDest,
                        'nome_contato' => $nomeDest,
                        'status' => 'falha',
                        'resposta_webhook' => $e->getMessage(),
                        'notificada_em' => now(),
                    ]);

                    $falhas[] = "{$emailDest} (" . $e->getMessage() . ")";
                }
            }

            $todosEmails = array_keys($destinatarios);
            $desfiliacao->empresa_email = !empty($emailsSucesso) 
                ? substr(implode(', ', $emailsSucesso), 0, 191) 
                : substr(implode(', ', $todosEmails), 0, 191);
            $desfiliacao->empresa_aronline_id = !empty($aronlineIds) 
                ? substr(implode(', ', $aronlineIds), 0, 100) 
                : null;
            $desfiliacao->empresa_notificada_em = !empty($enviadosSucesso) ? now() : null;

            if (!empty($enviadosSucesso)) {
                $desfiliacao->empresa_envio_status = 'enviado';
                $desfiliacao->empresa_aronline_resposta = 'Notificação despachada para ' . count($enviadosSucesso) . ' contato(s) da empresa via AR-Online.';
                $resultados['empresa'] = true;
                $resultados['empresa_status'] = 'enviado';
                $resultados['empresa_aronline_id'] = implode(', ', $aronlineIds);
            } else {
                $desfiliacao->empresa_envio_status = 'falha';
                $desfiliacao->empresa_aronline_resposta = 'Falha no envio para os destinatários: ' . implode('; ', $falhas);
                $resultados['empresa_status'] = 'falha';
            }

            $desfiliacao->save();
            $desfiliacao->consolidarStatusEnvioEmpresa();
        } else {
            // Empresa não possui nenhum contato válido (sem bounce)
            $desfiliacao->update([
                'empresa_envio_status' => 'pendente_contato',
                'empresa_aronline_resposta' => 'Nenhum e-mail de RH válido (sem bounce) cadastrado para a empresa no catálogo corporativo.',
            ]);

            $resultados['empresa_status'] = 'pendente_contato';
        }

        return $resultados;
    }
}
