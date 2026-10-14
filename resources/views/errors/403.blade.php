<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acesso Não Autorizado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { background-color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }</style>
</head>
<body class="d-flex flex-column justify-content-center align-items-center min-vh-100 p-3">
    <div class="text-center p-5 rounded-4 shadow-lg bg-white border border-danger border-opacity-25" style="max-width: 550px; width: 100%;">
        <div class="mb-4">
            <i class="fa-solid fa-lock text-danger fa-3x p-3 bg-danger bg-opacity-10 rounded-circle border border-danger border-2"></i>
        </div>
        
        <h1 class="display-4 fw-bold text-danger mb-0">403</h1>
        <h2 class="fw-bold text-dark mb-3 fs-5">Ação Não Autorizada ou Link Expirado</h2>
        
        <p class="text-muted mb-4 small leading-relaxed">
            O link de confirmação acessado expirou (validade de 24 horas), foi adulterado ou você não possui permissão para executar esta operação.
        </p>
        
        <div class="d-flex gap-2 justify-content-center">
            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Voltar
            </a>
            <a href="{{ route('desfiliacao.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-file-signature me-1"></i> Nova Solicitação
            </a>
        </div>
        
        <div class="mt-4 pt-3 border-top">
            <small class="text-muted" style="font-size: 11px;">
                Portal de Aplicativos - TI Químicos Unificados &copy; {{ date('Y') }}
            </small>
        </div>
    </div>
</body>
</html>
