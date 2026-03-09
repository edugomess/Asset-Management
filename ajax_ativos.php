<?php
include 'auth.php';
include 'conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$id_asset = isset($_POST['id_asset']) ? (int) $_POST['id_asset'] : 0;
$admin_id = isset($_SESSION['id_usuarios']) ? $_SESSION['id_usuarios'] : NULL;

if ($id_asset <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID do ativo inválido.']);
    exit;
}

// Funções Auxiliares
function recordHistory($conn, $asset_id, $user_id, $acao, $detalhes)
{
    $sql = "INSERT INTO historico_ativos (ativo_id, usuario_id, acao, detalhes) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiss', $asset_id, $user_id, $acao, $detalhes);
    $stmt->execute();
    $stmt->close();
}

function getUserName($conn, $user_id)
{
    $sql = "SELECT nome FROM usuarios WHERE id_usuarios = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $name = "ID $user_id";
    if ($row = $res->fetch_assoc()) {
        $name = $row['nome'];
    }
    $stmt->close();
    return $name;
}

switch ($action) {
    case 'assign':
        $id_usuario = isset($_POST['id_usuario']) ? (int) $_POST['id_usuario'] : 0;
        if ($id_usuario <= 0) {
            echo json_encode(['success' => false, 'message' => 'Usuário inválido.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE ativos SET assigned_to = ?, status = 'Ativo' WHERE id_asset = ?");
        $stmt->bind_param('ii', $id_usuario, $id_asset);

        if ($stmt->execute()) {
            $userName = getUserName($conn, $id_usuario);
            recordHistory($conn, $id_asset, $admin_id, 'Atribuição', "Ativo atribuído ao usuário: $userName");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        $stmt->close();
        break;

    case 'unassign':
        $stmt = $conn->prepare("UPDATE ativos SET assigned_to = NULL, status = 'Ativo' WHERE id_asset = ?");
        $stmt->bind_param('i', $id_asset);

        if ($stmt->execute()) {
            recordHistory($conn, $id_asset, $admin_id, 'Liberação', "Atribuição removida. Ativo agora está disponível.");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        $stmt->close();
        break;

    case 'send_to_maintenance':
        $observacoes = isset($_POST['observacoes']) ? trim($_POST['observacoes']) : '';
        if (empty($observacoes)) {
            echo json_encode(['success' => false, 'message' => 'Observações são obrigatórias.']);
            exit;
        }

        $conn->begin_transaction();
        try {
            // Update asset status
            $stmt = $conn->prepare("UPDATE ativos SET status = 'Inativo' WHERE id_asset = ?");
            $stmt->bind_param('i', $id_asset);
            $stmt->execute();

            // Insert into maintenance table
            $stmt_m = $conn->prepare("INSERT INTO manutencao (id_asset, data_inicio, observacoes, status_manutencao) VALUES (?, NOW(), ?, 'Em Manutenção')");
            $stmt_m->bind_param('is', $id_asset, $observacoes);
            $stmt_m->execute();

            recordHistory($conn, $id_asset, $admin_id, 'Manutenção', "Enviado para manutenção: $observacoes");

            $conn->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'release_maintenance':
        $conn->begin_transaction();
        try {
            // Finalize maintenance record
            $stmt_m = $conn->prepare("UPDATE manutencao SET status_manutencao = 'Concluído', data_fim = NOW() 
                                    WHERE id_asset = ? AND status_manutencao = 'Em Manutenção'");
            $stmt_m->bind_param('i', $id_asset);
            $stmt_m->execute();

            if ($stmt_m->affected_rows > 0) {
                // Update asset status to Ativo
                $stmt = $conn->prepare("UPDATE ativos SET status = 'Ativo' WHERE id_asset = ?");
                $stmt->bind_param('i', $id_asset);
                $stmt->execute();

                recordHistory($conn, $id_asset, $admin_id, 'Fim de Manutenção', "Manutenção concluída e ativo liberado.");
                $conn->commit();
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("Nenhuma manutenção ativa encontrada para este ativo.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Ação desconhecida.']);
        break;
}

$conn->close();
?>