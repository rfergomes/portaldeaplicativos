<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atendimento de Desfiliação | Sindicato Químicos Unificados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .info-card {
            max-width: 680px;
            width: 100%;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .header-stripe {
            height: 6px;
            background: linear-gradient(90deg, #0d6efd 0%, #0284c7 100%);
        }
        .channel-item {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            transition: all 0.2s ease;
        }
        .channel-item:hover {
            border-color: #cbd5e1;
            background-color: #f1f5f9;
        }
    </style>
</head>
<body>
    <div class="info-card">
        <div class="header-stripe"></div>
        <div class="p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 72px; height: 72px;">
                    <i class="fa-solid fa-shield-halved fa-2x"></i>
                </div>
                <h1 class="h3 fw-bold text-dark mb-2">Atendimento Individualizado de Desfiliação</h1>
                <p class="text-muted">Sindicato dos Trabalhadores nas Indústrias Químicas e Plásticas</p>
            </div>

            <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-warning-emphasis d-flex align-items-start p-3 rounded-3 mb-4">
                <i class="fa-solid fa-triangle-exclamation me-3 mt-1 fa-lg text-warning"></i>
                <div>
                    <h6 class="fw-bold mb-1">Preenchimento Público Desativado</h6>
                    <p class="small mb-0">
                        Para segurança cadastral e proteção dos direitos da categoria, as solicitações de desfiliação não são mais abertas publicamente. O processo agora é realizado mediante <strong>link exclusivo, nominal e temporário</strong> fornecido diretamente pelos operadores ou dirigentes sindicais da entidade.
                    </p>
                </div>
            </div>

            <div class="mb-4">
                <h6 class="fw-bold text-dark text-uppercase small tracking-wide mb-3">Como solicitar o seu link exclusivo:</h6>
                <div class="row g-2">
                    <div class="col-sm-6">
                        <div class="channel-item h-100 d-flex align-items-center">
                            <i class="fa-brands fa-whatsapp text-success fa-2x me-3"></i>
                            <div>
                                <div class="fw-semibold small">WhatsApp Oficial</div>
                                <div class="text-muted small">(19) 9.7405-5662</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="channel-item h-100 d-flex align-items-center">
                            <i class="fa-solid fa-phone text-primary fa-2x me-3"></i>
                            <div>
                                <div class="fw-semibold small">Central Telefônica</div>
                                <div class="text-muted small">(19) 3735-4900</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mb-3">
                <a href="https://wa.me/5519974055662?text={{ rawurlencode('Olá! Gostaria de receber atendimento sobre desfiliação sindical.') }}" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="btn btn-success btn-lg fw-bold shadow-sm">
                    <i class="fa-brands fa-whatsapp me-2"></i> Falar com o Sindicato pelo WhatsApp
                </a>
                <a href="{{ route('desfiliacao.validar') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-qrcode me-2"></i> Consultar Autenticidade de Carta já Emitida
                </a>
            </div>

            <div class="text-center pt-3 border-top">
                <small class="text-muted">
                    Regional Campinas, Osasco, Vinhedo e Região &bull; Portal de Aplicativos Sindicais
                </small>
            </div>
        </div>
    </div>
</body>
</html>
