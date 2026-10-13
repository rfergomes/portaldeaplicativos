<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Termo de Desfiliação Sindical Assinado</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; color: #333333;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <tr>
            <td style="background-color: #047857; padding: 25px; text-align: center; color: #ffffff;">
                <h1 style="margin: 0; font-size: 20px; font-weight: bold; letter-spacing: 0.5px;">Portal Sindical - Gestão Digital</h1>
                <p style="margin: 5px 0 0 0; font-size: 13px; opacity: 0.9;">Termo Oficial de Desfiliação Emitido com Sucesso</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px 25px;">
                <p style="font-size: 16px; margin-top: 0;">Prezado(a) <strong>{{ $desfiliacao->nome_completo }}</strong>,</p>
                
                <p style="font-size: 14px; line-height: 1.6; color: #4b5563;">
                    Confirmamos a conclusão da sua solicitação de desfiliação voluntária. A sua assinatura eletrônica foi validada e o requerimento formal foi gerado com trilha probatória de auditoria (Lei Federal nº 14.063/2020).
                </p>

                <!-- ALERTA DE DESTAQUE REQUISITADO PELO USUÁRIO -->
                <div style="background-color: #fffbeb; border: 2px solid #f59e0b; border-radius: 8px; padding: 18px 20px; margin: 25px 0;">
                    <p style="margin: 0 0 8px 0; font-size: 14px; font-weight: bold; color: #b45309; display: flex; align-items: center;">
                        ⚠️ ATENÇÃO - IMPORTANTE:
                    </p>
                    <p style="margin: 0; font-size: 13px; color: #92400e; line-height: 1.6; font-weight: 500;">
                        A sua desfiliação foi processada junto ao Sindicato. Para suspender o desconto em folha, faça o download do documento gerado e entregue uma cópia impressa ou digitalizada ao RH da sua empresa.
                    </p>
                </div>

                <table width="100%" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px; margin: 20px 0; font-size: 13px; color: #374151;">
                    <tr>
                        <td style="padding: 4px 0;"><strong>Trabalhador:</strong></td>
                        <td style="padding: 4px 0;">{{ $desfiliacao->nome_completo }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0;"><strong>CPF:</strong></td>
                        <td style="padding: 4px 0;">{{ $desfiliacao->cpf_formatado }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0;"><strong>Empresa:</strong></td>
                        <td style="padding: 4px 0;">{{ $desfiliacao->empresa }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0;"><strong>Data/Hora da Assinatura:</strong></td>
                        <td style="padding: 4px 0;">{{ $desfiliacao->confirmado_em ? $desfiliacao->confirmado_em->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0;"><strong>Código Verificador (Hash):</strong></td>
                        <td style="padding: 4px 0; font-family: monospace; font-size: 11px; color: #1e40af; word-break: break-all;">
                            {{ $desfiliacao->termo_hash }}
                        </td>
                    </tr>
                </table>

                <p style="font-size: 14px; line-height: 1.6; color: #4b5563;">
                    O arquivo PDF oficial encontra-se <strong>em anexo a este e-mail</strong>. Ele contém sua rubrica manuscrita digitalizada, a declaração de responsabilidade exclusiva perante a fonte pagadora e os metadados de autenticidade jurídica.
                </p>

                <p style="font-size: 13px; line-height: 1.5; color: #6b7280; margin-top: 25px;">
                    Para conferência pública da autenticidade deste documento a qualquer momento, o RH da sua empresa pode acessar:
                    <br>
                    <a href="{{ route('desfiliacao.validar', ['hash' => $desfiliacao->termo_hash]) }}" style="color: #2563eb; word-break: break-all; font-weight: bold;">
                        {{ route('desfiliacao.validar', ['hash' => $desfiliacao->termo_hash]) }}
                    </a>
                </p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f3f4f6; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb;">
                <p style="margin: 0;">Portal de Aplicativos Sindicais &copy; {{ date('Y') }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
