# Portal de Aplicativos Sindicais

Plano de desenvolvimento de uma central de aplicativos multiplataforma baseada em NativePHP, com módulos independentes (microserviços lógicos) compartilhando a mesma base de dados e governança única de identidade, segurança e auditoria.

## 1) Visão estratégica

### Objetivo
Construir uma plataforma única para operação sindical com foco em:
- gestão de eventos e convites;
- protocolo web com envio autenticado de e-mails de valor jurídico;
- base cadastral centralizada (empresas, clientes, tipos e usuários);
- expansão futura para agenda e reservas da Colônia de Férias.

### Princípios de arquitetura
- **Modularidade por domínio**: cada módulo evolui de forma independente.
- **Base de dados compartilhada com isolamento lógico**: schema único com regras claras de ownership por tabela.
- **Interoperabilidade por APIs internas**: integração entre módulos por serviços e filas.
- **Rastreabilidade jurídica ponta a ponta**: logs imutáveis, trilha de auditoria e integração com carimbo de tempo ICP-Brasil, seguindo o manual da API AR Online (https://docs.ar-online.com.br/).
- **Experiência multiplataforma**: web responsiva + empacotamento desktop via NativePHP.

## 2) Arquitetura proposta

### Camadas
1. **Aplicação (NativePHP + Laravel)**
   - front-end administrativo web;
   - empacotamento desktop para operação interna;
   - APIs REST para consumo interno e integrações.
2. **Domínio (módulos)**
   - Eventos;
   - Convites e Vendas;
   - Protocolo Web;
   - Cadastros Comuns;
   - IAM (identidade e acesso).
3. **Infraestrutura**
   - banco relacional (MySQL/MariaDB recomendado);
   - fila/worker para envios e integrações;
   - armazenamento de anexos e comprovantes;
   - observabilidade (logs, métricas, alertas).

### Estratégia de microserviços com base compartilhada
- Iniciar como **modular monolith** (mais simples para governança inicial).
- Definir contratos entre módulos desde o início (DTOs/eventos de domínio).
- Quando necessário, extrair módulos críticos para serviços separados, mantendo acesso controlado à base comum por schema/tabela.

## 3) Módulos e escopo funcional

### Módulo A — Cadastros Comuns
- Cadastro de empresas;
- Cadastro de clientes;
- Tipos de clientes;
- Validação de dados (CPF/CNPJ, e-mail, telefone, status, região/categoria);
- Versionamento de alterações sensíveis (auditoria).

### Módulo B — Usuários e Segurança (IAM)
- Autenticação;
- Recuperação e reset de senha;
- Sessão e revogação de sessão;
- Perfis de acesso por aplicativo/módulo;
- Permissões granulares (RBAC);
- 2FA opcional para funções críticas;
- Política de senha e trilha de login.

### Módulo C — Eventos
- Cadastro de eventos;
- Cadastro de convites;
- Vendas de convites (inteira/meia, lotes, status);
- Gestão de convidados vinculados a convites;
- Relatórios operacionais e financeiros (arrecadação, presença, inadimplência).

### Módulo D — Protocolo Web
- Formulário de envio;
- Seleção de empresa/contatos;
- Upload de anexos;
- Integração com API de mensageria/autenticação jurídica;
- Relatório de envios com status (enviado, entregue, lido, falha);
- Reprocessamento de falhas.

### Módulo E — Agendas Colônia de Férias (futuro)
- Agenda de disponibilidade;
- Solicitação de reserva;
- Fluxo de aprovação;
- Cobrança e confirmação;
- Relatórios de ocupação.

## 4) Modelo de dados (macro)

### Tabelas centrais sugeridas
- `empresas`
- `clientes`
- `tipos_clientes`
- `usuarios`
- `perfis`
- `permissoes`
- `usuario_perfil`
- `perfil_permissao`
- `sessoes`
- `auditoria_eventos`

### Eventos e convites
- `eventos`
- `convites`
- `convidados`
- `vendas_convite`
- `lotes_convite`

### Protocolo jurídico
- `protocolos`
- `protocolo_destinatarios`
- `protocolo_anexos`
- `protocolo_envios`
- `protocolo_comprovantes`
- `carimbos_tempo`

## 5) Requisitos de validade jurídica (ICP-Brasil)

1. Assinatura/autenticação forte do remetente institucional.
2. Geração de hash dos documentos e metadados de envio.
3. Integração com Autoridade de Carimbo do Tempo (ACT) credenciada ICP-Brasil.
4. Armazenamento de token de carimbo + cadeia de validação.
5. Emissão de comprovante verificável (PDF/URL) com:
   - remetente;
   - destinatário;
   - hash do conteúdo;
   - data/hora oficial;
   - identificador único do protocolo.
6. Trilha de auditoria imutável e retenção conforme política jurídica.

## 6) Roadmap de implantação

### Fase 0 — Descoberta (2 a 3 semanas)
- Levantamento de regras de negócio por área;
- Inventário de planilhas e sistemas legados;
- Mapa de integrações externas;
- Definição de indicadores de sucesso (KPIs).

### Fase 1 — Fundação técnica (3 a 5 semanas)
- Setup NativePHP/Laravel;
- Estrutura modular;
- IAM completo;
- Cadastros comuns;
- Auditoria e observabilidade base.

### Fase 2 — Eventos e convites (4 a 6 semanas)
- Cadastro de eventos e convites;
- Fluxo de venda e convidados;
- Relatórios de arrecadação e operação;
- Importação inicial de dados legados.

### Fase 3 — Protocolo web jurídico (5 a 7 semanas)
- Formulário e fila de envio;
- Integração com API de envio;
- Integração de carimbo de tempo ICP-Brasil;
- Relatórios de rastreabilidade e comprovantes.

### Fase 4 — Hardening e rollout (2 a 4 semanas)
- Testes de carga e segurança;
- Treinamento de usuários-chave;
- Go-live assistido;
- Plano de continuidade e suporte.

### Fase 5 — Colônia de Férias (backlog priorizado)
- Descoberta específica;
- prototipação;
- entrega incremental.

## 7) Governança, qualidade e segurança

- **LGPD**: minimização de dados, base legal, consentimento quando aplicável e política de retenção.
- **Segurança**: criptografia em trânsito/repouso, gestão de segredos e segregação de ambientes.
- **Qualidade**: testes unitários, integração, contrato e E2E.
- **Operação**: CI/CD com validações automáticas, rollback e feature flags.
- **Auditoria**: logs de acesso, alteração e envio jurídico com carimbo temporal.

## 8) Backlog inicial priorizado (MVP)

1. IAM (login, recovery/reset, sessão, permissões por módulo).
2. Cadastros de empresas, clientes e tipos.
3. Eventos: cadastro e listagem.
4. Convites: emissão, venda e convidados.
5. Relatórios básicos de eventos/convites.
6. Protocolo web: envio + status + relatório.
7. Integração de comprovante com carimbo de tempo ICP-Brasil.

## 9) Indicadores recomendados

- Taxa de sucesso de envios de protocolo.
- Tempo médio de processamento por envio.
- Percentual de leitura/entrega por empresa.
- Receita por evento e taxa de ocupação por convite.
- Tempo de atendimento/execução de solicitações operacionais.

## 10) Próximos passos imediatos

1. Validar este plano com diretoria e jurídico.
2. Definir fornecedor/parceiro ACT ICP-Brasil para carimbo de tempo.
3. Priorizar backlog MVP com responsáveis e prazos.
4. Iniciar Fase 0 com workshop de requisitos e modelagem de dados detalhada.
