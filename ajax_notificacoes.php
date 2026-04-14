<?php
/**
 * AJAX NOTIFICAÇÕES: ajax_notificacoes.php
 * Endpoint para buscar alertas do sistema em tempo real (Estoque, Manutenção, etc).
 */
include_once 'auth.php';
include_once 'conexao.php';
require_once 'language.php';

header('Content-Type: application/json');

// Apenas Admin e Suporte recebem alertas de estoque
if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    echo json_encode(['count' => 0, 'alerts' => []]);
    exit();
}

// Busca configurações de alerta
$conf = $conn->query("SELECT * FROM configuracoes_alertas WHERE id = 1")->fetch_assoc();

$sql = "SELECT tier, categoria, COUNT(*) as total_disponivel
        FROM ativos
        WHERE status = 'Disponível'
        GROUP BY tier, categoria";

$result = $conn->query($sql);
$alerts = [];
$total_count = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tier = $row['tier'] ?: '';
        $categoria = $row['categoria'];
        $qtd = intval($row['total_disponivel']);
        
        // Determina o threshold baseado no tier (usando os novos campos configuráveis)
        $threshold = 3; // Fallback padrão
        if ($tier == 'Tier 1') $threshold = intval($conf['estoque_threshold_t1'] ?? 3);
        elseif ($tier == 'Tier 2') $threshold = intval($conf['estoque_threshold_t2'] ?? 3);
        elseif ($tier == 'Tier 3') $threshold = intval($conf['estoque_threshold_t3'] ?? 3);
        elseif ($tier == 'Tier 4') $threshold = intval($conf['estoque_threshold_t4'] ?? 3);
        elseif ($tier == 'Infraestrutura') $threshold = intval($conf['estoque_threshold_inf'] ?? 3);

        if ($qtd <= $threshold) {
            $alerts[] = [
                'icon' => 'fas fa-box-open',
                'bg_class' => 'bg-danger',
                'title' => __('Estoque Crítico') . ': ' . __($categoria),
                'subtitle' => ($tier ?: __('Geral')) . ' - ' . $qtd . ' ' . __('unidades disponíveis'),
                'link' => "equipamentos.php?busca=" . urlencode($categoria)
            ];
            $total_count++;
        }
    }
}

echo json_encode(['count' => $total_count, 'alerts' => $alerts]);
