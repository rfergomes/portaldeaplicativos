<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Controle de Eventos</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>
        body { margin:0; font-family:Figtree, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background:#e5e7eb; }
        .layout { min-height:100vh; display:flex; flex-direction:column; }
        header { background:#111827; color:#f9fafb; padding:0.75rem 2.25rem; display:flex; align-items:center; justify-content:space-between; }
        .header-left { display:flex; align-items:center; gap:0.75rem; }
        .app-title { font-size:1.1rem; font-weight:600; letter-spacing:0.04em; text-transform:uppercase; }
        main { flex:1; padding:1.5rem 2.25rem 2.5rem; }
        .breadcrumb { font-size:0.8rem; color:#9ca3af; margin-bottom:0.75rem; }
        .cards { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1.1rem; margin-bottom:1.5rem; }
        .card { border-radius:0.9rem; padding:0.95rem 1.1rem; color:#f9fafb; box-shadow:0 18px 34px rgba(15,23,42,0.35); display:flex; flex-direction:column; gap:0.25rem; }
        .card-blue { background:linear-gradient(135deg,#1d4ed8,#2563eb); }
        .card-green { background:linear-gradient(135deg,#16a34a,#22c55e); }
        .card-cyan { background:linear-gradient(135deg,#0891b2,#06b6d4); }
        .card-yellow { background:linear-gradient(135deg,#d97706,#f59e0b); }
        .card-label { font-size:0.85rem; opacity:0.9; display:flex; align-items:center; justify-content:space-between; }
        .card-value { font-size:1.6rem; font-weight:700; }
        .card-caption { font-size:0.75rem; opacity:0.85; }
        .panel { border-radius:0.9rem; background:#f9fafb; box-shadow:0 16px 30px rgba(15,23,42,0.16); padding:1.25rem 1.5rem; }
        .panel-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; }
        .panel-title { font-size:1rem; font-weight:600; color:#111827; }
        .tabs { display:inline-flex; border-radius:999px; background:#e5e7eb; padding:0.15rem; }
        .tab { font-size:0.8rem; padding:0.4rem 0.9rem; border-radius:999px; cursor:pointer; border:none; background:transparent; color:#4b5563; }
        .tab-active { background:#16a34a; color:#ecfdf5; font-weight:600; }
        table { width:100%; border-collapse:collapse; font-size:0.85rem; }
        th, td { padding:0.6rem 0.5rem; text-align:left; }
        th { font-size:0.8rem; text-transform:uppercase; letter-spacing:0.05em; color:#9ca3af; border-bottom:1px solid #e5e7eb; }
        tbody tr:nth-child(odd) { background:#f9fafb; }
        tbody tr:nth-child(even) { background:#eef2ff; }
        .empty-row { text-align:center; padding:1.25rem 0.5rem; color:#9ca3af; font-size:0.85rem; }
        .btn-primary { background:linear-gradient(135deg,#2563eb,#1d4ed8); border:none; color:#f9fafb; border-radius:0.7rem; padding:0.55rem 1.1rem; font-size:0.8rem; font-weight:600; cursor:pointer; box-shadow:0 12px 22px rgba(37,99,235,0.45); }
        .btn-primary:hover { filter:brightness(1.03); }
        .actions { display:flex; gap:0.35rem; }
        .badge { border-radius:999px; padding:0.15rem 0.6rem; font-size:0.7rem; font-weight:500; }
        .badge-open { background:#dcfce7; color:#166534; }
        .badge-closed { background:#fee2e2; color:#b91c1c; }
        .footer { margin-top:1.25rem; font-size:0.75rem; color:#9ca3af; display:flex; justify-content:space-between; }
        .modal-backdrop { position:fixed; inset:0; background:rgba(15,23,42,0.45); display:flex; align-items:center; justify-content:center; }
        .modal { max-width:480px; width:100%; background:#ffffff; border-radius:0.9rem; box-shadow:0 26px 52px rgba(15,23,42,0.55); padding:1.4rem 1.7rem 1.5rem; }
        .modal-title { font-size:1rem; font-weight:600; margin-bottom:0.25rem; color:#111827; }
        .modal-subtitle { font-size:0.8rem; color:#6b7280; margin-bottom:0.9rem; }
        .field { margin-bottom:0.8rem; }
        .label { display:block; font-size:0.8rem; font-weight:500; color:#374151; margin-bottom:0.15rem; }
        .input { width:100%; border-radius:0.5rem; border:1px solid #d1d5db; padding:0.55rem 0.7rem; font-size:0.85rem; }
        .input:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 1px rgba(37,99,235,0.3); }
        .modal-actions { display:flex; justify-content:flex-end; gap:0.4rem; margin-top:0.9rem; }
        .btn-secondary { border-radius:0.7rem; border:1px solid #d1d5db; padding:0.5rem 1.1rem; font-size:0.8rem; background:#ffffff; color:#374151; cursor:pointer; }
        .btn-secondary:hover { background:#f3f4f6; }
        .btn-small { font-size:0.75rem; padding:0.35rem 0.7rem; border-radius:999px; border:none; cursor:pointer; }
        .btn-outline { border:1px solid #d1d5db; background:#ffffff; color:#374151; }
        .btn-outline:hover { background:#e5e7eb; }
    </style>
</head>
<body>
<div class="layout">
    <header>
        <div class="header-left">
            <div class="app-title">CONTROLE DE EVENTOS</div>
        </div>
        <div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-small btn-outline">Sair</button>
            </form>
        </div>
    </header>

    <main>
        <div class="breadcrumb">/ Dashboard / Controle de Eventos</div>

        <section class="cards">
            <article class="card card-blue">
                <div class="card-label">
                    <span>Eventos</span>
                </div>
                <div class="card-value">{{ $totalEventos }}</div>
                <div class="card-caption">Eventos cadastrados</div>
            </article>

            <article class="card card-green">
                <div class="card-label">
                    <span>Convites</span>
                </div>
                <div class="card-value">{{ $totalConvites }}</div>
                <div class="card-caption">Convites emitidos</div>
            </article>

            <article class="card card-cyan">
                <div class="card-label">
                    <span>Convidados</span>
                </div>
                <div class="card-value">{{ $totalConvidados }}</div>
                <div class="card-caption">Convidados vinculados</div>
            </article>

            <article class="card card-yellow">
                <div class="card-label">
                    <span>Arrecadado</span>
                </div>
                <div class="card-value">R$ {{ number_format($totalArrecadado, 2, ',', '.') }}</div>
                <div class="card-caption">Receita total em eventos</div>
            </article>
        </section>

        <section class="panel">
            <div class="panel-header">
                <h2 class="panel-title">Lista de Eventos</h2>
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div class="tabs">
                        <button type="button" class="tab tab-active">Abertos</button>
                        <button type="button" class="tab">Encerrados</button>
                    </div>
                    <button type="button" class="btn-primary" onclick="document.getElementById('novo-evento-modal').style.display='flex'">
                        Novo Evento
                    </button>
                </div>
            </div>

            <div>
                <table>
                    <thead>
                    <tr>
                        <th style="width:26%;">Evento</th>
                        <th>Data</th>
                        <th>Local</th>
                        <th>Convites</th>
                        <th>Convidados</th>
                        <th>Arrecadado</th>
                        <th style="width:120px;">Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($eventosAbertos as $evento)
                        <tr>
                            <td>
                                <div style="font-weight:600; color:#111827;">{{ $evento->nome }}</div>
                                <div>
                                    <span class="badge badge-open">Aberto</span>
                                </div>
                            </td>
                            <td>{{ optional($evento->data_inicio)->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $evento->local ?? '-' }}</td>
                            <td>{{ $evento->convites()->count() }}</td>
                            <td>{{ $evento->convidados()->count() }}</td>
                            <td>R$ {{ number_format($evento->vendas()->sum('valor_venda'), 2, ',', '.') }}</td>
                            <td>
                                <div class="actions">
                                    <button class="btn-small btn-outline">Convidados</button>
                                    <button class="btn-small btn-outline">Relatório</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-row">Nenhum evento encontrado</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="footer">
                <span>© TI Químicos Unificados • 2024 – 2026</span>
                <span></span>
            </div>
        </section>
    </main>
</div>

<div id="novo-evento-modal" class="modal-backdrop" style="display:none;">
    <div class="modal">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
            <div>
                <div class="modal-title">Adicionar Evento</div>
                <div class="modal-subtitle">Preencha os dados principais do evento.</div>
            </div>
            <button type="button" onclick="document.getElementById('novo-evento-modal').style.display='none'" class="btn-small btn-outline">X</button>
        </div>

        <form method="POST" action="{{ route('eventos.store') }}">
            @csrf

            <div class="field">
                <label class="label" for="nome">Nome do Evento</label>
                <input id="nome" name="nome" class="input" value="{{ old('nome') }}" required>
            </div>

            <div class="field">
                <label class="label" for="data">Data</label>
                <input id="data" name="data" type="datetime-local" class="input" value="{{ old('data') }}">
            </div>

            <div class="field">
                <label class="label" for="local">Local</label>
                <input id="local" name="local" class="input" value="{{ old('local') }}">
            </div>

            <div class="field">
                <label class="label" for="valor_inteira">Valor (inteira)</label>
                <input id="valor_inteira" name="valor_inteira" type="number" step="0.01" min="0" class="input" value="{{ old('valor_inteira') }}">
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="document.getElementById('novo-evento-modal').style.display='none'">Cancelar</button>
                <button type="submit" class="btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>

