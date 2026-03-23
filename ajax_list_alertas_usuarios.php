<?php
require 'conexao.php';

$sql = "SELECT au.*, u.nome, u.sobrenome, u.email 
        FROM alertas_usuarios au 
        JOIN usuarios u ON au.usuario_id = u.id_usuarios 
        ORDER BY u.nome ASC";

$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $name = htmlspecialchars(trim($row['nome'] . ' ' . $row['sobrenome']));
        $initials = strtoupper(substr($row['nome'], 0, 1) . (isset($row['sobrenome'][0]) ? substr($row['sobrenome'], 0, 1) : ''));

        // Ativos
        $isActiveChamados = ($row['recebe_chamados'] ?? 0) == 1 ? 'active' : '';
        $isActiveManutencao = ($row['recebe_manutencao'] ?? 0) == 1 ? 'active' : '';

        $cP1 = ($row['prioridade_p1'] ?? 1) == 1 ? 'active' : '';
        $cP2 = ($row['prioridade_p2'] ?? 1) == 1 ? 'active' : '';
        $cP3 = ($row['prioridade_p3'] ?? 1) == 1 ? 'active' : '';
        $cP4 = ($row['prioridade_p4'] ?? 1) == 1 ? 'active' : '';

        $cInc = ($row['tipo_incidente'] ?? 1) == 1 ? 'active' : '';
        $cReq = ($row['tipo_requisicao'] ?? 1) == 1 ? 'active' : '';
        $cMud = ($row['tipo_mudanca'] ?? 1) == 1 ? 'active' : '';

        echo '
        <div class="recipient-item" data-uid="' . $row['usuario_id'] . '">
            <div class="d-flex align-items-center recipient-info">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 35px; height: 35px; font-size: 0.8rem; font-weight: bold; margin-right: 12px;">
                    ' . $initials . '
                </div>
                <div style="min-width: 0;">
                    <div class="font-weight-bold text-dark text-truncate" style="font-size: 0.85rem;" title="' . $name . '">' . $name . '</div>
                    <div class="text-muted text-truncate" style="font-size: 0.75rem;" title="' . htmlspecialchars($row['email']) . '">' . htmlspecialchars($row['email']) . '</div>
                </div>
            </div>
            
            <div class="recipient-actions">
                <!-- Tipo (Chamado x Manutenção) -->
                <div class="recipient-badge-group bg-light rounded px-2 py-1 border d-flex align-items-center">
                    <i class="fas fa-ticket-alt mini-icon-btn ' . $isActiveChamados . '" data-pref="recebe_chamados" title="Novos Chamados"></i>
                    <div class="mx-1 text-muted" style="width: 1px; height: 12px; background: #e3e6f0;"></div>
                    <i class="fas fa-tools mini-icon-btn ' . $isActiveManutencao . '" data-pref="recebe_manutencao" title="Manutenções"></i>
                </div>

                <!-- Prioridade -->
                <div class="recipient-badge-group">
                    <button type="button" class="mini-badge-btn ' . $cP1 . '" data-pref="prioridade_p1" title="Crítica" style="background-color: #8b0000 !important;">1</button>
                    <button type="button" class="mini-badge-btn bg-danger ' . $cP2 . '" data-pref="prioridade_p2" title="Alta">2</button>
                    <button type="button" class="mini-badge-btn bg-warning ' . $cP3 . '" data-pref="prioridade_p3" title="Média">3</button>
                    <button type="button" class="mini-badge-btn bg-success ' . $cP4 . '" data-pref="prioridade_p4" title="Baixa" style="background-color: #1cc88a !important;">4</button>
                </div>
                
                <!-- Categoria -->
                <div class="recipient-badge-group">
                    <button type="button" class="mini-badge-btn bg-info ' . $cInc . '" data-pref="tipo_incidente" title="Incidente">I</button>
                    <button type="button" class="mini-badge-btn bg-secondary ' . $cMud . '" data-pref="tipo_mudanca" title="Mudança" style="background-color: #6610f2 !important;">M</button>
                    <button type="button" class="mini-badge-btn bg-dark ' . $cReq . '" data-pref="tipo_requisicao" title="Requisição" style="background-color: #858796 !important;">R</button>
                </div>
                
                <i class="fas fa-times-circle text-muted mini-icon-btn remove-recipient" title="Remover Recebedor" style="font-size: 1.1rem; margin-left: 5px;"></i>
            </div>
        </div>';
    }
} else {
    echo '<div class="p-3 text-center text-muted small">Nenhum destinatário ativo configurado.</div>';
}
?>