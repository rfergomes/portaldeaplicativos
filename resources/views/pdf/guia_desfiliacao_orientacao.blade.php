<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Guia Orientativo - Desfiliação Sindical Digital</title>
    <style>
        @page {
            margin: 12mm 15mm 12mm 15mm;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5pt;
            line-height: 1.45;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }
        
        /* Cabeçalho */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 3px solid #0284c7;
            padding-bottom: 10px;
        }
        .header-title {
            font-size: 16pt;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-sub {
            font-size: 10pt;
            color: #0284c7;
            font-weight: bold;
            margin-top: 3px;
        }
        .badge-versao {
            background-color: #0284c7;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 12px;
            text-align: center;
            display: inline-block;
        }

        /* Banner Ilustrado */
        .banner-container {
            width: 100%;
            text-align: center;
            margin: 10px 0 16px 0;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px;
            background-color: #f8fafc;
        }
        .banner-img {
            width: 100%;
            max-height: 255px;
            border-radius: 6px;
        }

        /* Destaque Introdutório */
        .intro-box {
            background-color: #f0f9ff;
            border-left: 4px solid #0284c7;
            padding: 10px 14px;
            border-radius: 4px;
            margin-bottom: 14px;
            font-size: 9.5pt;
            color: #0c4a6e;
        }

        /* Grade de 3 Colunas para Modalidades de Link */
        .modalidades-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 16px;
        }
        .mod-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px;
            vertical-align: top;
            width: 33.33%;
        }
        .mod-title {
            font-weight: bold;
            font-size: 9.5pt;
            color: #0f172a;
            margin-bottom: 5px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 4px;
        }
        .mod-desc {
            font-size: 8.5pt;
            color: #475569;
        }

        /* Seção Título */
        .section-header {
            background-color: #0f172a;
            color: #ffffff;
            padding: 6px 12px;
            font-size: 11pt;
            font-weight: bold;
            border-radius: 4px;
            margin: 14px 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Cards do Passo a Passo */
        .step-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .step-num-col {
            width: 45px;
            text-align: center;
            vertical-align: middle;
            color: #ffffff;
            font-size: 14pt;
            font-weight: bold;
            border-top-left-radius: 5px;
            border-bottom-left-radius: 5px;
        }
        .step-content-col {
            padding: 8px 12px;
            vertical-align: top;
        }
        .step-title {
            font-weight: bold;
            font-size: 10pt;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .step-actor {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 4px;
        }
        .step-desc {
            font-size: 9pt;
            color: #334155;
            line-height: 1.4;
        }

        /* Cores temáticas por passo */
        .step-1-color { background-color: #0284c7; }
        .step-2-color { background-color: #0d9488; }
        .step-3-color { background-color: #1e293b; }
        .step-4-color { background-color: #d97706; }
        .step-5-color { background-color: #16a34a; }

        .actor-operador { background-color: #e0f2fe; color: #0369a1; }
        .actor-trabalhador { background-color: #ccfbf1; color: #0f766e; }
        .actor-sindicato { background-color: #f1f5f9; color: #334155; }
        .actor-sistema { background-color: #fef3c7; color: #b45309; }
        .actor-empresa { background-color: #dcfce7; color: #15803d; }

        /* Tabela de Responsabilidades */
        .resp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-top: 8px;
        }
        .resp-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #cbd5e1;
            font-weight: bold;
        }
        .resp-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        /* Box de Validade Jurídica */
        .juridico-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #16a34a;
            border-radius: 4px;
            padding: 8px 12px;
            margin-top: 10px;
            font-size: 8.5pt;
            color: #1e293b;
        }

        /* Rodapé */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 7.5pt;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- ==================== PÁGINA 1 ==================== -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <h1 class="header-title">Desfiliação Sindical Digital</h1>
                <div class="header-sub">Guia Ilustrado de Orientação e Fluxo de Atendimento Oficial</div>
            </td>
            <td style="text-align: right; vertical-align: middle; width: 140px;">
                <span class="badge-versao">PORTAL DE APLICATIVOS</span>
                <div style="font-size: 8pt; color: #64748b; margin-top: 4px;">Edição 2026 • 100% Digital</div>
            </td>
        </tr>
    </table>

    <!-- Imagem Ilustrativa Central em Cores -->
    <div class="banner-container">
        @if(!empty($imagemBase64))
            <img src="{{ $imagemBase64 }}" class="banner-img" alt="Fluxo Ilustrado de Desfiliação">
        @endif
    </div>

    <!-- Caixa de Apresentação / Objetivo -->
    <div class="intro-box">
        <strong>🎯 Propósito do Módulo:</strong> Proporcionar um atendimento individualizado, transparente e desburocratizado para o requerimento de desfiliação. Elimina papéis e filas por meio de links nominais temporários, rubrica digital na tela com validação biométrica/touch, protocolo institucional e notificação automática via AR-Online com fé pública.
    </div>

    <!-- Modalidades de Geração de Links -->
    <div style="font-weight: bold; font-size: 10pt; color: #0f172a; margin-bottom: 6px; text-transform: uppercase;">
        Modos de Emissão do Atendimento (Operador / Representante)
    </div>
    <table class="modalidades-table">
        <tr>
            <td class="mod-card">
                <div class="mod-title" style="color: #0284c7;">1. Link Individual</div>
                <div class="mod-desc">
                    Emitido com identificação nominal e contato do trabalhador. Ideal para atendimento direto via WhatsApp ou balcão. Válido para <strong>1 única utilização</strong> e expiração de 24h a 72h.
                </div>
            </td>
            <td class="mod-card">
                <div class="mod-title" style="color: #0d9488;">2. Emissão em Lote</div>
                <div class="mod-desc">
                    Permite ao operador gerar múltiplos links individuais em um único clique, facilitando o envio simultâneo para listas de trabalhadores sem retrabalho manual.
                </div>
            </td>
            <td class="mod-card">
                <div class="mod-title" style="color: #d97706;">3. Link com Cota</div>
                <div class="mod-desc">
                    Gera <strong>1 link compartilhado</strong> configurado para <em>N</em> utilizações (ex: 5 atendimentos em um setor). Após atingir a cota máxima de submissões, o link bloqueia automaticamente.
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabela Síntese de Papéis -->
    <div style="font-weight: bold; font-size: 10pt; color: #0f172a; margin-bottom: 4px; text-transform: uppercase;">
        Síntese de Responsabilidades por Ator
    </div>
    <table class="resp-table">
        <thead>
            <tr>
                <th style="width: 25%;">Ator</th>
                <th style="width: 40%;">Ações no Fluxo</th>
                <th style="width: 35%;">Garantias & Entregáveis</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>📱 Trabalhador</strong></td>
                <td>Acessa o link seguro, confere dados pessoais e da empregadora, aceita LGPD e assina na tela do celular.</td>
                <td>Recebe Termo Oficial com Protocolo de Recepção por e-mail e QR Code de autenticidade.</td>
            </tr>
            <tr>
                <td><strong>👤 Operador Sindicato</strong></td>
                <td>Emite o link temporário, faz a conferência no ERP Ábaco, homologa o aceite formal ou recusa motivada.</td>
                <td>Painel de controle com alertas em tempo real e prevenção de cartas duplicadas ou divergentes.</td>
            </tr>
            <tr>
                <td><strong>🏢 RH da Empresa</strong></td>
                <td>Recebe a notificação oficial formalizada com protocolo para cessar o desconto da contribuição em folha.</td>
                <td>Envio automático aos e-mails válidos da tabela <code>clientes</code> via AR-Online com registro de entrega.</td>
            </tr>
        </tbody>
    </table>

    <table class="footer-table">
        <tr>
            <td>Portal de Aplicativos Sindicais • Gestão Estratégica & Governança Digital</td>
            <td style="text-align: right;">Página 1 de 2</td>
        </tr>
    </table>

    <!-- ==================== PÁGINA 2 ==================== -->
    <div class="page-break"></div>

    <table class="header-table" style="margin-bottom: 8px;">
        <tr>
            <td style="vertical-align: middle;">
                <h1 class="header-title" style="font-size: 13pt;">Passo a Passo Detalhado do Fluxo</h1>
                <div class="header-sub" style="font-size: 9pt;">Roteiro Operacional das 5 Etapas do Requerimento</div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <span class="badge-versao" style="background-color: #1e293b;">ROTEIRO OPERACIONAL</span>
            </td>
        </tr>
    </table>

    <!-- Passo 1 -->
    <table class="step-table">
        <tr>
            <td class="step-num-col step-1-color">1</td>
            <td class="step-content-col">
                <span class="step-actor actor-operador">Operador / Representante</span>
                <div class="step-title">Geração e Envio do Link Seguro de Atendimento</div>
                <div class="step-desc">
                    O operador acessa o portal interno e gera um link temporário com validade (24h a 72h). O acesso público geral fica desativado, evitando fraudes. O link é encaminhado por WhatsApp ou E-mail para o trabalhador.
                </div>
            </td>
        </tr>
    </table>

    <!-- Passo 2 -->
    <table class="step-table">
        <tr>
            <td class="step-num-col step-2-color">2</td>
            <td class="step-content-col">
                <span class="step-actor actor-trabalhador">Trabalhador Solicitante</span>
                <div class="step-title">Preenchimento e Rubrica Digital na Tela do Celular</div>
                <div class="step-desc">
                    O trabalhador abre o link em seu smartphone, valida seus dados (Nome, CPF, E-mail, Empresa com autocompletar inteligente) e rubrica com o próprio dedo na tela touchscreen. Aceita os termos de privacidade (LGPD) e envia.
                </div>
            </td>
        </tr>
    </table>

    <!-- Passo 3 -->
    <table class="step-table">
        <tr>
            <td class="step-num-col step-3-color">3</td>
            <td class="step-content-col">
                <span class="step-actor actor-sindicato">Triagem / Operador do Sindicato</span>
                <div class="step-title">Conferência no ERP Ábaco e Homologação do Aceite</div>
                <div class="step-desc">
                    O link é consumido imediatamente para impedir reuso. O operador confere a matrícula e documentação no ERP Ábaco. Havendo conformidade, clica em <strong>"Dar Aceite / Recepcionar"</strong>. O sistema gera o Protocolo Oficial Institucional.
                </div>
            </td>
        </tr>
    </table>

    <!-- Passo 4 -->
    <table class="step-table">
        <tr>
            <td class="step-num-col step-4-color">4</td>
            <td class="step-content-col">
                <span class="step-actor actor-sistema">Sistema / Notificação Oficial</span>
                <div class="step-title">Despacho Oficial Automático via AR-Online e E-mail</div>
                <div class="step-desc">
                    O termo em PDF com a chancela institucional é encaminhado ao e-mail do trabalhador. Simultaneamente, o sistema localiza os contatos corporativos da empresa na tabela <code>clientes</code> e despacha a notificação via <strong>AR-Online (Aviso de Recebimento Digital)</strong> com rastreio de leitura e fé pública.
                </div>
            </td>
        </tr>
    </table>

    <!-- Passo 5 -->
    <table class="step-table">
        <tr>
            <td class="step-num-col step-5-color">5</td>
            <td class="step-content-col">
                <span class="step-actor actor-empresa">Conclusão & Auditoria Pública</span>
                <div class="step-title">Efetivação no Sistema e Consulta Pública de Autenticidade</div>
                <div class="step-desc">
                    O RH da empresa cessa o desconto em folha respaldado pelo AR-Online. O operador efetiva a baixa no cadastro do sindicato. Qualquer parte interessada pode apontar a câmera para o <strong>QR Code</strong> e comprovar a autenticidade jurídica instantaneamente.
                </div>
            </td>
        </tr>
    </table>

    <!-- Box de Validade Jurídica e Segurança -->
    <div class="juridico-box">
        <strong style="color: #16a34a; font-size: 9pt;">🔒 Garantias Jurídicas, Auditoria e LGPD:</strong><br>
        • <strong>Hash SHA-256 e QR Code</strong>: Cada documento possui integridade criptográfica impossível de adulterar.<br>
        • <strong>AR-Online Oficial</strong>: Comprovação legal da ciência da empresa com registro de data, hora e IP de abertura.<br>
        • <strong>Unificação em Clientes</strong>: Os contatos corporativos são gerenciados de forma centralizada e higienizada.<br>
        • <strong>Limpeza Automática</strong>: Links expirados ou não utilizados são descartados periodicamente, protegendo a base de dados.
    </div>

    <table class="footer-table" style="margin-top: 25px;">
        <tr>
            <td>Desenvolvido para máxima segurança, transparência e agilidade no atendimento sindical.</td>
            <td style="text-align: right;">Página 2 de 2</td>
        </tr>
    </table>

</body>
</html>
