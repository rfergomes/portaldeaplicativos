<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Protocolos Web</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>
        body { margin:0; font-family:Figtree, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background:#e5e7eb; }
        .layout { min-height:100vh; display:flex; flex-direction:column; }
        header { background:#111827; color:#f9fafb; padding:0.75rem 2.25rem; display:flex; align-items:center; justify-content:space-between; }
        .title { font-size:1.05rem; font-weight:600; letter-spacing:0.04em; text-transform:uppercase; }
        main { flex:1; padding:1.5rem 2.25rem 2.5rem; }
        .breadcrumb { font-size:0.8rem; color:#9ca3af; margin-bottom:0.75rem; }
        .panel { border-radius:0.9rem; background:#f9fafb; box-shadow:0 16px 30px rgba(15,23,42,0.18); padding:1.25rem 1.5rem; }
        .panel-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:0.9rem; }
        .panel-title { font-size:1rem; font-weight:600; color:#111827; }
        .panel-subtitle { font-size:0.8rem; color:#6b7280; }
        table { width:100%; border-collapse:collapse; font-size:0.85rem; margin-top:0.8rem; }
        th, td { padding:0.6rem 0.5rem; text-align:left; }
        th { font-size:0.8rem; text-transform:uppercase; letter-spacing:0.05em; color:#9ca3af; border-bottom:1px solid #e5e7eb; }
        tbody tr:nth-child(odd) { background:#f9fafb; }
        tbody tr:nth-child(even) { background:#eef2ff; }
        .badge { border-radius:999px; padding:0.15rem 0.6rem; font-size:0.7rem; font-weight:500; }
        .badge-status-enviado { background:#dbeafe; color:#1d4ed8; }
        .badge-status-pendente { background:#fef9c3; color:#854d0e; }
        .badge-status-falha { background:#fee2e2; color:#b91c1c; }
        .badge-status-concluido { background:#dcfce7; color:#166534; }
        .btn-primary { background:linear-gradient(135deg,#2563eb,#1d4ed8); border:none; color:#f9fafb; border-radius:0.7rem; padding:0.55rem 1.3rem; font-size:0.8rem; font-weight:600; cursor:pointer; box-shadow:0 12px 22px rgba(37,99,235,0.4); }
        .btn-primary:hover { filter:brightness(1.04); }
        .btn-outline { border-radius:0.7rem; border:1px solid #d1d5db; padding:0.45rem 1.1rem; font-size:0.8rem; background:#ffffff; color:#374151; cursor:pointer; }
        .btn-outline:hover { background:#f3f4f6; }
        .empty-row { text-align:center; padding:1.25rem 0.5rem; color:#9ca3af; font-size:0.85rem; }
        .status-pill { display:inline-flex; align-items:center; gap:0.25rem; }
    </style>
</head>
<body>
<div class="layout">
    <header>
        <div class="title">PROTOCOLO WEB</div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-outline">Sair</button>
        </form>
    </header>

    <main>
        <div class="breadcrumb">/ Protocolo Web</div>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <div class="panel-title">Protocolos enviados</div>
                    <div class="panel-subtitle">Rastreamento de envios com valor jurídico.</div>
                </div>
                <a href="{{ route('protocolos.create') }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">Novo Protocolo</a>
            </div>

            <table>
                <thead>
                <tr>
                    <th style="width:32%;">Assunto</th>
                    <th>Empresa</th>
                    <th>Status</th>
                    <th>Criado em</th>
                    <th>Atualizado em</th>
                </tr>
                </thead>
                <tbody>
                @forelse($protocolos as $protocolo)
                    <tr>
                        <td>{{ $protocolo->assunto }}</td>
                        <td>{{ $protocolo->empresa?->razao_social ?? '-' }}</td>
                        <td>
                            <span class="status-pill">
                                @php($status = $protocolo->status)
                                <span class="badge badge-status-{{ $status }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </span>
                        </td>
                        <td>{{ $protocolo->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $protocolo->updated_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-row">Nenhum protocolo cadastrado até o momento.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>

