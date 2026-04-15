<?php
require 'conexao.php';
require_once 'language.php';

$sql = "SELECT au.*, u.nome, u.sobrenome, u.email 
        FROM alertas_usuarios au 
        JOIN usuarios u ON au.usuario_id = u.id_usuarios 
        WHERE au.recebe_estoque = 1
        ORDER BY u.nome ASC";

$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $name = htmlspecialchars(trim($row['nome'] . ' ' . $row['sobrenome']));
        $initials = strtoupper(substr($row['nome'], 0, 1) . (isset($row['sobrenome'][0]) ? substr($row['sobrenome'], 0, 1) : ''));

        // Estados das preferências
        $sT1 = ($row['estoque_t1'] ?? 1) == 1 ? 'active' : '';
        $sT2 = ($row['estoque_t2'] ?? 1) == 1 ? 'active' : '';
        $sT3 = ($row['estoque_t3'] ?? 1) == 1 ? 'active' : '';
        $sT4 = ($row['estoque_t4'] ?? 1) == 1 ? 'active' : '';
        $sINF = ($row['estoque_inf'] ?? 1) == 1 ? 'active' : '';

        echo '
        <div class="recipient-item d-flex align-items-center justify-content-between p-2 mb-2 bg-white rounded shadow-sm border" data-uid="' . $row['usuario_id'] . '">
            <div class="d-flex align-items-center" style="min-width: 0; flex: 1;">
                <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 30px; height: 30px; font-size: 0.75rem; font-weight: bold; margin-right: 10px;">
                    ' . $initials . '
                </div>
                <div style="min-width: 0; flex: 1;">
                    <div class="font-weight-bold text-dark text-truncate" style="font-size: 0.8rem;">' . $name . '</div>
                    <div class="text-muted text-truncate" style="font-size: 0.7rem;">' . htmlspecialchars($row['email']) . '</div>
                </div>
            </div>
            
            <!-- Toggles de Tier -->
            <div class="d-flex align-items-center mx-3">
                <div class="recipient-badge-group d-flex" style="gap: 3px;">
                    <button type="button" class="mini-badge-btn ' . $sT1 . '" data-pref="estoque_t1" title="Tier 1">T1</button>
                    <button type="button" class="mini-badge-btn ' . $sT2 . '" data-pref="estoque_t2" title="Tier 2">T2</button>
                    <button type="button" class="mini-badge-btn ' . $sT3 . '" data-pref="estoque_t3" title="Tier 3">T3</button>
                    <button type="button" class="mini-badge-btn ' . $sT4 . '" data-pref="estoque_t4" title="Tier 4">T4</button>
                    <button type="button" class="mini-badge-btn ' . $sINF . '" data-pref="estoque_inf" title="Infraestrutura">INF</button>
                </div>
            </div>

            <div class="ml-2">
                <i class="fas fa-times-circle text-muted pointer remove-estoque-recipient" title="' . __('Remover Alerta de Estoque') . '" style="font-size: 1rem;"></i>
            </div>
        </div>';
    }
} else {
    echo '<div class="p-3 text-center text-muted small">' . __('Nenhum destinatário de estoque configurado.') . '</div>';
}
?>
<style>
/* Reutilizando as classes mini-badge se não existirem no CSS global, 
   mas garantindo que o visual seja consistente com o card de e-mail */
.mini-badge-btn {
    width: 24px;
    height: 24px;
    padding: 0;
    font-size: 0.65rem;
    font-weight: bold;
    border-radius: 4px;
    border: 1px solid #e3e6f0;
    background-color: #f8f9fc;
    color: #b7b9cc;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.mini-badge-btn.active {
    background-color: #36b9cc !important;
    border-color: #36b9cc !important;
    color: #fff !important;
}
.mini-badge-btn:hover {
    filter: brightness(0.9);
}
</style>
