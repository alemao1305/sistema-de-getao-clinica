# Clínica Fabris - Sistema Web de Gestão Fisioterapêutica

Sistema web completo para gestão operacional, clínica, comercial e financeira de clínicas de fisioterapia, desenvolvido com **PHP 8.4**, **Laravel 11**, **Tailwind CSS**, **Alpine.js**, **Lucide Icons** e **Chart.js**.

---

## 🏥 Visão Geral & Funcionalidades

O sistema foi arquitetado para atender aos fluxos diários da proprietária, recepcionistas, fisioterapeutas e gestores financeiros, em total conformidade com a **LGPD** e as diretrizes do **COFFITO**:

1. **Painel Inicial (Dashboard Interativo)**:
   - Indicadores em tempo real: atendimentos do dia, taxa de ocupação, faturamento, sessões restantes de pacotes e despesas a vencer.
   - Gráficos de evolução financeira (receitas vs. despesas) e distribuição de atendimentos por fisioterapeuta.
   - Lista rápida de próximos pacientes do dia com atalhos de check-in e atendimento.

2. **Cadastro Completo de Pacientes**:
   - Dados demográficos, CPF com validação/máscara, contatos, ocupação e endereço com preenchimento via CEP.
   - Dados clínicos de alerta (hipertensão, diabetes, marcapasso, cirurgias prévias).
   - Termo de consentimento e privacidade **LGPD** registrado com data/hora.
   - Histórico consolidado do paciente: agendamentos, avaliações, evoluções, pacotes adquiridos e documentos anexados.

3. **Agenda Inteligente com Detecção de Conflitos**:
   - Modos de visualização: **Diário**, **Semanal** e **Lista**.
   - **Prevenção ativa de conflitos**: impede sobreposição de horários para o mesmo profissional, respeita capacidade máxima simultânea da sala/box e bloqueia horários com interdição cadastrada (reuniões, férias, manutenções).
   - Suporte a recorrências (semanal/quinzenal), remarcações e cancelamentos justificados.
   - Exportação de agenda no padrão **iCalendar (.ics)** para sincronização com Google Calendar e Outlook.

4. **Prontuário Eletrônico & Evoluções Fisioterapêuticas (SOAP)**:
   - **Avaliação Inicial**: anamnese completa, Escala Visual Analógica de Dor (EVA 0 a 10), goniometria/ADM, força muscular, testes ortopédicos especiais e diagnóstico cinético-funcional.
   - **Evoluções Diárias no Padrão SOAP**:
     - **S**ubjetivo: queixas e relato do paciente.
     - **O**bjetivo: condutas, exercícios, parâmetros eletroterápicos (TENS, ultrassom, etc.).
     - **A**valiação: resposta terapêutica imediata e reavaliação da dor.
     - **P**lano: orientações domiciliares e metas para a próxima sessão.
   - **Imutabilidade e Aditivos Justificados**: registros clínicos finalizados não podem ser editados no local ou excluídos silenciosamente. Retificações utilizam aditivos datados com justificativa ética obrigatória, preservando a integridade original.
   - Emissão de declarações de comparecimento formatadas para impressão.

5. **Comercial, Vendas & Pacotes de Sessões**:
   - Venda de procedimentos avulsos e pacotes promocionais (ex: 10 sessões de Traumato-Ortopedia).
   - **Pagamento Dividido (Split Payment)**: permite combinar múltiplas formas em uma única venda (ex: 50% Pix + 50% Cartão de Crédito parcelado).
   - **Gestão de Saldos de Pacotes**: controle automático de sessões contratadas, bônus, executadas e saldo restante.
   - **Extrato de Consumo**: histórico completo de débitos de sessão por atendimento ou ajustes manuais.

6. **Gestão Financeira Completa**:
   - **Contas a Receber**: geradas automaticamente a partir das vendas ou guias de convênios.
   - **Contas a Pagar**: controle de despesas operacionais da clínica (aluguel, descartáveis, contas públicas).
   - **Fluxo de Caixa**: conciliação em tempo real e saldo do período.
   - **Repasses Profissionais**: cálculo automatizado de comissões por atendimento executado (percentual ou fixo), com baixa de pagamento e comprovante.

7. **Convênios & Guias de Autorização (TISS)**:
   - Cadastro de operadoras de saúde credenciadas com registro ANS e prazos de faturamento.
   - Controle de guias de autorização com número da guia, carteirinha, sessões autorizadas vs. executadas, validade e controle de glosas.

8. **Relatórios Dinâmicos Configuráveis**:
   - Gerador dinâmico por módulo (Pacientes, Atendimentos, Financeiro, Vendas).
   - Seleção de colunas personalizadas e filtros por período e status.
   - Exportação em **CSV** para planilhas e **PDF formatado** para impressão limpa.

9. **Segurança, Auditoria & LGPD**:
   - Controle de Acesso Baseado em Perfis (RBAC): Proprietária, Recepção, Fisioterapeuta, Financeiro e Paciente.
   - Trilha de Auditoria imutável (`audit_logs`) com rastreamento de IP, usuário, data/hora e *diffs* JSON (valores anteriores vs. novos valores).
   - Bloqueio imediato de usuários desativados no middleware de autenticação.

---

## 👥 Credenciais Padrão de Acesso

O sistema é inicializado com as seguintes contas pré-configuradas para testes:

| Perfil | E-mail | Senha | Função / Acesso |
| :--- | :--- | :--- | :--- |
| **Proprietária / Admin** | `admin@clinicafabris.com.br` | `admin123` | Acesso irrestrito a todos os módulos, configurações e finanças |
| **Recepção** | `recepcao@clinicafabris.com.br` | `recepcao123` | Cadastro de pacientes, agendamentos, vendas e check-in |
| **Fisioterapeuta** | `fisioterapeuta@clinicafabris.com.br` | `fisio123` | Agenda própria, prontuários, avaliações, evoluções SOAP |
| **Financeiro** | `financeiro@clinicafabris.com.br` | `financeiro123` | Fluxo de caixa, contas a pagar/receber, repasses e vendas |

---

## 🚀 Como Executar o Sistema

### Pré-requisitos
- **Docker** e **Docker Compose** instalados no ambiente.
- As portas `8000`, `8080` e `3306` podem estar ocupadas por outros projetos; **a Clínica Fabris utiliza a porta 8085** (`APP_PORT=8085`).

### 1. Iniciar o Servidor Web via Docker Compose
Na pasta raiz do projeto:
```bash
docker compose up -d
```
O serviço iniciará o container `clinica_fabris_app` e a aplicação estará acessível em:
👉 **[http://localhost:8085](http://localhost:8085)**

Para acompanhar os logs de execução:
```bash
docker compose logs -f app
```

Para pausar o serviço:
```bash
docker compose down
```

---

## 🛠️ Scripts Utilitários CLI (Sem Dependência de PHP no Host)

O projeto inclui dois wrappers executáveis que utilizam a imagem oficial `php:8.4-cli-alpine` do Docker, garantindo que qualquer comando artisan ou composer seja executado diretamente:

### Executar comandos Artisan
```bash
# Executar migrações
./artisan-cli migrate

# Popular dados de demonstração
./artisan-cli db:seed --class=DemoSeeder

# Listar rotas
./artisan-cli route:list

# Limpar caches
./artisan-cli optimize:clear
```

### Executar a Suíte de Testes Automatizados
```bash
./artisan-cli test
```

### Executar comandos Composer
```bash
./composer-cli install
./composer-cli update
```

---

## 🧪 Testes Automatizados Implementados

O projeto possui suíte de testes com banco SQLite em memória (`:memory:`) cobrindo:
1. `AuthAndRoleTest`: Autenticação, bloqueio de usuários inativos e controle de acesso a dashboards.
2. `AgendaConflictTest`: Detecção de sobreposição de horários para fisioterapeutas e respeito a bloqueios administrativos.
3. `VendasAndPacotesTest`: Venda comercial com múltiplos pagamentos (Split), criação de saldo de sessões e débito automático ao concluir atendimentos.
4. `ProntuarioImmutabilityTest`: Registro de avaliação inicial com EVA, evolução diária SOAP e inclusão de aditivos retificadores com justificativa obrigatória.

---

## 🗄️ Rotina de Backup dos Dados

Os dados operacionais e anexos clínicos estão armazenados em:
- **Banco de Dados**: `database/database.sqlite`
- **Anexos Clínicos Protegidos**: `storage/app/documentos_pacientes/`

Para efetuar um backup manual a quente:
```bash
tar -czvf backup_clinica_fabris_$(date +%Y%m%d_%H%M%S).tar.gz \
    database/database.sqlite \
    storage/app/documentos_pacientes/ \
    .env
```

---

## ⚖️ Conformidade LGPD & Segurança

- **Consentimento**: Pacientes possuem registro explícito de consentimento e ciência do tratamento de dados de saúde.
- **Não Exclusão Oculta**: Registros médicos finalizados utilizam mecanismo de auditoria imutável e aditivos; exclusões são lógicas (*soft deletes*) com logs detalhados.
- **Proteção de Documentos**: Exames e laudos anexados são armazenados no disco local protegido (`storage/app/`) e servidos apenas por download autenticado mediante checagem de permissão.
