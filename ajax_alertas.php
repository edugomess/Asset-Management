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
                if ($conn->query("INSERT INTO destinatarios_alertas (usuario_id, prioridade_alta, prioridade_media, prioridade_baixa, recebe_chamados, recebe_manutencao, cat_incidente, cat_mudanca, cat_requisicao) VALUES ($usuario_id, 1, 1, 1, 1, 1, 1, 1, 1)")) {
                    $new_id = $conn->insert_id;
                    $res = $conn->query("SELECT d.*, u.nome, u.sobrenome, u.email FROM destinatarios_alertas d JOIN usuarios u ON d.usuario_id = u.id_usuarios WHERE d.id = $new_id");
                    $recipient = $res->fetch_assoc();
                    echo json_encode(['status' => 'success', 'recipient' => $recipient]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Erro ao adicionar usuário.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Usuário já está na lista.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID de usuário inválido.']);
        }
    } elseif ($action === 'remove') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $conn->query("DELETE FROM destinatarios_alertas WHERE id = $id");
        }
        echo json_encode(['status' => 'success']);
    } elseif ($action === 'update_priority') {
        $id = (int) ($_POST['id'] ?? 0);
        $priority = $_POST['priority'] ?? '';
        $value = (int) ($_POST['value'] ?? 0);

        $allowed_priorities = ['alta', 'media', 'baixa'];
        if ($id > 0 && in_array($priority, $allowed_priorities)) {
            $column = "prioridade_" . $priority;
            $conn->query("UPDATE destinatarios_alertas SET $column = $value WHERE id = $id");
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
        }
    } elseif ($action === 'update_global_priority') {
        $priority = $_POST['priority'] ?? '';
        $value = (int) ($_POST['value'] ?? 0);

        $allowed_priorities = ['alta', 'media', 'baixa'];
        if (in_array($priority, $allowed_priorities)) {
            $column = "whatsapp_prioridade_" . $priority;
            if ($conn->query("UPDATE configuracoes_alertas SET $column = $value WHERE id = 1")) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar banco.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Prioridade inválida.']);
        }
    } elseif ($action === 'toggle_event') {
        $event = $_POST['event'] ?? '';
        $value = (int) ($_POST['value'] ?? 0);

        $allowed_events = ['chamados', 'manutencao'];
        if (in_array($event, $allowed_events)) {
            $column = $event . "_ativo";
            if ($conn->query("UPDATE configuracoes_alertas SET $column = $value WHERE id = 1")) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar banco.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Evento inválido.']);
        }
    } elseif ($action === 'update_user_event') {
        $id = (int) ($_POST['id'] ?? 0);
        $event = $_POST['event'] ?? '';
        $value = (int) ($_POST['value'] ?? 0);

        $allowed_events = ['chamados', 'manutencao'];
        if ($id > 0 && in_array($event, $allowed_events)) {
            $column = "recebe_" . $event;
            if ($conn->query("UPDATE destinatarios_alertas SET $column = $value WHERE id = $id")) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar banco.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
        }
    } elseif ($action === 'update_global_category') {
        $category = $_POST['category'] ?? ''; // incidente, mudanca, requisicao
        $value = (int) ($_POST['value'] ?? 0);

        $allowed_cats = ['incidente', 'mudanca', 'requisicao'];
        if (in_array($category, $allowed_cats)) {
            $column = "cat_" . $category;
            if ($conn->query("UPDATE configuracoes_alertas SET $column = $value WHERE id = 1")) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar banco.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Categoria inválida.']);
        }
    } elseif ($action === 'update_user_category') {
        $id = (int) ($_POST['id'] ?? 0);
        $category = $_POST['category'] ?? ''; // incidente, mudanca, requisicao
        $value = (int) ($_POST['value'] ?? 0);

        $allowed_cats = ['incidente', 'mudanca', 'requisicao'];
        if ($id > 0 && in_array($category, $allowed_cats)) {
            $column = "cat_" . $category;
            if ($conn->query("UPDATE destinatarios_alertas SET $column = $value WHERE id = $id")) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar banco.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
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