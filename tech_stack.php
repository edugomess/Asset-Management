<?php
/**
 * TECH STACK: tech_stack.php
 * Vitrine tecnológica Enterprise do sistema Asset-Management.
 */
include_once 'auth.php';
include_once 'conexao.php';
require_once 'language.php';

$lang_code = (isset($_SESSION['language']) && $_SESSION['language'] == 'en') ? 'en' : 'pt-BR';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tech Stack — Asset MGT</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #4e73df; --success: #1cc88a; --info: #36b9cc; --warning: #f6c23e;
            --danger: #e74a3b; --dark: #2c3e50; --glass: rgba(255, 255, 255, 0.95);
            --bg-gradient: linear-gradient(135deg, #f8f9fc 0%, #e2e8f0 100%);
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg-gradient); color: #4a5568; min-height: 100vh; padding: 40px 20px; }
        h1, h2, h3, .brand { font-family: 'Outfit', sans-serif; }
        .container { max-width: 1100px; }
        .glass-header {
            background: var(--glass); backdrop-filter: blur(10px); border-radius: 24px; padding: 40px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07); border: 1px solid rgba(255, 255, 255, 0.18);
            margin-bottom: 40px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;
        }
        .brand-title { font-size: 2.5rem; font-weight: 800; background: linear-gradient(135deg, #2c3e50, #4e73df); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 8px; }
        .subtitle { font-size: 1.1rem; color: #718096; font-weight: 500; }
        .table-premium { background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.04); }
        .table-premium thead th { background: #f8f9fc; border-bottom: 2px solid #edf2f7; color: #2d3748; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; padding: 20px; }
        .table-premium tbody td { padding: 14px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-size: 0.88rem; }
        .badge-pill-custom { padding: 5px 12px; font-weight: 700; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .tech-version { font-family: monospace; background: #f1f5f9; padding: 2px 7px; border-radius: 5px; font-size: 0.8rem; color: #475569; }
        .btn-premium { background: linear-gradient(135deg, #4e73df, #224abe); color: #fff; border: none; padding: 14px 28px; border-radius: 14px; font-weight: 700; display: inline-flex; align-items: center; gap: 10px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(78, 115, 223, 0.4); }
        .divider { height: 4px; width: 40px; background: var(--primary); border-radius: 2px; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container pb-5">
    <header class="glass-header animate__animated animate__fadeInDown">
        <div><div class="divider"></div><h1 class="brand-title">📦 Tech Stack — Asset MGT</h1><p class="subtitle"><?php echo __('Cadeia completa de inovações e recursos do ecossistema.'); ?></p></div>
        <button class="btn-premium" onclick="exportToExcel()"><i class="fas fa-file-excel"></i> <?php echo __('Exportar Tech Stack'); ?></button>
    </header>

    <div class="table-premium animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table mb-0" id="techTable">
                <thead><tr><th>Camada</th><th>Tecnologia</th><th>Versão / Detalhes</th><th>Função no Sistema</th></tr></thead>
                <tbody>
                    <!-- BACKEND -->
                    <tr><td><span class="badge badge-pill badge-primary badge-pill-custom">Backend</span></td><td><strong>PHP</strong></td><td><span class="tech-version">8.x / 7.4+</span></td><td>Linguagem servidor; motor de lógica empresarial e gestão de inventário.</td></tr>
                    <tr><td><span class="badge badge-pill badge-primary badge-pill-custom">Backend</span></td><td><strong>MySQL</strong></td><td><span class="tech-version">5.7+ / MariaDB</span></td><td>Banco relacional; armazena ativos, usuários, logs e auditorias.</td></tr>
                    <tr><td><span class="badge badge-pill badge-primary badge-pill-custom">Backend</span></td><td><strong>PHP Sessions</strong></td><td><span class="tech-version">Nativo</span></td><td>Controle de autenticação, níveis de acesso e persistência de idioma.</td></tr>
                    <tr><td><span class="badge badge-pill badge-primary badge-pill-custom">Backend</span></td><td><strong>PHPMailer</strong></td><td><span class="tech-version">6.x / SMTP</span></td><td>Envio seguro de e-mails via SSL/TLS para notificações e alertas.</td></tr>
                    <tr><td><span class="badge badge-pill badge-primary badge-pill-custom">Backend</span></td><td><strong>JSON Interaction</strong></td><td><span class="tech-version">Serialized Schema</span></td><td>Armazenamento estruturado de históricos de interação e timelines complexas.</td></tr>
                    <tr><td><span class="badge badge-pill badge-primary badge-pill-custom">Backend</span></td><td><strong>FPDF</strong></td><td><span class="tech-version">1.86</span></td><td>Geração sob medida de relatórios e folhas de rosto em formato PDF.</td></tr>
                    <!-- IA ENGINE -->
                    <tr><td><span class="badge badge-pill badge-info badge-pill-custom">IA Engine</span></td><td><strong>Google Gemini API</strong></td><td><span class="tech-version">1.5 Flash/Pro</span></td><td>Assistente inteligente para suporte técnico e análise preditiva de ativos.</td></tr>
                    <tr><td><span class="badge badge-pill badge-info badge-pill-custom">IA Engine</span></td><td><strong>GitHub Models</strong></td><td><span class="tech-version">GPT-4o Mini</span></td><td>Infraestrutura de fallback para processamento de linguagem natural.</td></tr>
                    <tr><td><span class="badge badge-pill badge-info badge-pill-custom">IA Engine</span></td><td><strong>CallMeBot API</strong></td><td><span class="tech-version">REST API</span></td><td>Envio instantâneo de notificações e alertas via WhatsApp.</td></tr>
                    <!-- INNOVATION -->
                    <tr><td><span class="badge badge-pill badge-success badge-pill-custom">Innovation</span></td><td><strong>Digital Twin (v.php)</strong></td><td><span class="tech-version">QRCode.js 1.0</span></td><td>Sincronização mobile para consulta rápida em campo via QR Code.</td></tr>
                    <tr><td><span class="badge badge-pill badge-success badge-pill-custom">Innovation</span></td><td><strong>Base64 Media</strong></td><td><span class="tech-version">Clipboard Sync</span></td><td>Injeção direta de imagens e capturas de tela no banco via clipboard.</td></tr>
                    <tr><td><span class="badge badge-pill badge-success badge-pill-custom">Innovation</span></td><td><strong>i18n Translation</strong></td><td><span class="tech-version">JSON Engine</span></td><td>Gerenciamento multi-idioma nativo para interfaces globais.</td></tr>
                    <tr><td><span class="badge badge-pill badge-success badge-pill-custom">Innovation</span></td><td><strong>Asset Tiering</strong></td><td><span class="tech-version">Custom Logic</span></td><td>Classificação técnica de equipamentos por Tiers de desempenho.</td></tr>
                    <!-- UX / FRONTEND -->
                    <tr><td><span class="badge badge-pill badge-danger badge-pill-custom">UX Design</span></td><td><strong>Bootstrap</strong></td><td><span class="tech-version">4.5.2 / 4.6</span></td><td>Grid responsivo e componentes reativos de interface humana.</td></tr>
                    <tr><td><span class="badge badge-pill badge-danger badge-pill-custom">UX Design</span></td><td><strong>Vanilla CSS</strong></td><td><span class="tech-version">Premium Custom</span></td><td>Glassmorphism, hover effects e animações premium personalizadas.</td></tr>
                    <tr><td><span class="badge badge-pill badge-danger badge-pill-custom">UX Design</span></td><td><strong>Animate.css</strong></td><td><span class="tech-version">4.1.1</span></td><td>Biblioteca de micro-animações visuais para transição de estados.</td></tr>
                    <tr><td><span class="badge badge-pill badge-danger badge-pill-custom">UX Design</span></td><td><strong>Font Awesome</strong></td><td><span class="tech-version">5.15.4</span></td><td>Ecossistema de ícones Enterprise para representação de ativos.</td></tr>
                    <tr><td><span class="badge badge-pill badge-danger badge-pill-custom">UX Design</span></td><td><strong>Google Fonts</strong></td><td><span class="tech-version">Outfit / Inter</span></td><td>Tipografia premium calibrada para legibilidade e profissionalismo.</td></tr>
                    <tr><td><span class="badge badge-pill badge-danger badge-pill-custom">UX Design</span></td><td><strong>Rich Text UI</strong></td><td><span class="tech-version">Summernote Framework</span></td><td>Interface de edição avançada para artigos e comentários de chamados.</td></tr>
                    <tr><td><span class="badge badge-pill badge-danger badge-pill-custom">UX Design</span></td><td><strong>Responsive Media</strong></td><td><span class="tech-version">HTML5 / Iframes</span></td><td>Renderização adaptativa de vídeos, tabelas complexas e blockquotes.</td></tr>
                    <!-- JAVASCRIPT LAYER -->
                    <tr><td><span class="badge badge-pill badge-warning badge-pill-custom">JS Engine</span></td><td><strong>jQuery</strong></td><td><span class="tech-version">3.5.1 / 3.6</span></td><td>Fundação fundamental para interatividade e manipulação do DOM.</td></tr>
                    <tr><td><span class="badge badge-pill badge-warning badge-pill-custom">JS Engine</span></td><td><strong>jQuery Easing</strong></td><td><span class="tech-version">1.4.1</span></td><td>plugin para suavização de animações e transições jQuery.</td></tr>
                    <tr><td><span class="badge badge-pill badge-warning badge-pill-custom">JS Engine</span></td><td><strong>SweetAlert2</strong></td><td><span class="tech-version">11.x</span></td><td>Modais de confirmação e alertas elegantes com alta usabilidade.</td></tr>
                    <tr><td><span class="badge badge-pill badge-warning badge-pill-custom">JS Engine</span></td><td><strong>Chart.js</strong></td><td><span class="tech-version">2.9.4+</span></td><td>Renderização dinâmica de gráficos e dashboards operacionais.</td></tr>
                    <tr><td><span class="badge badge-pill badge-warning badge-pill-custom">JS Engine</span></td><td><strong>SheetJS (XLSX)</strong></td><td><span class="tech-version">0.18.5</span></td><td>Exportação massiva de dados para o formato Excel em tempo real.</td></tr>
                    <tr><td><span class="badge badge-pill badge-warning badge-pill-custom">JS Engine</span></td><td><strong>Lightpick / DRP</strong></td><td><span class="tech-version">Date Pickers</span></td><td>Seleção avançada de datas para filtros e relatórios periódicos.</td></tr>
                    <tr><td><span class="badge badge-pill badge-warning badge-pill-custom">JS Engine</span></td><td><strong>Summernote JS</strong></td><td><span class="tech-version">0.8.18</span></td><td>Biblioteca core para processamento de rich text e upload assíncrono.</td></tr>
                    <!-- PROTOCOLOS -->
                    <tr><td><span class="badge badge-pill badge-dark badge-pill-custom">Protocols</span></td><td><strong>AJAX (XHR)</strong></td><td><span class="tech-version">Nativo</span></td><td>Processamento assíncrono de dados para uma interface sem recarregamentos.</td></tr>
                    <tr><td><span class="badge badge-pill badge-dark badge-pill-custom">Protocols</span></td><td><strong>SMTP TLS/SSL</strong></td><td><span class="tech-version">TLS 1.2+</span></td><td>Protocolo seguro para transporte de comunicações institucionais.</td></tr>
                    <tr><td><span class="badge badge-pill badge-dark badge-pill-custom">Protocols</span></td><td><strong>HTTP REST</strong></td><td><span class="tech-version">JSON</span></td><td>Diálogo estruturado com APIs Gemini, CallMeBot e Endpoints internos.</td></tr>
                    <!-- INFRA -->
                    <tr><td><span class="badge badge-pill badge-secondary badge-pill-custom">Infra</span></td><td><strong>Apache Server</strong></td><td><span class="tech-version">2.4.58</span></td><td>Servidor web de alto desempenho para processamento de requisições.</td></tr>
                    <tr><td><span class="badge badge-pill badge-secondary badge-pill-custom">Infra</span></td><td><strong>XAMPP Stack</strong></td><td><span class="tech-version">8.2.12</span></td><td>Ecossistema integrado de desenvolvimento e servidor local Enterprise.</td></tr>
                    <tr><td><span class="badge badge-pill badge-secondary badge-pill-custom">Infra</span></td><td><strong>LONGTEXT Storage</strong></td><td><span class="tech-version">MySQL / 4GB</span></td><td>Capacitação massiva de armazenamento para interações de alta fidelidade.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <footer class="mt-5 text-center text-muted small pb-4">© <?php echo date('Y'); ?> Asset Management System — Elevando a gestão de ativos ao nível Enterprise.</footer>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
function exportToExcel() {
    const table = document.getElementById('techTable');
    const rows = [];
    table.querySelectorAll('tr').forEach(tr => {
        const cells = [];
        tr.querySelectorAll('th, td').forEach(c => cells.push(c.innerText.trim()));
        rows.push(cells);
    });
    const ws = XLSX.utils.aoa_to_sheet(rows);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Tech Stack');
    XLSX.writeFile(wb, 'AssetMGT_TechStack.xlsx');
}
</script>
</body>
</html>
