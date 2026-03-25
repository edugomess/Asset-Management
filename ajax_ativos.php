<?php
include 'auth.php';
include 'conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => __('Método inválido.')]);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$id_asset = isset($_POST['id_asset']) ? (int) $_POST['id_asset'] : 0;
$admin_id = isset($_SESSION['id_usuarios']) ? $_SESSION['id_usuarios'] : NULL;

if ($id_asset <= 0) {
    echo json_encode(['success' => false, 'message' => __('ID do ativo inválido.')]);
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
            echo json_encode(['success' => false, 'message' => __('Usuário inválido.')]);
            exit;
        }

        $stmt = $conn->prepare("UPDATE ativos SET assigned_to = ?, id_local = NULL, status = 'Em uso' WHERE id_asset = ?");
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

    case 'assign_local':
        $id_local = isset($_POST['id_local']) ? (int) $_POST['id_local'] : 0;
        if ($id_local <= 0) {
            echo json_encode(['success' => false, 'message' => __('Local inválido.')]);
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE ativos SET id_local = ?, assigned_to = NULL, status = 'Em uso' WHERE id_asset = ?");
        $stmt->bind_param('ii', $id_local, $id_asset);
        
        if ($stmt->execute()) {
            recordHistory($conn, $id_asset, $admin_id, 'Atribuição', "Ativo atribuído a um Local (ID $id_local)");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        $stmt->close();
        break;

    case 'unassign':
        $stmt = $conn->prepare("UPDATE ativos SET assigned_to = NULL, id_local = NULL, status = 'Disponível' WHERE id_asset = ?");
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
        $tipo = isset($_POST['tipo_manutencao']) ? $_POST['tipo_manutencao'] : 'Reparo';
        $observacoes = isset($_POST['observacoes']) ? trim($_POST['observacoes']) : '';
        $item_trocado = isset($_POST['item_trocado']) ? trim($_POST['item_trocado']) : '';
        $detalhes_update = isset($_POST['detalhes_update']) ? trim($_POST['detalhes_update']) : '';
        $cat_upgrade = isset($_POST['categoria_upgrade']) ? $_POST['categoria_upgrade'] : NULL;
        $val_upgrade = isset($_POST['valor_upgrade']) ? (float)$_POST['valor_upgrade'] : 0.00;

        if (empty($observacoes) && $tipo === 'Reparo') {
            echo json_encode(['success' => false, 'message' => __('Observações são obrigatórias para reparo.')]);
            exit;
        }

        $conn->begin_transaction();
        try {
            // Update asset status
            $stmt = $conn->prepare("UPDATE ativos SET status = 'Inativo' WHERE id_asset = ?");
            $stmt->bind_param('i', $id_asset);
            $stmt->execute();

            // Insert into maintenance table inclusive new dynamic fields
            $stmt_m = $conn->prepare("INSERT INTO manutencao (id_asset, data_inicio, tipo_manutencao, categoria_upgrade, observacoes, item_trocado, detalhes_update, valor_upgrade, status_manutencao) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, 'Em Manutenção')");
            $stmt_m->bind_param('isssssd', $id_asset, $tipo, $cat_upgrade, $observacoes, $item_trocado, $detalhes_update, $val_upgrade);
            $stmt_m->execute();
            $stmt_m->close();

            // Sincronização automática de hardware para Upgrades
            if ($tipo === 'Upgrade') {
                // Busca valores atuais para comparação no histórico
                $stmt_curr = $conn->prepare("SELECT memoria, armazenamento FROM ativos WHERE id_asset = ?");
                $stmt_curr->bind_param('i', $id_asset);
                $stmt_curr->execute();
                $res_curr = $stmt_curr->get_result();
                $old_data = $res_curr->fetch_assoc();
                $stmt_curr->close();

                if ($cat_upgrade === 'Memória') {
                    $old_val = $old_data['memoria'] ?: 'Nao informado';
                    $stmt_up = $conn->prepare("UPDATE ativos SET memoria = ? WHERE id_asset = ?");
                    $stmt_up->bind_param('si', $item_trocado, $id_asset);
                    if ($stmt_up->execute()) {
                        recordHistory($conn, $id_asset, $admin_id, 'Atualização Técnica', "Hardware atualizado via Upgrade: Memória RAM alterada de [$old_val] para [$item_trocado].");
                    }
                    $stmt_up->close();
                } elseif ($cat_upgrade === 'Armazenamento') {
                    $old_val = $old_data['armazenamento'] ?: 'Nao informado';
                    // Atualiza tanto a capacidade quanto a tecnologia (tipo_armazenamento)
                    $stmt_up = $conn->prepare("UPDATE ativos SET armazenamento = ?, tipo_armazenamento = ? WHERE id_asset = ?");
                    $stmt_up->bind_param('ssi', $detalhes_update, $item_trocado, $id_asset);
                    if ($stmt_up->execute()) {
                        recordHistory($conn, $id_asset, $admin_id, 'Atualização Técnica', "Hardware atualizado via Upgrade: Armazenamento alterado para [$item_trocado $detalhes_update].");
                    }
                    $stmt_up->close();
                }
            }

            $hist_details = "[$tipo] " . ($tipo === 'Upgrade' ? "$cat_upgrade: " : "") . $observacoes;
            if (!empty($item_trocado)) $hist_details .= " | Peça/Módulo: $item_trocado";
            if (!empty($detalhes_update)) $hist_details .= " | Detalhes: $detalhes_update";
            if ($val_upgrade > 0) $hist_details .= " | Valor: R$ " . number_format($val_upgrade, 2, ',', '.');

            recordHistory($conn, $id_asset, $admin_id, 'Manutenção', $hist_details);

            // ALERTAS (Background - Email e WhatsApp)
            $php_path = 'c:\xampp\php\php.exe';
            $script_path = 'c:\xampp\htdocs\processar_alertas.php';
            $alert_obs = $hist_details;
            $cmd = "start /B $php_path $script_path manutencao $id_asset \"$alert_obs\" > NUL 2>&1";
            pclose(popen($cmd, "r"));

            $conn->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'release_maintenance':
    case 'finish_maintenance':
        $id_m = isset($_POST['id_manutencao']) ? intval($_POST['id_manutencao']) : 0;
        $id_a = isset($_POST['id_asset']) ? intval($_POST['id_asset']) : 0;
        
        $conn->begin_transaction();
        try {
            if ($id_m > 0) {
                // Busca o ID do ativo associado para o histórico posterior
                $stmt_find = $conn->prepare("SELECT id_asset FROM manutencao WHERE id_manutencao = ?");
                $stmt_find->bind_param('i', $id_m);
                $stmt_find->execute();
                $id_a = $stmt_find->get_result()->fetch_assoc()['id_asset'] ?? 0;
                $stmt_find->close();

                $stmt_m = $conn->prepare("UPDATE manutencao SET status_manutencao = 'Concluído', data_fim = NOW() WHERE id_manutencao = ?");
                $stmt_m->bind_param('i', $id_m);
            } else {
                $stmt_m = $conn->prepare("UPDATE manutencao SET status_manutencao = 'Concluído', data_fim = NOW() 
                                        WHERE id_asset = ? AND status_manutencao = 'Em Manutenção'");
                $stmt_m->bind_param('i', $id_a);
            }
            
            $stmt_m->execute();

            if ($stmt_m->affected_rows > 0 && $id_a > 0) {
                // Update asset status to Ativo
                $stmt = $conn->prepare("UPDATE ativos SET status = 'Ativo' WHERE id_asset = ?");
                $stmt->bind_param('i', $id_a);
                $stmt->execute();

                recordHistory($conn, $id_a, $admin_id, 'Fim de Manutenção', "Manutenção concluída e ativo liberado via perfil.");
                $conn->commit();
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("Nenhuma manutenção ativa encontrada ou identificada.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => __('Ação desconhecida.')]);
        break;
}

$conn->close();