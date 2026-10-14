<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Protocolos\DTOs\ArOnlineSendPayload;
use App\Models\Desfiliacao;
use Illuminate\Support\Facades\Storage;

class DesfiliacaoArOnlineService
{
    /**
     * Monta o payload padronizado da API AR-Online para notificação oficial ao RH da empresa empregadora.
     */
    public function criarPayloadNotificacaoEmpresa(Desfiliacao $desfiliacao, string $emailRh): ArOnlineSendPayload
    {
        $assunto = "Notificação Oficial: Desfiliação Sindical - {$desfiliacao->nome_completo} (CPF: {$desfiliacao->cpf_formatado})";

        $corpoHtml = <<<HTML
<div style="font-family: Arial, sans-serif; color: #1e293b; line-height: 1.6; max-width: 600px;">
    <h3 style="color: #0f172a; border-bottom: 2px solid #dc2626; padding-bottom: 8px;">
        Sindicato dos Químicos Unificados - Regional Campinas
    </h3>
    <p>À Gerência de Recursos Humanos / Departamento Pessoal da empresa <strong>{$desfiliacao->empresa}</strong>,</p>
    
    <p>
        Notificamos oficialmente que o(a) empregado(a) abaixo qualificado(a) formalizou e homologou seu 
        <strong>Requerimento de Desfiliação Sindical</strong> perante esta entidade sindical, assinado eletronicamente 
        nos termos da Lei Federal nº 14.063/2020:
    </p>

    <div style="background-color: #f1f5f9; border-left: 4px solid #dc2626; padding: 12px; margin: 16px 0;">
        <p style="margin: 4px 0;"><strong>Nome Completo:</strong> {$desfiliacao->nome_completo}</p>
        <p style="margin: 4px 0;"><strong>CPF:</strong> {$desfiliacao->cpf_formatado}</p>
        <p style="margin: 4px 0;"><strong>Protocolo Interno:</strong> #{$desfiliacao->id}</p>
        <p style="margin: 4px 0;"><strong>Hash Probatório SHA-256:</strong> <code style="font-size: 11px;">{$desfiliacao->termo_hash}</code></p>
    </div>

    <p>
        Diante da manifestação expressa de vontade do trabalhador, solicitamos a 
        <strong>cessação de quaisquer descontos a título de mensalidade social associativa ou contribuição sindical</strong> 
        em folha de pagamento referente ao(à) empregado(a) acima qualificado(a).
    </p>

    <p>
        Segue em anexo o documento comprobatório completo devidamente chancelado com carimbo de tempo institucional e hash criptográfico.
        A autenticidade deste ato pode ser consultada publicamente no endereço: 
        <a href="{$this->urlAutenticidade($desfiliacao)}">{$this->urlAutenticidade($desfiliacao)}</a>.
    </p>

    <p style="margin-top: 24px; font-size: 12px; color: #64748b;">
        Esta notificação é transmitida via plataforma AR-Online com registro de carimbo de tempo e validade jurídica probatória.
    </p>
</div>
HTML;

        $attachments = [];
        if ($desfiliacao->pdf_path && Storage::disk('local')->exists($desfiliacao->pdf_path)) {
            $conteudo = Storage::disk('local')->get($desfiliacao->pdf_path);
            if ($conteudo) {
                $attachments[] = [
                    'name' => 'Termo_Desfiliacao_' . preg_replace('/\D/', '', (string) $desfiliacao->cpf) . '.pdf',
                    'base64' => base64_encode($conteudo),
                ];
            }
        }

        return new ArOnlineSendPayload(
            nameTo: $desfiliacao->empresa,
            subject: $assunto,
            contentHtml: $corpoHtml,
            emailTo: $emailRh,
            attachments: !empty($attachments) ? $attachments : null,
            customId: 'desfiliacao-' . $desfiliacao->id,
        );
    }

    private function urlAutenticidade(Desfiliacao $desfiliacao): string
    {
        return url('/desfiliacao/validar/' . $desfiliacao->termo_hash);
    }
}
