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
                    title: '👋 Bem-vindo!',
                    intro: 'Este é o seu painel de controle pessoal. Aqui você acompanha seus chamados e ativos atribuídos.'
                },
                {
                    element: '#globalSearchInput',
                    title: 'Busca Inteligente',
                    intro: 'Encontre equipamentos, usuários ou chamados instantaneamente.'
                },
                {
                    element: '#alertsDropdown',
                    title: 'Notificações',
                    intro: 'Alertas críticos sobre estoque, manutenções e sistema.'
                },
                {
                    element: '#messagesDropdown',
                    title: 'Comunicação',
                    intro: 'Acesse o chat interno para falar com a equipe de TI.'
                }
            ];
        } 
        else if (path.includes('inicio.php')) {
            // CONSOLE OPERACIONAL (ADMIN)
            steps = [
                {
                    title: '📊 Console Operacional',
                    intro: 'Visão executiva para gestores. Acompanhe a saúde da sua infraestrutura.'
                },
                {
                    element: '#dashboard-stock-alert',
                    title: 'Estoque Crítico',
                    intro: 'Itens que atingiram o limite mínimo e precisam de reposição urgente.'
                },
                {
                    element: '.border-left-primary',
                    title: 'Disponibilidade',
                    intro: 'Total de ativos prontos para uso em estoque.'
                },
                {
                    element: '.border-left-success',
                    title: 'Valor Patrimonial',
                    intro: 'Valor total do inventário calculado com depreciação em tempo real.'
                }
            ];
        }
        else if (path.includes('equipamentos.php')) {
            // INVENTÁRIO
            steps = [
                {
                    title: '📦 Inventário de Ativos',
                    intro: 'Lista completa de hardware e dispositivos da empresa.'
                },
                {
                    element: '.btn-premium-cadastro',
                    title: 'Adicionar Ativos',
                    intro: 'Cadastre novos itens individualmente ou via importação em massa.'
                },
                {
                    element: '#assetsDataTable',
                    title: 'Tabela de Dados',
                    intro: 'Visualize Tags, Seriais e Localização. Clique em uma linha para ver o perfil completo.'
                },
                {
                    element: '.dataTables_filter',
                    title: 'Filtros Dinâmicos',
                    intro: 'Filtre por setor, categoria ou fabricante para encontrar o que precisa.'
                }
            ];
        }
        else if (path.includes('usuarios.php')) {
            // USUÁRIOS
            steps = [
                {
                    title: '👤 Gestão de Pessoas',
                    intro: 'Administre o acesso de colaboradores e técnicos ao sistema.'
                },
                {
                    element: '.btn-premium-cadastro',
                    title: 'Novo Usuário',
                    intro: 'Crie perfis de Admin, Suporte ou Usuário Comum.'
                },
                {
                    element: '#userTable',
                    title: 'Controle de Acessos',
                    intro: 'Acompanhe quem possui ativos atribuídos e o status da conta.'
                }
            ];
        }
        else if (path.includes('licencas.php')) {
            // LICENÇAS
            steps = [
                {
                    title: '🔑 Software & Licenças',
                    intro: 'Gerencie subscrições, chaves de ativação e compliance de software.'
                },
                {
                    element: '.btn-info-system',
                    title: 'Atribuição Direta',
                    intro: 'Vincule uma licença a um usuário específico com um clique.'
                },
                {
                    element: '.progress',
                    title: 'Uso de Seats',
                    intro: 'Monitore quantos computadores estão usando esta licença vs o limite contratado.'
                }
            ];
        }
        else if (path.includes('chamados.php')) {
            // CHAMADOS
            steps = [
                {
                    title: '🎫 Central de Tickets',
                    intro: 'Fluxo de atendimento técnico e solicitações de serviço.'
                },
                {
                    element: '.sla-container',
                    title: 'Prazo de Resposta (SLA)',
                    intro: 'Monitoramento visual do tempo restante para o atendimento.'
                },
                {
                    element: '#filtro_status',
                    title: 'Organização da Fila',
                    intro: 'Filtre por chamados abertos ou pendentes de aprovação.'
                }
            ];
        }
        else if (path.includes('relatorios.php')) {
            // RELATÓRIOS
            steps = [
                {
                    title: '📑 Central de Inteligência',
                    intro: 'Gere documentos oficiais e análises de dados.'
                },
                {
                    element: '#reportType',
                    title: 'Modelos Disponíveis',
                    intro: 'Mais de 60 tipos de relatórios: Financeiro, Inventário, Auditoria e mais.'
                },
                {
                    element: '#reportFormat',
                    title: 'Exportação',
                    intro: 'Escolha entre PDF para documentos oficiais ou XLSX para edição em Excel.'
                }
            ];
        }
        else if (path.includes('insights.php')) {
            // IA INSIGHTS
            steps = [
                {
                    title: '🤖 Inteligência Artificial',
                    intro: 'Análise preditiva gerada pelo Gemini 2.0.'
                },
                {
                    element: '#ai-analysis-text',
                    title: 'Análise Estratégica',
                    intro: 'Sugestões automáticas baseadas no comportamento da sua infraestrutura.'
                },
                {
                    element: '.border-bottom-danger',
                    title: 'Prevenção de Falhas',
                    intro: 'Identificamos ativos que falham com frequência para substituição proativa.'
                }
            ];
        }
        else if (path.includes('locais.php')) {
            // LOCAIS
            steps = [
                {
                    title: '📍 Estrutura Física',
                    intro: 'Organize sua empresa por Unidades, Prédios e Salas.'
                },
                {
                    element: '.col-md-4',
                    title: 'Cadastro de Local',
                    intro: 'Crie hierarquias (Ex: Racks dentro de uma Sala no 2º Andar).'
                }
            ];
        }
        else if (path.includes('chat_interno.php')) {
            // CHAT
            steps = [
                {
                    title: '💬 Comunicação Interna',
                    intro: 'Converse com técnicos e colaboradores em tempo real.'
                },
                {
                    element: '.chat-sidebar',
                    title: 'Contatos e Grupos',
                    intro: 'Busque colegas ou crie grupos de trabalho para equipes específicas.'
                },
                {
                    element: '#myStatusBtn',
                    title: 'Status de Presença',
                    intro: 'Avise se você está disponível, ocupado ou ausente.'
                }
            ];
        }
        else if (path.includes('gerenciar_kb.php')) {
            // BASE DE CONHECIMENTO
            steps = [
                {
                    title: '📚 Base de Conhecimento',
                    intro: 'Repositório de soluções e manuais técnicos.'
                },
                {
                    element: '.btn-primary[href="adicionar_artigo.php"]',
                    title: 'Contribuir',
                    intro: 'Crie novos artigos para documentar resoluções de problemas comuns.'
                },
                {
                    element: '.input-group',
                    title: 'Pesquisa',
                    intro: 'Busque por palavras-chave para encontrar soluções rapidamente.'
                }
            ];
        }
        else if (path.includes('centro_de_custo.php') || path.includes('fornecedores.php')) {
            // CADASTROS AUXILIARES
            steps = [
                {
                    title: '📋 Gestão Administrativa',
                    intro: 'Módulo para organização financeira e de parcerias.'
                },
                {
                    element: '.btn-premium-cadastro',
                    title: 'Novo Registro',
                    intro: 'Adicione centros de custo ou fornecedores para vincular aos seus ativos.'
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

    // Auto-disparo: Apenas no primeiro acesso à página
    setTimeout(() => {
        runSystemManual(false);
    }, 1200); // Delay sutil para garantir que a página carregou visualmente
});
