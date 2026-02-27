<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Portal de Aplicativos</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <style>
        body { margin: 0; font-family: Figtree, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f3f4f6; }
        .page { min-height: 100vh; display: flex; flex-direction: column; }
        header { background: #111827; color: #f9fafb; padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; }
        .title { font-size: 1.1rem; font-weight: 600; }
        .content { flex: 1; padding: 1.75rem 2rem; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .card { border-radius: 0.75rem; padding: 1.1rem 1.25rem; color: #ffffff; display: flex; flex-direction: column; gap: 0.25rem; box-shadow: 0 14px 28px rgba(15, 23, 42, 0.24); }
        .card-blue { background: linear-gradient(135deg, #1d4ed8, #2563eb); }
        .card-green { background: linear-gradient(135deg, #16a34a, #22c55e); }
        .card-cyan { background: linear-gradient(135deg, #0891b2, #06b6d4); }
        .card-yellow { background: linear-gradient(135deg, #d97706, #f59e0b); }
        .card-label { font-size: 0.85rem; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.05em; }
        .card-value { font-size: 1.4rem; font-weight: 700; }
        .section-title { font-size: 1rem; font-weight: 600; color: #111827; margin-bottom: 0.75rem; }
        .placeholder { border-radius: 0.75rem; background: #ffffff; padding: 1.25rem 1.5rem; box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08); font-size: 0.9rem; color: #4b5563; }
        .logout-form { margin: 0; }
        .btn-logout { background: transparent; border-radius: 9999px; border: 1px solid rgba(249,250,251,0.35); color: #e5e7eb; padding: 0.35rem 0.9rem; font-size: 0.8rem; font-weight: 500; cursor: pointer; }
        .btn-logout:hover { background: rgba(15,23,42,0.6); }
    </style>
</head>
<body>
<div class="page">
    <header>
        <div class="title">Portal de Aplicativos Sindicais</div>
        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit" class="btn-logout">Sair</button>
        </form>
    </header>

    <main class="content">
        <section class="cards">
            <article class="card card-blue">
                <span class="card-label">Eventos</span>
                <span class="card-value">0</span>
            </article>
            <article class="card card-green">
                <span class="card-label">Convites</span>
                <span class="card-value">0</span>
            </article>
            <article class="card card-cyan">
                <span class="card-label">Convidados</span>
                <span class="card-value">0</span>
            </article>
            <article class="card card-yellow">
                <span class="card-label">Arrecadado</span>
                <span class="card-value">R$ 0,00</span>
            </article>
        </section>

        <section>
            <h2 class="section-title">Bem-vindo ao painel</h2>
            <div class="placeholder">
                Esta é a área inicial do Portal. A partir daqui serão acessados os módulos de
                <strong>Eventos / Convites</strong>, <strong>Protocolo Web</strong> e demais aplicativos
                definidos no plano.
            </div>
        </section>
    </main>
</div>
</body>
</html>

