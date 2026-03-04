<?php
require_once 'conexao.php';
require_once 'auth.php'; // Garantir que apenas usuários logados acessem

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $usuario_id = (int) ($_POST['usuario_id'] ?? 0);
        if ($usuario_id > 0) {
            // Verificar se já existe
            $check = $conn->query("SELECT id FROM destinatarios_alertas WHERE usuario_id = $usuario_id");
            if ($check->num_rows == 0) {
                $conn->query("INSERT INTO destinatarios_alertas (usuario_id) VALUES ($usuario_id)");
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Usuário já está na lista.']);
            }
        }
    } elseif ($action === 'remove') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $conn->query("DELETE FROM destinatarios_alertas WHERE id = $id");
            echo json_encode(['status' => 'success']);
        }
    } elseif ($action === 'search') {
        $term = $conn->real_escape_string($_POST['term'] ?? '');
        $res = $conn->query("SELECT id_usuarios as id, nome, sobrenome, email 
                             FROM usuarios 
                             WHERE nome LIKE '%$term%' OR email LIKE '%$term%' 
                             LIMIT 10");
        $users = [];
        while ($row = $res->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode($users);
    }
}
?>