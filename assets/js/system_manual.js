document.addEventListener('DOMContentLoaded', function() {
    const startBtn = document.getElementById('start-manual');
    if (!startBtn) return;

    /**
     * Executa o tour guiado.
     * @param {boolean} force - Se verdadeiro, ignora o localStorage e inicia o tour.
     */
    function runSystemManual(force = false) {
        const path = window.location.pathname;
        const pageKey = 'manual_seen_' + path.split('/').pop().replace('.php', '') || 'dashboard';
        
        // Verifica se o usuário já viu o manual nesta página (se não for forçado)
        if (!force && localStorage.getItem(pageKey)) {
            return;
        }

        const intro = introJs();
        let steps = [];

        // Detecção de Página e Mapeamento de Passos (Mesma lógica anterior...)
        if (path.includes('index.php') || path.endsWith('/') || path.endsWith('/htdocs/')) {
            // DASHBOARD DO USUÁRIO
            steps = [
                {
                    title: '👋 Bem-vindo ao Asset MGT!',
                    intro: 'Este é o seu portal central. Além de ser funcional para o dia a dia, ele foi projetado para garantir a **integridade dos dados** e a **rastreabilidade total** dos ativos da empresa.'
                },
                {
                    element: '.sidebar',
                    title: 'Navegação Estrutural (Manual Não Funcional)',
                    intro: 'A barra lateral é a espinha dorsal do sistema. Ela reflete a separação de responsabilidades (SoD), permitindo que cada perfil acesse apenas o que lhe compete.'
                },
                {
                    title: '🚀 Painel de Controle',
                    intro: 'Acompanhe aqui seus chamados em aberto e ativos vinculados. O sistema monitora automaticamente o tempo de resposta (SLA) para garantir eficiência técnica.'
                },
                {
                    element: '#globalSearchInput',
                    title: 'Busca Inteligente',
                    intro: 'Pesquisa indexada que varre o banco de dados em tempo real para localizar etiquetas (Tags), números de série ou usuários.'
                },
                {
                    element: '#alertsDropdown',
                    title: 'Central de Alertas',
                    intro: 'Monitoramento proativo: o sistema alerta sobre o estoque atingindo o limite crítico antes que falte o insumo na operação.'
                },
                {
                    element: '#messagesDropdown',
                    title: 'Comunicação Segura',
                    intro: 'Chat interno criptografado para evitar o uso de ferramentas externas e manter o histórico de suporte centralizado.'
                }
            ];
        } 
        else if (path.includes('inicio.php')) {
            // CONSOLE OPERACIONAL (ADMIN)
            steps = [
                {
                    title: '📊 Console de Governança',
                    intro: 'Visão executiva baseada em KPIs (Key Performance Indicators). Aqui transformamos dados técnicos em decisões estratégicas.'
                },
                {
                    element: '#dashboard-stock-alert',
                    title: 'Mitigação de Riscos',
                    intro: 'Itens em vermelho indicam risco operacional por falta de estoque. O sistema calcula a necessidade de compra baseado no histórico de consumo.'
                },
                {
                    element: '.border-left-primary',
                    title: 'Taxa de Disponibilidade',
                    intro: 'Proporção de ativos prontos para uso vs ativos em manutenção. Um índice alto indica uma TI eficiente.'
                },
                {
                    element: '.border-left-success',
                    title: 'Gestão Patrimonial',
                    intro: 'Valor total do inventário. Esta função não funcional auxilia na contabilidade e no cálculo de impostos e depreciação anual.'
                }
            ];
        }
        else if (path.includes('equipamentos.php')) {
            // INVENTÁRIO
            steps = [
                {
                    title: '📦 Gestão de Ciclo de Vida',
                    intro: 'Gerencie o ativo desde a aquisição até o descarte. O foco aqui é o **Compliance** e a proteção do patrimônio.'
                },
                {
                    element: '.btn-premium-cadastro',
                    title: 'Input de Dados',
                    intro: 'Adicione novos itens. Cada registro gera um hash único para etiquetação física.'
                },
                {
                    element: '#assetsDataTable',
                    title: 'Base de Dados de Ativos',
                    intro: 'Tabela relacional. Cada linha permite rastrear quem usou o equipamento, quando foi para manutenção e qual o custo acumulado.'
                },
                {
                    element: '.dataTables_filter',
                    title: 'Filtros de Auditoria',
                    intro: 'Filtre rapidamente para auditorias surpresa ou conferência de estoque por setor.'
                }
            ];
        }
        else if (path.includes('usuarios.php')) {
            // USUÁRIOS
            steps = [
                {
                    title: '👤 Identidade e Acesso (IAM)',
                    intro: 'Controle quem pode fazer o quê. O sistema utiliza RBAC (Role-Based Access Control) para garantir segurança.'
                },
                {
                    element: '.btn-premium-cadastro',
                    title: 'Provisionamento',
                    intro: 'Crie novos perfis. Lembre-se: o tipo de usuário define a autoridade dele sobre o inventário.'
                },
                {
                    element: '#userTable',
                    title: 'Matriz de Responsabilidade',
                    intro: 'Visualize ativos sob custódia de cada colaborador. Essencial para processos de Onboarding e Offboarding.'
                }
            ];
        }
        else if (path.includes('licencas.php')) {
            // LICENÇAS
            steps = [
                {
                    title: '🔑 Compliance de Software',
                    intro: 'Evite multas e garanta o uso legal. Este módulo foca na **Gestão de Ativos de Software (SAM)**.'
                },
                {
                    element: '.btn-info-system',
                    title: 'Vinculação de Direito de Uso',
                    intro: 'Atribua licenças a usuários. O sistema audita automaticamente se o número de instalações excede o comprado.'
                },
                {
                    element: '.progress',
                    title: 'Ocupação de Seats',
                    intro: 'Visão não funcional: Identifique subutilização de licenças pagas para otimizar custos de subscrição.'
                }
            ];
        }
        else if (path.includes('chamados.php')) {
            // CHAMADOS
            steps = [
                {
                    title: '🎫 Gestão de Incidentes e Requisições',
                    intro: 'Centralize o suporte. O objetivo principal aqui é a **Restauração Rápida do Serviço**.'
                },
                {
                    element: '.sla-container',
                    title: 'Acordo de Nível de Serviço (SLA)',
                    intro: 'Conceito Não Funcional: É a promessa de entrega. As cores indicam se estamos cumprindo o contrato de suporte com o usuário.'
                },
                {
                    element: '#filtro_status',
                    title: 'Fila de Atendimento',
                    intro: 'Organize o trabalho por urgência e impacto. Priorize chamados P1 (Críticos) para evitar paradas na operação.'
                }
            ];
        }
        else if (path.includes('relatorios.php')) {
            // RELATÓRIOS
            steps = [
                {
                    title: '📑 Business Intelligence',
                    intro: 'Converta dados brutos em conhecimento. Relatórios são fundamentais para auditorias externas (ISO/IEC 27001).'
                },
                {
                    element: '#reportType',
                    title: 'Taxonomia de Relatórios',
                    intro: 'Modelos prontos que cobrem desde a saúde financeira até o inventário físico detalhado.'
                },
                {
                    element: '#reportFormat',
                    title: 'Integridade dos Dados',
                    intro: 'Exportação em PDF para documentos IMUTÁVEIS (oficiais) ou XLSX para análise de tendências.'
                }
            ];
        }
        else if (path.includes('insights.php')) {
            // IA INSIGHTS
            steps = [
                {
                    title: '🤖 Análise Preditiva (IA)',
                    intro: 'Utilizamos modelos avançados para prever falhas antes que elas ocorram, permitindo a manutenção proativa.'
                },
                {
                    element: '#ai-analysis-text',
                    title: 'Insights Estratégicos',
                    intro: 'A IA analisa padrões de chamados e sugere melhorias na infraestrutura para reduzir a recorrência de problemas.'
                },
                {
                    element: '.border-bottom-danger',
                    title: 'Gestão de Obsolescência',
                    intro: 'Identificamos automaticamente equipamentos que geram custo de manutenção superior ao valor de mercado.'
                }
            ];
        }
        else if (path.includes('locais.php')) {
            // LOCAIS
            steps = [
                {
                    title: '📍 Topologia da Infraestrutura',
                    intro: 'Organize logicamente seu espaço físico. Um inventário preciso depende de saber EXATAMENTE onde cada ativo está localizado.'
                },
                {
                    element: '.col-md-4',
                    title: 'Hierarquia de Locais',
                    intro: 'Crie estruturas em árvore (ex: Empresa > Prédio > CPD > Rack). Isso permite gerar relatórios de valor por unidade física.'
                }
            ];
        }
        else if (path.includes('chat_interno.php')) {
            // CHAT
            steps = [
                {
                    title: '💬 Colaboração em Tempo Real',
                    intro: 'A agilidade no suporte depende de uma comunicação rápida. Utilize o chat para esclarecer dúvidas sobre chamados sem sair do contexto do sistema.'
                },
                {
                    element: '.chat-sidebar',
                    title: 'Segurança da Informação',
                    intro: 'Conceito Não Funcional: Manter conversas técnicas dentro do ambiente corporativo protege dados sensíveis da empresa contra vazamentos.'
                },
                {
                    element: '#myStatusBtn',
                    title: 'Disponibilidade Técnica',
                    intro: 'Informe seu estado. Isso ajuda na distribuição de tarefas e no gerenciamento da expectativa de resposta dos usuários.'
                }
            ];
        }
        else if (path.includes('gerenciar_kb.php')) {
            // BASE DE CONHECIMENTO
            steps = [
                {
                    title: '📚 Gestão do Conhecimento',
                    intro: 'Evite o "Silo de Informação". Documentar soluções recorrentes reduz o tempo médio de reparo (MTTR) em até 40%.'
                },
                {
                    element: '.btn-primary[href="adicionar_artigo.php"]',
                    title: 'Padronização Técnica',
                    intro: 'Ao criar um artigo, você funcionalmente ajuda outros técnicos a seguirem o mesmo procedimento operacional para um problema comum.'
                }
            ];
        }
        else if (path.includes('centro_de_custo.php') || path.includes('fornecedores.php')) {
            // CADASTROS AUXILIARES
            steps = [
                {
                    title: '📋 Governança Financeira',
                    intro: 'Crucial para o ciclo de vida do ativo. O vínculo com Centros de Custo permite saber qual departamento está gerando maior gasto tecnológico.'
                }
            ];
        }
        else if (path.includes('configuracoes.php')) {
            // CONFIGURAÇÕES
            steps = [
                {
                    title: '⚙️ Parametrizar Sistema',
                    intro: 'Ajuste fino do Asset MGT.'
                },
                {
                    element: '#settingsTabs',
                    title: 'Menu de Abas',
                    intro: 'Configure Branding, SMTP, IA, e as regras exclusivas de SLA.'
                }
            ];
        }

        // Se a página não tiver passos definidos, mostra um guia básico
        if (steps.length === 0) {
            steps = [
                {
                    title: 'Explorar Módulo',
                    intro: 'Utilize as ferramentas desta tela para gerenciar seus ativos. Em caso de dúvidas, consulte o administrador do sistema.'
                }
            ];
        }

        // Iniciar Intro.js com opções customizadas
        intro.setOptions({
            steps: steps,
            nextLabel: 'Próximo →',
            prevLabel: '← Anterior',
            doneLabel: 'Entendi!',
            showProgress: true,
            showBullets: false,
            overlayOpacity: 0.8,
            scrollToElement: true,
            disableInteraction: false,
            tooltipClass: 'custom-tour-tooltip'
        }).onbeforechange(function(element) {
            // Lógica para alternar abas automaticamente nas configurações
            if (path.includes('configuracoes.php') && element.id) {
                if (element.id.includes('branding')) $('#branding-tab').tab('show');
                if (element.id.includes('sla')) $('#sla-tab').tab('show');
                if (element.id.includes('ia')) $('#ia-tab').tab('show');
            }
        }).oncomplete(function() {
            localStorage.setItem(pageKey, 'true');
        }).onexit(function() {
            localStorage.setItem(pageKey, 'true');
        }).start();
    }

    // Clique Manual: Sempre dispara o tour
    startBtn.addEventListener('click', function() {
        runSystemManual(true);
    });

    // Auto-disparo: Apenas no Dashboard (index.php) no primeiro acesso
    setTimeout(() => {
        const path = window.location.pathname;
        if (path.includes('index.php') || path.endsWith('/') || path.endsWith('/htdocs/')) {
            runSystemManual(false);
        }
    }, 1200); 
});
