<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Portal de Aplicativos</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <style>
        body { margin: 0; font-family: Figtree, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f3f4f6; }
        .page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
        .card { max-width: 420px; width: 100%; background: #ffffff; border-radius: 0.75rem; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12); padding: 2rem 2.25rem; }
        .title { font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 0.25rem; }
        .subtitle { font-size: 0.9rem; color: #6b7280; margin-bottom: 1.75rem; }
        .label { font-size: 0.85rem; font-weight: 600; color: #374151; display: block; margin-bottom: 0.35rem; }
        .input { width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.75rem 0.9rem; font-size: 0.9rem; outline: none; transition: border-color 0.15s, box-shadow 0.15s; }
        .input:focus { border-color: #2563eb; box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.35); }
        .error-text { font-size: 0.8rem; color: #b91c1c; margin-top: 0.35rem; }
        .field { margin-bottom: 1rem; }
        .actions { display: flex; align-items: center; justify-content: space-between; margin-top: 1.25rem; }
        .checkbox-label { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #4b5563; }
        .checkbox { width: 1rem; height: 1rem; border-radius: 0.25rem; border: 1px solid #9ca3af; }
        .btn-primary { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #ffffff; border: none; border-radius: 0.5rem; padding: 0.7rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3); transition: transform 0.12s, box-shadow 0.12s, filter 0.12s; }
        .btn-primary:hover { filter: brightness(1.05); box-shadow: 0 14px 28px rgba(37, 99, 235, 0.4); transform: translateY(-1px); }
        .footer { margin-top: 1.75rem; font-size: 0.8rem; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="page">
        <div class="card">
            <div style="margin-bottom: 1.5rem;">
                <div class="title">Portal de Aplicativos</div>
                <div class="subtitle">Acesse com suas credenciais de usuário sindical.</div>
            </div>

            @if ($errors->any())
                <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:0.75rem;padding:0.75rem 0.9rem;font-size:0.85rem;margin-bottom:1rem;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ url('/login') }}">
                @csrf

                <div class="field">
                    <label class="label" for="email">E-mail</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="input">
                </div>

                <div class="field">
                    <label class="label" for="password">Senha</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="input">
                </div>

                <div class="actions">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" class="checkbox">
                        <span>Lembrar sessão</span>
                    </label>

                    <button type="submit" class="btn-primary">
                        <span>Entrar</span>
                    </button>
                </div>
            </form>

            <div class="footer">
                © {{ date('Y') }} Portal de Aplicativos Sindicais
            </div>
        </div>
    </div>
</body>
</html>

