# Portal de Aplicativos - Sindicato Químicos Unificados

Sistema de gestão administrativa e de lazer, integrando controle de reservas para colônias de férias, gestão de usuários e protocolos.

## 🚀 Tecnologias Utilizadas

- **Backend:** Laravel 11.x (PHP 8.2+)
- **Frontend:** Blade, CSS Vanilla, JavaScript (Vite)
- **Banco de Dados:** MySQL / MariaDB
- **PDF:** DomPDF (`barryvdh/laravel-dompdf`)
- **Automação:** GitHub Actions

## 🛠️ Instalação Local (Desenvolvimento)

Para rodar o projeto localmente (ex: XAMPP), siga os passos:

1. **Clonar o Repositório:**
   ```bash
   git clone <url-do-repositorio>
   cd portaldeaplicativos/app
   ```

2. **Instalar Dependências:**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Configuração do Ambiente:**
   - Copie o arquivo `.env.example` para `.env`
   - Configure as credenciais do banco de dados (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
   - Gere a chave da aplicação:
     ```bash
     php artisan key:generate
     ```

4. **Migrações e Dados:**
   ```bash
   php artisan migrate
   ```

5. **Executar Servidor:**
   ```bash
   php artisan serve
   ```

---

## 🌐 Deploy (VPS)

O projeto está configurado para **Deploy Automático** via GitHub Actions sempre que houver um `push` na branch `master`.

### Configurações da VPS:
- **IP do Servidor:** `191.252.205.40`
- **Usuário:** `root`
- **Script de Deploy:** `/var/www/deploy.sh`

### Fluxo de CI/CD:
O arquivo de workflow encontra-se em `.github/workflows/main.yml`. Ele utiliza as seguintes **Secrets** do GitHub:
- `SSH_PRIVATE_KEY`: Chave privada para acesso ao servidor.

Para realizar o deploy manualmente, acesse o servidor via SSH e execute o script:
```bash
ssh root@191.252.205.40
/var/www/deploy.sh
```

---

## 📁 Estrutura de Módulos Recentes

- **Agenda Colônia:** Gestão de períodos e reservas.
- **Módulo de Impressos:** Geração de Guias de Pré-Reserva (2 por folha A4) e Lista de Inscritos em PDF.
- **Histórico de Exclusões:** Auditoria de reservas removidas com registro de motivo.

## 📄 Licença

Este projeto é de uso exclusivo do **Sindicato Químicos Unificados**.
