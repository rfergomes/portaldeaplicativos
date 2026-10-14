<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Confirmação de Solicitação de Desfiliação</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; color: #333333;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <tr>
            <td style="background-color: #1e3a8a; padding: 25px; text-align: center; color: #ffffff;">
                <h1 style="margin: 0; font-size: 20px; font-weight: bold; letter-spacing: 0.5px;">Portal Sindical - Gestão Digital</h1>
                <p style="margin: 5px 0 0 0; font-size: 13px; opacity: 0.9;">Solicitação Eletrônica de Desfiliação</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px 25px;">
                <p style="font-size: 16px; margin-top: 0;">Olá, <strong>{{ $desfiliacao->nome_completo }}</strong>,</p>
                
                <p style="font-size: 14px; line-height: 1.6; color: #4b5563;">
                    Recebemos seu pedido de desfiliação voluntária em nosso portal de atendimento eletrônico.
                </p>

                <table width="100%" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px; margin: 20px 0; font-size: 13px; color: #374151;">
                    <tr>
                        <td style="padding: 4px 0;"><strong>CPF:</strong></td>
                        <td style="padding: 4px 0;">{{ $desfiliacao->cpf_formatado }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0;"><strong>Empresa:</strong></td>
                        <td style="padding: 4px 0;">{{ $desfiliacao->empresa }}</td>
                    </tr>

                    <tr>
                        <td style="padding: 4px 0;"><strong>Data da Solicitação:</strong></td>
                        <td style="padding: 4px 0;">{{ $desfiliacao->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                </table>

                <p style="font-size: 14px; line-height: 1.6; color: #4b5563;">
                    Para dar prosseguimento e concluir formalmente o processo com validade jurídica (conforme Lei Federal nº 14.063/2020), clique no botão abaixo para revisar os dados, desenhar sua assinatura digital e assinar o requerimento:
                </p>

                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ $linkConfirmacao }}" style="background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 14px 28px; font-size: 15px; font-weight: bold; border-radius: 6px; display: inline-block; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);">
                        Confirmar e Assinar Desfiliação
                    </a>
                </div>

                <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 12px 15px; border-radius: 4px; margin: 25px 0;">
                    <p style="margin: 0; font-size: 12px; color: #991b1b; line-height: 1.5;">
                        <strong>Atenção ao prazo:</strong> Este link é individual, seguro e possui validade estrita de <strong>24 horas</strong>. Após esse período, ele expirará automaticamente.
                    </p>
                </div>

                <p style="font-size: 12px; color: #6b7280; line-height: 1.5; margin-bottom: 0;">
                    Se o botão acima não funcionar, copie e cole o endereço abaixo diretamente no seu navegador:<br>
                    <a href="{{ $linkConfirmacao }}" style="color: #2563eb; word-break: break-all;">{{ $linkConfirmacao }}</a>
                </p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f3f4f6; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb;">
                <p style="margin: 0;">Caso você não tenha realizado esta solicitação, por favor desconsidere este e-mail.</p>
                <p style="margin: 5px 0 0 0;">Portal de Aplicativos Sindicais &copy; {{ date('Y') }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
