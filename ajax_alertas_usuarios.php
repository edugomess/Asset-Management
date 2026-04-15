<?php
require 'conexao.php'; // Adicione a conexão apropriada

$action = $_POST['action'] ?? '';

if ($action == 'search') {
    $query = mysqli_real_escape_string($conn, $_POST['query']);
    $context = $_POST['context'] ?? 'geral';

    // Busca usuários ativos que ainda não estão na lista DO CONTEXTO selecionado
    $subquery = "SELECT usuario_id FROM alertas_usuarios";
    if ($context === 'estoque') {
        $subquery .= " WHERE recebe_estoque = 1";
    } else {
        $subquery .= " WHERE recebe_chamados = 1 OR recebe_manutencao = 1";
    }

    $sql = "SELECT id_usuarios, nome, sobrenome, email 
            FROM usuarios 
            WHERE (nome LIKE '%$query%' OR sobrenome LIKE '%$query%' OR email LIKE '%$query%') 
              AND status = 'Ativo'
              AND id_usuarios NOT IN ($subquery)
            LIMIT 15";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            echo '<a class="dropdown-item select-recipient" href="#" data-uid="' . $row['id_usuarios'] . '">
                    <div class="small font-weight-bold">' . htmlspecialchars(trim($row['nome'] . ' ' . $row['sobrenome'])) . '</div>
                    <div class="small text-muted">' . htmlspecialchars($row['email']) . '</div>
                  </a>';
        }
    } else {
        echo '<div class="dropdown-item text-muted small">Nenhum usuário correspondente encontrado.</div>';
    }
    exit;
}

if ($action == 'add') {
    $user_id = (int) $_POST['user_id'];
    $context = $_POST['context'] ?? '';

    // Check if user exists and is active
    $check = $conn->query("SELECT id_usuarios FROM usuarios WHERE id_usuarios = $user_id AND status = 'Ativo'");
    if ($check->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Usuário inválido ou inativo.']);
        exit;
    }

    // Configurações padrão ao adicionar
    $recebe_chamados = ($context == 'estoque') ? 0 : 1;
    $recebe_estoque = ($context == 'estoque') ? 1 : 0;

    $sql = "INSERT INTO alertas_usuarios (usuario_id, recebe_chamados, recebe_manutencao, recebe_estoque, estoque_t1, estoque_t2, estoque_t3, estoque_t4, estoque_inf, prioridade_p1, prioridade_p2, prioridade_p3, prioridade_p4, tipo_incidente, tipo_requisicao, tipo_mudanca)
            VALUES ($user_id, $recebe_chamados, 0, $recebe_estoque, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1)";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    exit;
}

if ($action == 'toggle') {
    $user_id = (int) $_POST['user_id'];
    $pref = preg_replace('/[^a-z0-9_]/', '', strtolower($_POST['pref']));
    $state = (int) $_POST['state'];

    $allowed_prefs = [
        'recebe_chamados', 'recebe_manutencao', 'recebe_estoque', 
        'estoque_t1', 'estoque_t2', 'estoque_t3', 'estoque_t4', 'estoque_inf',
        'prioridade_p1', 'prioridade_p2', 'prioridade_p3', 'prioridade_p4', 
        'tipo_incidente', 'tipo_requisicao', 'tipo_mudanca'
    ];

    if (in_array($pref, $allowed_prefs)) {
        $sql = "UPDATE alertas_usuarios SET `$pref` = $state WHERE usuario_id = $user_id";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid preference: ' . $pref]);
    }
    exit;
}

if ($action == 'remove') {
    $user_id = (int) $_POST['user_id'];
    $sql = "DELETE FROM alertas_usuarios WHERE usuario_id = $user_id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
?>