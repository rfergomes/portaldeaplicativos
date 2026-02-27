<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Novo Protocolo Web</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>
        body { margin:0; font-family:Figtree, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background:#e5e7eb; }
        .layout { min-height:100vh; display:flex; flex-direction:column; }
        header { background:#111827; color:#f9fafb; padding:0.75rem 2.25rem; display:flex; align-items:center; justify-content:space-between; }
        .title { font-size:1.05rem; font-weight:600; letter-spacing:0.04em; text-transform:uppercase; }
        main { flex:1; padding:1.5rem 2.25rem 2.25rem; }
        .breadcrumb { font-size:0.8rem; color:#9ca3af; margin-bottom:0.75rem; }
        .panel { border-radius:0.9rem; background:#f9fafb; box-shadow:0 16px 30px rgba(15,23,42,0.18); padding:1.4rem 1.6rem; max-width:960px; }
        .panel-title { font-size:1rem; font-weight:600; color:#111827; margin-bottom:0.5rem; }
        .panel-subtitle { font-size:0.8rem; color:#6b7280; margin-bottom:1.2rem; }
        .grid-2 { display:grid; grid-template-columns:2fr 1fr; gap:1.25rem; }
        .field { margin-bottom:0.75rem; }
        .label { display:block; font-size:0.8rem; font-weight:500; color:#374151; margin-bottom:0.2rem; }
        .input, select, textarea { width:100%; border-radius:0.5rem; border:1px solid #d1d5db; padding:0.55rem 0.7rem; font-size:0.85rem; }
        textarea { min-height:180px; resize:vertical; }
        .input:focus, select:focus, textarea:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 1px rgba(37,99,235,0.28); }
        .chips { display:flex; flex-wrap:wrap; gap:0.35rem; margin-top:0.35rem; }
        .chip { border-radius:999px; border:1px dashed #d1d5db; padding:0.25rem 0.5rem; font-size:0.75rem; color:#6b7280; }
        .btn-primary { background:linear-gradient(135deg,#2563eb,#1d4ed8); border:none; color:#f9fafb; border-radius:0.7rem; padding:0.6rem 1.4rem; font-size:0.85rem; font-weight:600; cursor:pointer; box-shadow:0 12px 22px rgba(37,99,235,0.4); }
        .btn-primary:hover { filter:brightness(1.04); }
        .btn-outline { border-radius:0.7rem; border:1px solid #d1d5db; padding:0.5rem 1.2rem; font-size:0.8rem; background:#ffffff; color:#374151; cursor:pointer; }
        .btn-outline:hover { background:#f3f4f6; }
        .actions { display:flex; justify-content:flex-end; gap:0.5rem; margin-top:1.1rem; }
        .error-box { background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; border-radius:0.75rem; padding:0.6rem 0.8rem; font-size:0.8rem; margin-bottom:0.9rem; }
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
        <div class="breadcrumb">/ Protocolo Web / Novo</div>

        <section class="panel">
            <div class="panel-title">Novo Protocolo</div>
            <div class="panel-subtitle">Defina os dados da empresa, destinatários, conteúdo e anexos para envio com valor jurídico.</div>

            @if ($errors->any())
                <div class="error-box">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('protocolos.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid-2">
                    <div>
                        <div class="field">
                            <label class="label" for="empresa_id">Empresa</label>
                            <select id="empresa_id" name="empresa_id" class="input">
                                <option value="">Selecione...</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" @selected(old('empresa_id') == $empresa->id)>
                                        {{ $empresa->razao_social }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label class="label" for="assunto">Assunto</label>
                            <input id="assunto" name="assunto" class="input" value="{{ old('assunto') }}" required>
                        </div>

                        <div class="field">
                            <label class="label" for="corpo">Mensagem</label>
                            <textarea id="corpo" name="corpo" required>{{ old('corpo') }}</textarea>
                        </div>
                    </div>

                    <div>
                        <div class="field">
                            <label class="label">Destinatários</label>
                            <input class="input" name="destinatarios[0][nome]" placeholder="Nome do destinatário" value="{{ old('destinatarios.0.nome') }}" required style="margin-bottom:0.4rem;">
                            <input class="input" name="destinatarios[0][email]" placeholder="E-mail do destinatário" value="{{ old('destinatarios.0.email') }}" required>
                            <div class="chips">
                                <span class="chip">Suporte a múltiplos destinatários pode ser adicionado depois.</span>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label" for="anexos">Anexos</label>
                            <input id="anexos" name="anexos[]" type="file" multiple class="input">
                            <div class="chips">
                                <span class="chip">PDF</span>
                                <span class="chip">Imagens</span>
                                <span class="chip">Outros formatos permitidos pela política interna</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ route('protocolos.index') }}" class="btn-outline" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">Cancelar</a>
                    <button type="submit" class="btn-primary">Enviar Protocolo</button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>

