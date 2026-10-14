<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Indisponível | Sindicato Químicos Unificados</title>
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
        .error-card {
            max-width: 580px;
            width: 100%;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            text-align: center;
            padding: 3rem 2rem;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle mb-3" style="width: 80px; height: 80px;">
            <i class="fa-solid fa-link-slash fa-2x"></i>
        </div>
        <h2 class="h4 fw-bold text-dark mb-2">Link Exclusivo Não Disponível</h2>
        <p class="text-secondary mb-4">
            {{ $motivo ?? 'O link acessado é inválido, expirou ou já foi utilizado para envio de uma solicitação.' }}
        </p>

        <div class="alert alert-light border small text-muted text-start mb-4">
            <i class="fa-solid fa-circle-info text-primary me-2"></i>
            Os links emitidos pelo sindicato são de <strong>uso único</strong> e possuem prazo de validade determinado. Caso você precise de um novo link, entre em contato diretamente com a nossa equipe de atendimento.
        </div>

        <div class="d-grid gap-2">
            <a href="https://wa.me/551937371400" target="_blank" class="btn btn-success fw-bold">
                <i class="fa-brands fa-whatsapp me-2"></i> Falar com o Sindicato no WhatsApp
            </a>
            <a href="{{ route('desfiliacao.validar') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-qrcode me-2"></i> Validar Autenticidade de Documento
            </a>
        </div>
    </div>
</body>
</html>
