<?php
/**
 * AJAX HANDLER: ajax_chat.php
 * Gerencia as operações de envio, recebimento, listagem, status e perfis do Chat Interno.
 * Versão Final: Ordenação por relevância e limite de 10MB.
 */
include 'auth.php';
include 'conexao.php';
include 'language.php';

header('Content-Type: application/json');

// Aumentar o limite de upload em tempo de execução (se permitido pelo servidor)
@ini_set('upload_max_filesize', '10M');
@ini_set('post_max_size', '10M');

$action = $_GET['action'] ?? '';
$my_id = $_SESSION['id_usuarios'];

// ATUALIZAÇÃO DE PRESENÇA
mysqli_query($conn, "UPDATE usuarios SET last_seen = NOW() WHERE id_usuarios = $my_id");

switch ($action) {
    case 'list_users':
        // Seleciona usuários ordenando pelas conversas mais recentes
        $sql = "SELECT u.id_usuarios, u.nome, u.sobrenome, u.foto_perfil, u.nivelUsuario, u.status, u.chat_status, u.funcao, u.last_seen,
                    (SELECT MAX(timestamp) FROM chat_mensagens 
                     WHERE (remetente_id = u.id_usuarios AND destinatario_id = $my_id) 
                        OR (remetente_id = $my_id AND destinatario_id = u.id_usuarios)
                    ) as ultimo_contato,
                    IF(u.last_seen > NOW() - INTERVAL 5 MINUTE, u.chat_status, 'Offline') as status_atual
                FROM usuarios u
                WHERE u.id_usuarios != $my_id AND u.status = 'Ativo'
                ORDER BY ultimo_contato DESC, u.nome ASC";
        
        $res = mysqli_query($conn, $sql);
        $users = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $row['nome_completo'] = $row['nome'] . ' ' . $row['sobrenome'];
            $row['foto'] = !empty($row['foto_perfil']) ? $row['foto_perfil'] : '/assets/img/no-image.png';
            $row['chat_status'] = $row['status_atual']; 
            $users[] = $row;
        }
        echo json_encode(['success' => true, 'users' => $users]);
        break;

    case 'get_user_detail':
        $uid = (int)($_GET['id'] ?? 0);
        if ($uid > 0) {
            $sql = "SELECT id_usuarios, nome, sobrenome, usuarioAD, email, setor, funcao, foto_perfil, chat_status, last_seen,
                    IF(last_seen > NOW() - INTERVAL 5 MINUTE, chat_status, 'Offline') as status_atual
                    FROM usuarios WHERE id_usuarios = $uid";
            $res = mysqli_query($conn, $sql);
            if ($row = mysqli_fetch_assoc($res)) {
                $row['nome_completo'] = $row['nome'] . ' ' . $row['sobrenome'];
                $row['foto'] = !empty($row['foto_perfil']) ? $row['foto_perfil'] : '/assets/img/no-image.png';
                $row['chat_status'] = $row['status_atual'];
                echo json_encode(['success' => true, 'user' => $row]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado.']);
            }
        }
        break;

    case 'update_status':
        $new_status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Disponível');
        $valid_statuses = ['Disponível', 'Ausente', 'Ocupado', 'Offline'];
        if (in_array($new_status, $valid_statuses)) {
            $sql = "UPDATE usuarios SET chat_status = '$new_status' WHERE id_usuarios = $my_id";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => true]);
            }
        }
        break;

    case 'get_my_status':
        $sql = "SELECT chat_status FROM usuarios WHERE id_usuarios = $my_id";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($res);
        echo json_encode(['success' => true, 'status' => $row['chat_status'] ?: 'Disponível']);
        break;

    case 'send':
        $dest_id = (int)($_POST['destinatario_id'] ?? 0);
        $msg = mysqli_real_escape_string($conn, $_POST['mensagem'] ?? '');
        $tipo = 'texto';
        $arquivo_url = null;

        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            // Validar tamanho (10MB)
            if ($_FILES['imagem']['size'] > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo excede o limite de 10MB.']);
                exit;
            }

            $uploadDir = 'assets/img/chat/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $originalName = $_FILES['imagem']['name'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $img_exts = ['jpg', 'jpeg', 'png', 'gif'];
            $doc_exts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'zip', 'rar', 'xml'];
            
            if (in_array($ext, $img_exts)) $tipo = 'imagem';
            else if (in_array($ext, $doc_exts)) $tipo = 'arquivo';
            else {
                echo json_encode(['success' => false, 'message' => 'Formato não suportado.']);
                exit;
            }

            $newFileName = uniqid('chat_', true) . '_' . $originalName; 
            $targetPath = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $targetPath)) {
                $arquivo_url = $targetPath;
                if (empty($msg) && $tipo == 'arquivo') $msg = $originalName;
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao mover arquivo.']);
                exit;
            }
        }

        if ($dest_id > 0 && (!empty($msg) || !empty($arquivo_url))) {
            $sql = "INSERT INTO chat_mensagens (remetente_id, destinatario_id, mensagem, tipo, arquivo_url) 
                    VALUES ($my_id, $dest_id, '$msg', '$tipo', " . ($arquivo_url ? "'$arquivo_url'" : "NULL") . ")";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
            }
        }
        break;

    case 'fetch':
        $other_id = (int)($_GET['with'] ?? 0);
        if ($other_id > 0) {
            mysqli_query($conn, "UPDATE chat_mensagens SET lido = 1 
                                WHERE remetente_id = $other_id AND destinatario_id = $my_id AND lido = 0");
            $sql = "SELECT * FROM chat_mensagens 
                    WHERE (remetente_id = $my_id AND destinatario_id = $other_id)
                       OR (remetente_id = $other_id AND destinatario_id = $my_id)
                    ORDER BY timestamp ASC LIMIT 200";
            $res = mysqli_query($conn, $sql);
            $messages = [];
            while ($row = mysqli_fetch_assoc($res)) {
                $row['is_me'] = ($row['remetente_id'] == $my_id);
                $row['time_formatted'] = date('H:i', strtotime($row['timestamp']));
                $messages[] = $row;
            }
            echo json_encode(['success' => true, 'messages' => $messages]);
        }
        break;

    case 'poll':
        $sql = "SELECT remetente_id, COUNT(*) as total 
                FROM chat_mensagens 
                WHERE destinatario_id = $my_id AND lido = 0 
                GROUP BY remetente_id";
        $res = mysqli_query($conn, $sql);
        $unread = [];
        $total_geral = 0;
        while ($row = mysqli_fetch_assoc($res)) {
            $unread[$row['remetente_id']] = (int)$row['total'];
            $total_geral += (int)$row['total'];
        }
        echo json_encode(['success' => true, 'unread' => $unread, 'total' => $total_geral]);
        break;

    case 'heartbeat':
        echo json_encode(['success' => true]);
        break;
}

mysqli_close($conn);
?>
