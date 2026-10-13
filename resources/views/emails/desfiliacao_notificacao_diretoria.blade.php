<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Notificação de Desfiliação Confirmada</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; color: #333333;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <tr>
            <td style="background-color: #1e293b; padding: 25px; text-align: center; color: #ffffff;">
                <h1 style="margin: 0; font-size: 18px; font-weight: bold;">Gestão de Cadastro e Arrecadação</h1>
                <p style="margin: 5px 0 0 0; font-size: 13px; color: #cbd5e1;">Notificação de Desfiliação Digital Confirmada</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px 25px;">
                <p style="font-size: 15px; margin-top: 0; color: #1e293b;">
                    Prezados dirigentes e equipe de cadastro,
                </p>
                <p style="font-size: 14px; line-height: 1.6; color: #475569;">
                    Um associado concluiu o processo de assinatura eletrônica do requerimento de desfiliação voluntária no Portal de Aplicativos.
                </p>

                <table width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; margin: 20px 0; font-size: 13px; color: #334155;">
                    <tr>
                        <td style="padding: 5px 0; width: 140px;"><strong>Nome Completo:</strong></td>
                        <td style="padding: 5px 0;">{{ $desfiliacao->nome_completo }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>CPF:</strong></td>
                        <td style="padding: 5px 0;">{{ $desfiliacao->cpf_formatado }}</td>
                    </tr>
                    @if($desfiliacao->matricula)
                    <tr>
                        <td style="padding: 5px 0;"><strong>Matrícula:</strong></td>
                        <td style="padding: 5px 0;">{{ $desfiliacao->matricula }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding: 5px 0;"><strong>Empresa:</strong></td>
                        <td style="padding: 5px 0;">{{ $desfiliacao->empresa }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>E-mail:</strong></td>
                        <td style="padding: 5px 0;">{{ $desfiliacao->email }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Telefone/WhatsApp:</strong></td>
                        <td style="padding: 5px 0;">{{ $desfiliacao->telefone_formatado }}</td>
                    </tr>
                    @if($desfiliacao->motivo)
                    <tr>
                        <td style="padding: 5px 0;"><strong>Motivo Informado:</strong></td>
                        <td style="padding: 5px 0;">{{ $desfiliacao->motivo }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding: 5px 0;"><strong>IP de Confirmação:</strong></td>
                        <td style="padding: 5px 0;">{{ $desfiliacao->ip_confirmacao }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Data/Hora Assinatura:</strong></td>
                        <td style="padding: 5px 0;">{{ $desfiliacao->confirmado_em ? $desfiliacao->confirmado_em->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Código SHA-256:</strong></td>
                        <td style="padding: 5px 0; font-family: monospace; font-size: 11px; color: #2563eb; word-break: break-all;">
                            {{ $desfiliacao->termo_hash }}
                        </td>
                    </tr>
                </table>

                <p style="font-size: 14px; line-height: 1.6; color: #475569;">
                    O termo em formato PDF oficial devidamente assinado com metadados de auditoria encontra-se <strong>em anexo</strong> para os devidos lançamentos e baixa cadastral interna.
                </p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f1f5f9; padding: 15px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0;">
                Sistema Portal de Aplicativos Sindicais
            </td>
        </tr>
    </table>
</body>
</html>
