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
                    title: '👋 Bem-vindo(a) ao Asset MGT!',
                    intro: 'Neste pequeno tour, vamos te mostrar como é fácil usar o sistema. O **Dashboard** é a sua tela inicial, onde você tem um resumo rápido de tudo o que importa.'
                },
                {
                    element: '.sidebar',
                    title: 'Navegação Principal',
                    intro: 'Aqui no menu lateral você encontra todos os módulos do sistema. Sinta-se livre para explorar: Inventário, Chamados, Relatórios e muito mais!'
                },
                {
                    title: '🚀 Seus Chamados e Ativos',
                    intro: 'Logo de cara, mostramos os chamados que precisam da sua atenção e os equipamentos que estão sob a sua responsabilidade.'
                },
                {
                    element: '#globalSearchInput',
                    title: 'Busca Rápida',
                    intro: 'Precisa encontrar algo depressa? Digite a etiqueta (Tag) do equipamento, número de série ou até mesmo o nome de um usuário aqui.'
                },
                {
                    element: '#alertsDropdown',
                    title: 'Central de Notificações',
                    intro: 'Fique de olho aqui! O sistema vai te avisar sempre que houver algum alerta importante, como equipamentos precisando de manutenção ou estoque baixo.'
                },
                {
                    element: '#messagesDropdown',
                    title: 'Chat da Equipe',
                    intro: 'Fale com os técnicos e membros da equipe rapidamente através do nosso chat interno, sem precisar abrir outro aplicativo.'
                }
            ];
        } 
        else if (path.includes('inicio.php')) {
            // CONSOLE OPERACIONAL (ADMIN)
            steps = [
                {
                    title: '📊 Console Operacional',
                    intro: 'Esta é a visão gerencial. Aqui você acompanha a saúde geral da TI e do inventário através de gráficos fáceis de ler.'
                },
                {
                    element: '#dashboard-stock-alert',
                    title: 'Alertas de Estoque',
                    intro: 'Se algum item estiver acabando, ele aparecerá em vermelho aqui para que você possa comprar mais antes que falte.'
                },
                {
                    element: '.border-left-primary',
                    title: 'Ativos em Operação',
                    intro: 'Este cartão mostra a quantidade total de ativos que estão atualmente sob a responsabilidade de algum usuário.'
                },
                {
                    element: '.border-left-success',
                    title: 'Patrimônio Atualizado',
                    intro: 'Acompanhe aqui o valor real (já descontada a depreciação) de todo o seu parque tecnológico.'
                },
                {
                    element: '.border-left-warning',
                    title: 'Elegíveis para Baixa',
                    intro: 'Quantidade de equipamentos que já podem ser doados ou enviados para leilão, baseados na vida útil.'
                },
                {
                    element: '.border-left-danger',
                    title: 'Desvalorização Média',
                    intro: 'A porcentagem média de perda patrimonial do parque desde o momento da compra.'
                },
                {
                    element: '#dataTable',
                    title: 'Ativos Elegíveis',
                    intro: 'Esta tabela lista os ativos com foco operacional. Se o botão "Doar" estiver verde, significa que o equipamento já cumpriu seu tempo de carência e pode ser doado.'
                },
                {
                    element: '.btn-premium-cadastro',
                    title: 'Histórico de Doações',
                    intro: 'Clicando aqui, você vai para a galeria que registra todas as doações oficiais já feitas pela empresa.'
                }
            ];
        }
        else if (path.includes('equipamentos.php')) {
            // INVENTÁRIO
            steps = [
                {
                    title: '📦 Inventário de Ativos',
                    intro: 'Aqui é onde controlamos todos os equipamentos da empresa, desde computadores e monitores até teclados.'
                },
                {
                    element: '.btn-premium-cadastro',
                    title: 'Adicionar Novo',
                    intro: 'Clique aqui para registrar um novo equipamento. O sistema vai gerar automaticamente uma etiqueta (Tag) única para ele.'
                },
                {
                    element: '#assetsDataTable',
                    title: 'Lista Completa',
                    intro: 'Nesta tabela você vê todos os equipamentos. Você pode clicar em um deles para ver o histórico de quem usou e as manutenções.'
                },
                {
                    element: '.dataTables_filter',
                    title: 'Filtrar Lista',
                    intro: 'Use esta caixinha para procurar um equipamento específico no meio da lista.'
                }
            ];
        }
        else if (path.includes('usuarios.php')) {
            // USUÁRIOS
            steps = [
                {
                    title: '👤 Controle de Usuários',
                    intro: 'Gerencie todos os colaboradores que têm acesso ao sistema e veja quais equipamentos estão com eles.'
                },
                {
                    element: '.btn-premium-cadastro',
                    title: 'Novo Colaborador',
                    intro: 'Cadastre novas pessoas aqui. Você pode definir se a pessoa é apenas um Usuário comum, um Técnico de Suporte ou um Administrador.'
                }
            ];
        }
        else if (path.includes('licencas.php')) {
            // LICENÇAS
            steps = [
                {
                    title: '🔑 Licenças de Software',
                    intro: 'Controle os softwares pagos da empresa (como Office, Antivírus, etc) para garantir que você não está pagando por licenças sem uso.'
                },
                {
                    element: '.btn-premium-cadastro',
                    title: 'Nova Licença',
                    intro: 'Clique aqui para registrar a compra de um novo software ou serviço de assinatura.'
                },
                {
                    element: '#licencasTable',
                    title: 'Inventário de Software',
                    intro: 'Aqui você acompanha todas as licenças que a empresa possui, e quantas ainda estão disponíveis para uso.'
                },
                {
                    element: '.btn-info-system',
                    title: 'Distribuir Licenças',
                    intro: 'Use este botão para associar uma licença a um usuário ou a um computador específico.'
                }
            ];
        }
        else if (path.includes('chamados.php')) {
            // CHAMADOS
            steps = [
                {
                    title: '🎫 Central de Chamados',
                    intro: 'Este é o coração do suporte! Todos os problemas relatados pelos usuários chegam aqui na forma de um "Ticket" (Chamado).'
                },
                {
                    element: '.sla-container',
                    title: 'Tempo de Atendimento (SLA)',
                    intro: 'As barrinhas coloridas ajudam a entender a urgência. Elas avisam se o tempo para resolver um problema está acabando.'
                },
                {
                    element: '#filtro_status',
                    title: 'Organize o Trabalho',
                    intro: 'Filtre os chamados para ver apenas os "Abertos" ou filtre por "Prioridade Alta" para saber o que resolver primeiro.'
                }
            ];
        }
        else if (path.includes('relatorios.php')) {
            // RELATÓRIOS
            steps = [
                {
                    title: '📑 Emissão de Relatórios',
                    intro: 'Precisa apresentar dados em uma reunião? Escolha o modelo aqui e gere um relatório completo com apenas um clique.'
                },
                {
                    element: '#reportType',
                    title: 'Tipos de Relatório',
                    intro: 'Temos relatórios de doações, de equipamentos em uso, histórico de manutenções e muito mais.'
                },
                {
                    element: '#reportFormat',
                    title: 'Formato do Arquivo',
                    intro: 'Você pode escolher se prefere baixar um arquivo PDF bonito e bloqueado para edição, ou uma planilha de Excel (XLSX) para editar.'
                }
            ];
        }
        else if (path.includes('insights.php')) {
            // IA INSIGHTS
            steps = [
                {
                    title: '🤖 Dicas da Inteligência Artificial',
                    intro: 'Nossa IA analisa todo o seu sistema e dá dicas automáticas para melhorar a operação de TI.'
                },
                {
                    element: '#ai-analysis-text',
                    title: 'Sugestões Estratégicas',
                    intro: 'Leia aqui os conselhos da IA. Por exemplo, ela pode avisar se um modelo específico de impressora está quebrando demais.'
                }
            ];
        }
        else if (path.includes('locais.php')) {
            // LOCAIS
            steps = [
                {
                    title: '📍 Locais e Infraestrutura',
                    intro: 'Organize as salas, andares e prédios da empresa. Saber exatamente onde um equipamento está facilita muito os inventários físicos.'
                },
                {
                    element: '.col-md-4 .card',
                    title: 'Adicionar Local',
                    intro: 'Use este formulário para cadastrar novos prédios, andares, salas ou até mesmo racks e baias.'
                },
                {
                    element: '.col-md-8',
                    title: 'Árvore de Locais',
                    intro: 'Aqui fica a hierarquia visual. Por exemplo: Prédio Central -> Andar 2 -> Sala de Reunião.'
                }
            ];
        }
        else if (path.includes('chat_interno.php')) {
            // CHAT
            steps = [
                {
                    title: '💬 Chat Interno',
                    intro: 'Converse com os técnicos do suporte por aqui. Assim, toda a comunicação técnica fica salva e organizada dentro do sistema.'
                },
                {
                    element: '#myStatusBtn',
                    title: 'Seu Status',
                    intro: 'Você pode mudar seu status para "Ocupado" ou "Ausente" para que os outros saibam se você pode ajudar no momento.'
                }
            ];
        }
        else if (path.includes('gerenciar_kb.php')) {
            // BASE DE CONHECIMENTO
            steps = [
                {
                    title: '📚 Base de Conhecimento (Wiki)',
                    intro: 'Uma biblioteca de soluções! Se você resolveu um problema difícil, crie um artigo aqui para ajudar outros técnicos no futuro.'
                },
                {
                    element: '.btn-primary',
                    title: 'Novo Artigo',
                    intro: 'Clique aqui para escrever um novo manual ou tutorial.'
                },
                {
                    element: '.table',
                    title: 'Lista de Artigos',
                    intro: 'Aqui ficam todos os artigos já publicados. Você pode editar ou apagar os mais antigos.'
                }
            ];
        }
        else if (path.includes('configuracoes.php')) {
            // CONFIGURAÇÕES
            steps = [
                {
                    title: '⚙️ Configurações do Sistema',
                    intro: 'Ajuste o sistema para ficar com a cara da sua empresa.'
                },
                {
                    element: '#settingsTabs',
                    title: 'Abas de Configuração',
                    intro: 'Navegue pelas abas para trocar o logotipo, configurar os tempos de SLA ou alterar configurações avançadas.'
                }
            ];
        }
        else if (path.includes('leiloes.php')) {
            steps = [
                {
                    title: '🔨 Lotes de Leilão',
                    intro: 'Aqui você organiza os equipamentos antigos que serão vendidos. Agrupe-os em lotes para facilitar a venda.'
                },
                {
                    element: '#dataTable',
                    title: 'Ativos Elegíveis',
                    intro: 'Esta tabela mostra os equipamentos que atingiram 100% de depreciação e estão prontos para serem leiloados.'
                },
                {
                    element: '.btn-warning',
                    title: 'Criar Novo Lote',
                    intro: 'Você pode criar um lote vazio e depois ir adicionando vários equipamentos dentro dele.'
                },
                {
                    element: '#lotes-fechados-card',
                    title: 'Lotes Aguardando Leilão',
                    intro: 'Aqui ficam os lotes já fechados e prontos. Eles aguardam o processo de leilão para serem vendidos.'
                }
            ];
        }
        else if (path.includes('ativos_doados.php')) {
            steps = [
                {
                    title: '🤝 Ativos Doados',
                    intro: 'Esta é a galeria de boas ações! Aqui fica o registro permanente de todos os equipamentos que a empresa doou para ONGs ou instituições.'
                },
                {
                    element: '#dataTable',
                    title: 'Histórico de Doações',
                    intro: 'Aqui estão os detalhes de cada doação. Ter este registro é importante para prestação de contas da empresa.'
                },
                {
                    element: '.btn-danger',
                    title: 'Exportar Relatório PDF',
                    intro: 'Você pode gerar um PDF com todas as doações clicando aqui.'
                },
                {
                    element: '.btn-success',
                    title: 'Exportar Planilha XLSX',
                    intro: 'Se preferir trabalhar com os dados, gere uma planilha do Excel.'
                }
            ];
        }
        else if (path.includes('ativos_leiloados.php')) {
            steps = [
                {
                    title: '💰 Ativos Leiloados',
                    intro: 'Veja o histórico de tudo que já foi vendido nos leilões. Aqui você confere o valor arrecadado com cada equipamento antigo.'
                },
                {
                    element: '#dataTable',
                    title: 'Tabela de Vendas',
                    intro: 'Esta lista é imutável e serve para auditorias fiscais sobre o desfazimento do patrimônio.'
                },
                {
                    element: '.btn-danger',
                    title: 'Relatório Oficial PDF',
                    intro: 'Gera o relatório de vendas completo formatado em PDF para auditoria.'
                },
                {
                    element: '.btn-success',
                    title: 'Planilha Contábil XLSX',
                    intro: 'Gera uma planilha em Excel para sua equipe financeira processar os valores arrecadados.'
                }
            ];
        }
        else if (path.includes('centro_de_custo.php')) {
            steps = [
                {
                    title: '🏢 Centros de Custo',
                    intro: 'Cadastre os departamentos ou projetos da empresa. Isso ajuda a saber qual setor gasta mais com tecnologia!'
                },
                {
                    element: '.btn-premium-cadastro',
                    title: 'Novo Departamento',
                    intro: 'Clique aqui para adicionar um novo setor ou centro de custo.'
                },
                {
                    element: '#dataTable',
                    title: 'Lista de Setores',
                    intro: 'Aqui você vê todos os setores. Você pode editar ou excluir os centros de custo existentes.'
                }
            ];
        }
        else if (path.includes('fornecedores.php')) {
            steps = [
                {
                    title: '🤝 Fornecedores',
                    intro: 'Cadastre as lojas e distribuidores de onde você compra equipamentos. É sempre bom ter o contato rápido quando precisar acionar uma garantia.'
                },
                {
                    element: '.btn-premium-cadastro',
                    title: 'Novo Fornecedor',
                    intro: 'Adicione um novo parceiro comercial aqui.'
                },
                {
                    element: '#fornecedoresDataTable',
                    title: 'Contatos Rápidos',
                    intro: 'Aqui ficam os e-mails e telefones dos seus fornecedores.'
                }
            ];
        }
        else if (path.includes('agent.php')) {
            steps = [
                {
                    title: '🧠 Assistente IA',
                    intro: 'Precisa de uma ajuda inteligente? Converse com o nosso Assistente de Inteligência Artificial para tirar dúvidas sobre o sistema ou equipamentos.'
                },
                {
                    element: '#chat-history',
                    title: 'Histórico da Conversa',
                    intro: 'Aqui você verá as respostas da nossa IA.'
                },
                {
                    element: '#user-input',
                    title: 'Faça sua Pergunta',
                    intro: 'Digite o que você precisa saber. Por exemplo: "Como eu crio um chamado?" ou "Quais ativos estão em manutenção?".'
                }
            ];
        }
        else if (path.includes('suporte.php')) {
            steps = [
                {
                    title: '🛠️ Suporte Técnico',
                    intro: 'Está com algum problema no sistema? Aqui você encontra os canais oficiais para falar com a nossa equipe de suporte avançado.'
                },
                {
                    element: '#supportForm',
                    title: 'Abertura de Ticket',
                    intro: 'Preencha este formulário detalhando a sua dúvida ou problema técnico. Nossa equipe entrará em contato o mais rápido possível.'
                }
            ];
        }
        else if (path.includes('documentacao.php')) {
            steps = [
                {
                    title: '📖 Documentação',
                    intro: 'Manuais técnicos e guias completos sobre como o sistema funciona nos mínimos detalhes.'
                },
                {
                    element: '.accordion',
                    title: 'Tópicos de Ajuda',
                    intro: 'Clique em cada categoria para expandir e ler o conteúdo.'
                }
            ];
        }
        else if (path.includes('cadastro_de_')) {
            // TELAS DE CADASTRO
            steps = [
                {
                    title: '📝 Tela de Cadastro',
                    intro: 'Aqui você pode registrar novas informações no sistema. Preencha os campos obrigatórios com atenção.'
                },
                {
                    element: '.card-body form, .card form',
                    title: 'Dica de Preenchimento',
                    intro: 'Certifique-se de preencher corretamente todos os dados do formulário. Isso é fundamental para manter a integridade do inventário!'
                },
                {
                    element: 'button[type="submit"]',
                    title: 'Salvar Registro',
                    intro: 'Após preencher tudo, não se esqueça de clicar aqui para salvar!'
                }
            ];
        }
        else if (path.includes('editar_')) {
            // TELAS DE EDIÇÃO
            steps = [
                {
                    title: '✏️ Edição de Registro',
                    intro: 'Você está editando um registro existente. Altere apenas as informações necessárias.'
                },
                {
                    element: '.card-body form, .card form',
                    title: 'Formulário de Edição',
                    intro: 'Revise os dados antes de confirmar. Lembre-se de verificar se não há nada incorreto.'
                },
                {
                    element: 'button[type="submit"]',
                    title: 'Atualizar',
                    intro: 'Clique aqui para aplicar e salvar as alterações no banco de dados.'
                }
            ];
        }
        else if (path.includes('perfil_') || path.includes('detalhes_')) {
            // TELAS DE PERFIL / DETALHES
            steps = [
                {
                    title: '🔍 Visão Detalhada',
                    intro: 'Esta é a página de detalhes completos. Aqui você pode ver todo o histórico e as informações aprofundadas sobre este item.'
                },
                {
                    element: '.card',
                    title: 'Resumo das Informações',
                    intro: 'Os cartões abaixo agrupam informações vitais, registros vinculados e histórico de movimentações.'
                }
            ];
        }

        // Se a página não tiver passos definidos, mostra um guia básico
        if (steps.length === 0) {
            steps = [
                {
                    title: 'Explore a Ferramenta',
                    intro: 'Esta é uma tela do sistema. Use os botões principais para interagir com as informações. Se precisar de ajuda, clique no botão de interrogação no topo da página.'
                }
            ];
        }

        // Iniciar Intro.js com opções customizadas
        intro.setOptions({
            steps: steps,
            nextLabel: 'Entendi, próximo →',
            prevLabel: '← Voltar',
            doneLabel: 'Pronto, vamos começar!',
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

    // Auto-disparo: Em qualquer página na primeira vez
    setTimeout(() => {
        runSystemManual(false);
    }, 1200); 
});
