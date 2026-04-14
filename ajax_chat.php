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
        $search = mysqli_real_escape_string($conn, $_GET['q'] ?? '');

        // USERS: Filtrar por Ativo, remover self, e aplicar busca se existir.
        // Regra de Negócio: Se não houver busca, mostrar apenas contatos com interação prévia (ultimo_contato != NULL).
        $sql_users = "SELECT u.id_usuarios, u.nome, u.sobrenome, u.foto_perfil, u.chat_status,
                (SELECT MAX(timestamp) FROM chat_mensagens 
                 WHERE (remetente_id = u.id_usuarios AND destinatario_id = $my_id) 
                    OR (remetente_id = $my_id AND destinatario_id = u.id_usuarios)
                ) as ultimo_contato,
                IF(u.last_seen > NOW() - INTERVAL 5 MINUTE, u.chat_status, 'Offline') as status_atual
            FROM usuarios u
            WHERE u.id_usuarios != $my_id AND u.status = 'Ativo' ";
        
        if (!empty($search)) {
            $sql_users .= " AND (u.nome LIKE '%$search%' OR u.sobrenome LIKE '%$search%' OR u.usuarioAD LIKE '%$search%') ";
        }

        $sql_users .= " HAVING (ultimo_contato IS NOT NULL OR '$search' != '') ";
        $sql_users .= " ORDER BY ultimo_contato DESC, u.nome ASC";

        $res_users = $conn->query($sql_users);
        $users = [];
        while ($row = $res_users->fetch_assoc()) {
            $row['nome_completo'] = $row['nome'] . ' ' . $row['sobrenome'];
            $row['foto'] = !empty($row['foto_perfil']) ? $row['foto_perfil'] : '/assets/img/no-image.png';
            $row['chat_status'] = $row['status_atual']; 
            $row['is_group'] = false;
            $users[] = $row;
        }

        // GRUPOS: Mesmo critério (interação ou busca direta)
        $sql_groups = "SELECT g.id_grupo, g.nome_grupo, g.foto_grupo,
                (SELECT MAX(timestamp) FROM chat_mensagens WHERE grupo_id = g.id_grupo) as ultimo_contato
            FROM chat_grupos g
            JOIN chat_grupo_membros m ON g.id_grupo = m.id_grupo
            WHERE m.usuario_id = $my_id ";

        if (!empty($search)) {
            $sql_groups .= " AND g.nome_grupo LIKE '%$search%' ";
        }

        $sql_groups .= " HAVING (ultimo_contato IS NOT NULL OR '$search' != '') ";
        $sql_groups .= " ORDER BY ultimo_contato DESC, g.nome_grupo ASC";

        $res_groups = $conn->query($sql_groups);
        $groups = [];
        while ($row = $res_groups->fetch_assoc()) {
            $row['nome_completo'] = $row['nome_grupo'];
            $row['foto'] = !empty($row['foto_grupo']) ? $row['foto_grupo'] : '/assets/img/group-no-image.png'; // Placeholder para grupos
            $row['chat_status'] = ''; // Grupos não tem status
            $row['is_group'] = true;
            $row['id_usuarios'] = 'g' . $row['id_grupo']; // Identificador único na lista
            $groups[] = $row;
        }

        echo json_encode(['success' => true, 'users' => array_merge($groups, $users)]);
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
        $grupo_id = (int)($_POST['grupo_id'] ?? 0);
        $msg = mysqli_real_escape_string($conn, $_POST['mensagem'] ?? '');
        $tipo = 'texto';
        $arquivo_url = null;

        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            // ... (upload logic stays same) ...
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

        if (($dest_id > 0 || $grupo_id > 0) && (!empty($msg) || !empty($arquivo_url))) {
            $dest_field = $dest_id > 0 ? "destinatario_id" : "grupo_id";
            $dest_val = $dest_id > 0 ? $dest_id : $grupo_id;
            
            $sql = "INSERT INTO chat_mensagens (remetente_id, $dest_field, mensagem, tipo, arquivo_url) 
                    VALUES ($my_id, $dest_val, '$msg', '$tipo', " . ($arquivo_url ? "'$arquivo_url'" : "NULL") . ")";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
            }
        }
        break;

    case 'fetch':
        $other_id = (int)($_GET['with'] ?? 0);
        $group_id = (int)($_GET['with_group'] ?? 0);

        if ($other_id > 0) {
            mysqli_query($conn, "UPDATE chat_mensagens SET lido = 1 
                                WHERE remetente_id = $other_id AND destinatario_id = $my_id AND lido = 0");
            $sql = "SELECT * FROM chat_mensagens 
                    WHERE (remetente_id = $my_id AND destinatario_id = $other_id)
                       OR (remetente_id = $other_id AND destinatario_id = $my_id)
                    ORDER BY timestamp ASC LIMIT 200";
        } elseif ($group_id > 0) {
            // Atualiza last_read_at do membro do grupo
            mysqli_query($conn, "UPDATE chat_grupo_membros SET last_read_at = NOW() 
                                WHERE id_grupo = $group_id AND usuario_id = $my_id");
            $sql = "SELECT m.*, u.nome, u.sobrenome FROM chat_mensagens m
                    JOIN usuarios u ON m.remetente_id = u.id_usuarios
                    WHERE m.grupo_id = $group_id
                    ORDER BY m.timestamp ASC LIMIT 200";
        }

        if (isset($sql)) {
            $res = mysqli_query($conn, $sql);
            $messages = [];
            while ($row = mysqli_fetch_assoc($res)) {
                $row['is_me'] = ($row['remetente_id'] == $my_id);
                $row['sender_name'] = isset($row['nome']) ? $row['nome'] . ' ' . $row['sobrenome'] : null;
                $row['time_formatted'] = date('H:i', strtotime($row['timestamp']));
                $messages[] = $row;
            }
            echo json_encode(['success' => true, 'messages' => $messages]);
        }
        break;

    case 'create_group':
        $nome = mysqli_real_escape_string($conn, $_POST['nome'] ?? '');
        $membros = $_POST['membros'] ?? []; // Array de IDs
        
        if (!empty($nome) && !empty($membros)) {
            $sql_group = "INSERT INTO chat_grupos (nome_grupo, admin_id) VALUES ('$nome', $my_id)";
            if (mysqli_query($conn, $sql_group)) {
                $gid = mysqli_insert_id($conn);
                // Adiciona o admin como membro também
                if (!in_array($my_id, $membros)) $membros[] = $my_id;
                
                foreach ($membros as $uid) {
                    $uid = (int)$uid;
                    mysqli_query($conn, "INSERT INTO chat_grupo_membros (id_grupo, usuario_id) VALUES ($gid, $uid)");
                }
                echo json_encode(['success' => true, 'grupo_id' => $gid]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao criar grupo.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Dados insuficientes.']);
        }
        break;

    case 'poll':
        // Unread direct messages
        $sql_u = "SELECT remetente_id, COUNT(*) as total 
                FROM chat_mensagens 
                WHERE destinatario_id = $my_id AND lido = 0 
                GROUP BY remetente_id";
        $res_u = mysqli_query($conn, $sql_u);
        $unread = [];
        $total_geral = 0;
        while ($row = mysqli_fetch_assoc($res_u)) {
            $unread[$row['remetente_id']] = (int)$row['total'];
            $total_geral += (int)$row['total'];
        }

        // Unread group messages (messages since last_read_at)
        $sql_g = "SELECT m.grupo_id, COUNT(*) as total
                  FROM chat_mensagens m
                  JOIN chat_grupo_membros gm ON m.grupo_id = gm.id_grupo
                  WHERE gm.usuario_id = $my_id AND m.timestamp > gm.last_read_at
                  AND m.remetente_id != $my_id
                  GROUP BY m.grupo_id";
        $res_g = mysqli_query($conn, $sql_g);
        while ($row = mysqli_fetch_assoc($res_g)) {
            $unread['g' . $row['grupo_id']] = (int)$row['total'];
            $total_geral += (int)$row['total'];
        }

        echo json_encode(['success' => true, 'unread' => $unread, 'total' => $total_geral]);
        break;

    case 'get_group_members':
        $gid = (int)($_GET['id'] ?? 0);
        if ($gid > 0) {
            $g_res = mysqli_query($conn, "SELECT admin_id FROM chat_grupos WHERE id_grupo = $gid");
            $g_data = mysqli_fetch_assoc($g_res);
            $admin_id = $g_data['admin_id'] ?? 0;

            $sql = "SELECT u.id_usuarios, u.nome, u.sobrenome, u.foto_perfil, u.funcao
                    FROM chat_grupo_membros gm
                    JOIN usuarios u ON gm.usuario_id = u.id_usuarios
                    WHERE gm.id_grupo = $gid";
            $res = mysqli_query($conn, $sql);
            $members = [];
            while ($row = mysqli_fetch_assoc($res)) {
                $row['nome_completo'] = $row['nome'] . ' ' . $row['sobrenome'];
                $row['foto'] = !empty($row['foto_perfil']) ? $row['foto_perfil'] : '/assets/img/no-image.png';
                $members[] = $row;
            }
            echo json_encode([
                'success' => true, 
                'members' => $members, 
                'admin_id' => (int)$admin_id,
                'is_admin' => ($admin_id == $my_id)
            ]);
        }
        break;

    case 'remove_member':
        $gid = (int)($_POST['id_grupo'] ?? 0);
        $uid = (int)($_POST['usuario_id'] ?? 0);
        
        // Valida se quem chamou é admin
        $check = mysqli_query($conn, "SELECT admin_id FROM chat_grupos WHERE id_grupo = $gid");
        $group = mysqli_fetch_assoc($check);
        
        if ($group && $group['admin_id'] == $my_id && $uid != $my_id) {
            mysqli_query($conn, "DELETE FROM chat_grupo_membros WHERE id_grupo = $gid AND usuario_id = $uid");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Sem permissão ou operação inválida.']);
        }
        break;

    case 'list_potential_members':
        $gid = (int)($_GET['id_grupo'] ?? 0);
        $sql = "SELECT id_usuarios, nome, sobrenome, foto_perfil
                FROM usuarios 
                WHERE status = 'Ativo' 
                AND id_usuarios NOT IN (SELECT usuario_id FROM chat_grupo_membros WHERE id_grupo = $gid)
                ORDER BY nome ASC";
        $res = mysqli_query($conn, $sql);
        $users = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $row['nome_completo'] = $row['nome'] . ' ' . $row['sobrenome'];
            $row['foto'] = !empty($row['foto_perfil']) ? $row['foto_perfil'] : '/assets/img/no-image.png';
            $users[] = $row;
        }
        echo json_encode(['success' => true, 'users' => $users]);
        break;

    case 'add_member':
        $gid = (int)($_POST['id_grupo'] ?? 0);
        $uid = (int)($_POST['usuario_id'] ?? 0);

        $check = mysqli_query($conn, "SELECT admin_id FROM chat_grupos WHERE id_grupo = $gid");
        $group = mysqli_fetch_assoc($check);

        if ($group && $group['admin_id'] == $my_id) {
            mysqli_query($conn, "INSERT IGNORE INTO chat_grupo_membros (id_grupo, usuario_id) VALUES ($gid, $uid)");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    case 'leave_group':
        $gid = (int)($_POST['id_grupo'] ?? 0);
        
        $check = mysqli_query($conn, "SELECT admin_id FROM chat_grupos WHERE id_grupo = $gid");
        $group = mysqli_fetch_assoc($check);
        
        if ($group) {
            mysqli_query($conn, "DELETE FROM chat_grupo_membros WHERE id_grupo = $gid AND usuario_id = $my_id");
            
            if ($group['admin_id'] == $my_id) {
                // Sucessão: busca o próximo membro mais antigo (ou qualquer um restante)
                $next = mysqli_query($conn, "SELECT usuario_id FROM chat_grupo_membros WHERE id_grupo = $gid LIMIT 1");
                if ($next_data = mysqli_fetch_assoc($next)) {
                    $new_admin = $next_data['usuario_id'];
                    mysqli_query($conn, "UPDATE chat_grupos SET admin_id = $new_admin WHERE id_grupo = $gid");
                }
            }
            echo json_encode(['success' => true]);
        }
        break;

    case 'heartbeat':
        echo json_encode(['success' => true]);
        break;
}

mysqli_close($conn);
?>
