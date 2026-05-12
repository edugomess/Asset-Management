# Documentação Completa do Sistema: Asset Management & Help Desk

## 1. Visão Geral do Sistema
O **Asset Management** é uma solução integrada desenvolvida em PHP e MySQL para a gestão eficiente de ativos de TI (equipamentos e licenças) e atendimento de suporte (Help Desk). O sistema conta com um design moderno ("Premium Design", Glassmorphism), suporte multi-idioma, integração com Inteligência Artificial (Gemini) e notificações em tempo real.

---

## 2. Níveis de Usuário e Permissões
O sistema opera com um controle de acesso baseado em três perfis principais:

1. **Administrador (Admin)**
   - **Permissões Globais:** Acesso total a todas as funcionalidades do sistema.
   - **Gestão:** Pode gerenciar usuários, configurações do sistema (SLAs, SMTP, APIs de IA e WhatsApp).
   - **Relatórios:** Acesso irrestrito a todos os relatórios gerenciais e financeiros.

2. **Técnico / Suporte (Suporte)**
   - **Atendimento:** Pode visualizar, atribuir, interagir e fechar qualquer chamado de suporte.
   - **Gestão de Ativos:** Pode cadastrar, editar, atribuir e realizar manutenção em equipamentos e licenças.
   - **Base de Conhecimento:** Criação e edição de artigos na KB.
   - **Restrições:** Não tem acesso a configurações de alto nível do sistema ou deleção de usuários administrativos.

3. **Usuário Comum (Usuário)**
   - **Help Desk:** Pode abrir chamados para si mesmo e interagir (responder, anexar arquivos) nos seus próprios chamados.
   - **Ativos:** Visualiza apenas os equipamentos que estão atribuídos ao seu nome.
   - **Consulta:** Acesso de leitura à Base de Conhecimento (KB) e edição do próprio perfil.

---

## 3. Requisitos do Sistema

### 3.1 Requisitos Funcionais (RF)
- **RF01:** O sistema deve autenticar usuários utilizando E-mail e Senha.
- **RF02:** O sistema deve permitir o cadastro (CRUD) de Ativos físicos, incluindo especificações de hardware, status e atribuição.
- **RF03:** O sistema deve gerenciar Licenças de software e controlar suas datas de expiração e limites de uso.
- **RF04:** O sistema deve permitir a abertura de Chamados com categorias, prioridades e upload de anexos (máx. 5MB).
- **RF05:** O sistema deve calcular automaticamente o tempo de SLA (Service Level Agreement) para primeira resposta e resolução de chamados.
- **RF06:** O sistema deve permitir comunicação interna em tempo real através de um módulo de Chat Interno.
- **RF07:** O sistema deve enviar alertas automatizados (E-mail e WhatsApp) para chamados criados, atualizados e licenças próximas ao vencimento.
- **RF08:** O sistema deve gerar relatórios parametrizados em formato PDF.
- **RF09:** O sistema deve possuir uma Base de Conhecimento estruturada em categorias para consultas.
- **RF10:** O sistema deve fornecer insights e assistente virtual utilizando integração com Inteligência Artificial.

### 3.2 Requisitos Não-Funcionais (RNF)
- **RNF01 (Desempenho):** O envio de notificações e e-mails deve ser assíncrono para não travar a navegação do usuário.
- **RNF02 (Usabilidade):** A interface deve ser responsiva e utilizar padrões de design modernos (Glassmorphism, transições suaves).
- **RNF03 (Segurança):** O sistema deve prevenir ataques de SQL Injection utilizando escape de strings e hashing de senhas.
- **RNF04 (Portabilidade):** O sistema deve suportar internacionalização (arquivos de tradução em `/lang`).
- **RNF05 (Compatibilidade):** O sistema deve ser compatível com os principais navegadores modernos (Chrome, Firefox, Edge, Safari).

---

## 4. Estrutura de Funcionalidades (Módulos)

### 4.1 Dashboard
- **Métricas em tempo real:** Chamados Abertos/Pendentes, SLA de primeira resposta, e Ranking de SLA por técnico.
- **Resumo de Ativos:** Gráficos mostrando o status dos ativos (Em uso, Disponíveis, Manutenção).
- **Alertas:** Avisos críticos na interface (ex: licenças a vencer).

### 4.2 Gestão de Ativos (Equipamentos)
- **Cadastro Detalhado:** Tipo (Desktop, Smartphone, etc.), configuração de hardware (CPU, RAM, Armazenamento), tag, número de série.
- **Histórico:** Rastreabilidade completa de movimentações, manutenção e atribuições do ativo.
- **Ciclo de Vida:** Atribuição a usuários, devolução, envio para manutenção, doação e leilão (venda).
- **Geração de Etiquetas:** Impressão de etiquetas com QR Code para facilitar auditoria.

### 4.3 Gestão de Licenças
- **Controle de Chaves:** Registro do fornecedor, tipo de licença, e custo.
- **Atribuição:** Vínculo de licenças a dispositivos específicos ou a usuários.
- **Auditoria:** Cálculo de licenças disponíveis vs. licenças em uso.

### 4.4 Help Desk (Chamados)
- **Fluxo de Trabalho:** Aberto > Em Andamento > Pendente > Resolvido / Cancelado.
- **SLAs:** Regras de negócio configuráveis para tempos de resposta e resolução com base na categoria e prioridade.
- **Gestão de Filas:** Atribuição de chamados para membros específicos do suporte.
- **Anexos:** Suporte a imagens e PDFs.

### 4.5 Base de Conhecimento (KB)
- **Gestão de Artigos:** Criação com editor rico (Summernote).
- **Feedback:** Sistema de classificação (Útil / Não Útil) para avaliar a eficácia dos artigos.

### 4.6 Estrutura Organizacional
- **Centro de Custo:** Mapeamento de departamentos e setores da empresa.
- **Locais:** Registro de localizações físicas, capacidade e tipo.
- **Fornecedores:** Diretório de contato com prestadores de serviço e vendedores.

### 4.7 Administração e Configurações
- **Configurações Gerais:** Definição de limites de SLA, integração de API (Gemini, Twilio/Wazapi).
- **SMTP:** Configuração do gateway de e-mail.
- **Importação:** Módulo para importação massiva de dados via CSV.

---

## 5. Dicionário de Dados (Principais Tabelas)

*Nota: Abaixo as tabelas principais e seus propósitos extraídos do código fonte.*

| Tabela | Descrição Principal |
| :--- | :--- |
| **`usuarios`** | Armazena dados dos usuários, senhas (hash), nível de acesso (`Admin`, `Suporte`, `Usuário`), departamento e status do chat. |
| **`ativos`** | Catálogo de todos os hardwares físicos. Contém dados de especificação técnica, status de uso, ID do usuário/local e valor de aquisição. |
| **`chamados`** | Registro do Help Desk. Mantém o título, descrição, status, tempos de abertura/fechamento (SLA) e IDs dos usuários envolvidos. |
| **`licencas`** | Registro de softwares adquiridos. Controla volume, chaves de ativação, e datas de expiração. |
| **`manutencao`** | Histórico e controle de hardware enviado para assistência técnica ou conserto interno. |
| **`historico_ativos`** | Trilha de auditoria das movimentações (ex: "Atribuído a João", "Enviado para Manutenção"). |
| **`atribuicoes`** / **`atribuicoes_licencas`** | Tabelas de relação N:N vinculando ativos e licenças a usuários finais. |
| **`centro_de_custo`** | Estrutura departamental da empresa para relatórios de alocação de custos. |
| **`fornecedor`** | Dados das empresas que prestam serviços ou vendem ativos e licenças. |
| **`venda`** | Registro de ativos que saíram da operação, seja por venda (leilão) ou doação. |
| **`locais`** | Dados de geolocalização física ou salas (ex: Filial SP, Sala de Servidores). |
| **`kb_articles`** | Base de conhecimento. Armazena artigos HTML e contadores de visualização/votos. |
| **`configuracoes_sla`** | Parâmetros dinâmicos que ditam o tempo aceitável para o fechamento de um chamado. |

---

## 6. Stack Tecnológica e Dependências

- **Linguagem de Servidor:** PHP
- **Banco de Dados:** MySQL / MariaDB (Driver: `mysqli`)
- **Frontend / Estilos:**
  - HTML5 & CSS3 (com utilitários customizados)
  - Bootstrap 4.6
  - Animate.css (para animações da UI)
  - Bibliotecas de Fontes: FontAwesome 5, Google Fonts (Inter, Montserrat, Nunito)
- **Gráficos e Dashboards:** Chart.js
- **Manipulação JavaScript:** jQuery 3.5.1
- **Editor Rico (WYSIWYG):** Summernote
- **Geração de PDF:** FPDF
- **Integrações Externas (APIs):**
  - IA Gemini
  - APIs de Envio de WhatsApp (Configurado no arquivo `funcoes_whatsapp.php`)
