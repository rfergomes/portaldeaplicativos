<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .password-box {
            background: #f4f4f4;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            font-weight: bold;
            font-size: 1.2em;
            border: 1px dashed #bbb;
            margin: 20px 0;
        }

        .footer {
            font-size: 0.8em;
            color: #777;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Bem-vindo, {{ $user->name }}!</h1>
            <p>Sua conta no <strong>Portal de Aplicativos</strong> foi criada com sucesso.</p>
        </div>

        <p>Olá,</p>
        <p>Você foi cadastrado como novo usuário. Abaixo estão seus dados de acesso temporários:</p>

        <ul>
            <li><strong>E-mail:</strong> {{ $user->email }}</li>
        </ul>

        <div class="password-box">
            Senha Temporária: {{ $temporaryPassword }}
        </div>

        <p><strong>Importante:</strong> Para sua segurança, será solicitado que você altere esta senha no seu primeiro
            acesso.</p>

        <div style="text-align: center; margin-top: 40px;">
            <a href="{{ url('/login') }}" class="btn">Acessar o Sistema</a>
        </div>

        <div class="footer">
            <p>Este é um e-mail automático, por favor não responda.</p>
            <p>&copy; {{ date('Y') }} Portal de Aplicativos - Todos os direitos reservados.</p>
        </div>
    </div>
</body>

</html>