<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Comunicado de Solicitação de Desfiliação Não Homologada</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
        .header { background: #0f172a; padding: 24px; text-align: center; color: #ffffff; }
        .content { padding: 30px; line-height: 1.6; font-size: 14px; }
        .alert-box { background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin: 20px 0; color: #991b1b; }
        .motivo-box { background-color: #f1f5f9; border-left: 4px solid #ef4444; padding: 12px 16px; margin: 15px 0; font-style: italic; color: #334155; }
        .footer { background: #f8fafc; padding: 16px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0; font-size: 18px;">Sindicato Químicos Unificados</h2>
            <p style="margin: 4px 0 0; font-size: 12px; color: #94a3b8;">Atendimento e Homologação Cadastral</p>
        </div>

        <div class="content">
            <p>Prezado(a) <strong>{{ $desfiliacao->nome_completo }}</strong>,</p>

            <p>
                Informamos que sua solicitação de desfiliação registrada sob o protocolo <strong>#{{ $desfiliacao->id }}</strong> foi analisada pela diretoria sindical perante os registros cadastrais da entidade e do sistema ERP Ábaco, e <strong>não pôde ser homologada</strong> devido a inconsistências identificadas.
            </p>

            <div class="alert-box">
                <strong>Justificativa da Recusa / Não Homologação:</strong>
                <div class="motivo-box">
                    "{{ $motivoRecusa }}"
                </div>
            </div>

            <p><strong>Consequências desta decisão:</strong></p>
            <ul>
                <li>A solicitação anterior foi <strong>invalidada</strong> no sistema;</li>
                <li>Nenhum termo ou ofício de suspensão de contribuição foi emitido ao empregador nesta data;</li>
                <li>Seus dados associativos permanecem no status cadastral anterior.</li>
            </ul>

            <p>
                <strong>Deseja sanar a divergência ou solicitar um novo atendimento?</strong><br>
                Caso queira regularizar as informações, esclarecer eventuais dúvidas ou solicitar a emissão de um novo link exclusivo de atendimento, entre em contato diretamente com a nossa Central de Atendimento pelo telefone <strong>(19) 3737-1400</strong> ou pelo WhatsApp institucional da entidade.
            </p>

            <p style="margin-top: 25px;">
                Atenciosamente,<br>
                <strong>Diretoria e Secretaria de Cadastro</strong><br>
                Sindicato dos Trabalhadores nas Indústrias Químicas e Plásticas
            </p>
        </div>

        <div class="footer">
            Mensagem automática enviada pelo Portal de Aplicativos Sindicais &bull; Químicos Unificados
        </div>
    </div>
</body>
</html>
