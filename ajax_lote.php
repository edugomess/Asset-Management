<?php
/**
 * AJAX HANDLER: ajax_lote.php
 * Gerencia operações de lotes para leilão com suporte a edição.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
include_once 'conexao.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $nome_lote = $_POST['nome_lote'] ?? 'Lote #' . date('Ymd-His');
            $stmt = $conn->prepare("INSERT INTO lotes_leilao (nome_lote) VALUES (?)");
            $stmt->bind_param('s', $nome_lote);
            if ($stmt->execute()) {
                $id_lote = $stmt->insert_id;
                $_SESSION['active_lote'] = [
                    'id' => $id_lote,
                    'nome' => $nome_lote
                ];
                echo json_encode(['success' => true, 'id_lote' => $id_lote, 'nome_lote' => $nome_lote]);
            } else {
                throw new RuntimeException("Erro ao criar lote: " . $conn->error);
            }
            break;

        case 'add_assets':
            $id_lote = $_POST['id_lote'] ?? ($_SESSION['active_lote']['id'] ?? 0);
            $assets = $_POST['assets'] ?? [];

            if (!$id_lote || empty($assets)) {
                throw new InvalidArgumentException("Dados insuficientes para adicionar ao lote.");
            }

            $placeholders = implode(',', array_fill(0, count($assets), '?'));
            $types = str_repeat('i', count($assets) + 1);
            $params = array_merge([$id_lote], array_map('intval', $assets));

            $stmt = $conn->prepare("UPDATE ativos SET id_lote = ? WHERE id_asset IN ($placeholders)");
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'count' => $stmt->affected_rows]);
            } else {
                throw new RuntimeException("Erro ao adicionar ativos ao lote: " . $conn->error);
            }
            break;

        case 'close':
            $id_lote = (int)($_POST['id_lote'] ?? ($_SESSION['active_lote']['id'] ?? 0));
            if (!$id_lote) {
                throw new InvalidArgumentException("ID do lote não fornecido.");
            }

            $stmt = $conn->prepare("UPDATE lotes_leilao SET status = 'Fechado' WHERE id_lote = ?");
            $stmt->bind_param('i', $id_lote);
            if ($stmt->execute()) {
                if (isset($_SESSION['active_lote']) && $_SESSION['active_lote']['id'] == $id_lote) {
                    unset($_SESSION['active_lote']);
                }
                echo json_encode(['success' => true]);
            } else {
                throw new RuntimeException("Erro ao fechar lote.");
            }
            break;

        case 'get_items':
            $id_lote = (int)($_POST['id_lote'] ?? 0);
            if (!$id_lote) {
                throw new InvalidArgumentException("ID do lote inválido.");
            }

            $query = "SELECT id_asset, modelo, tag, valor, categoria FROM ativos WHERE id_lote = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $id_lote);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            echo json_encode(['success' => true, 'items' => $items]);
            break;

        case 'remove_item':
            $id_asset = (int)($_POST['id_asset'] ?? 0);
            if (!$id_asset) {
                throw new InvalidArgumentException("ID do ativo inválido.");
            }

            $stmt = $conn->prepare("UPDATE ativos SET id_lote = NULL WHERE id_asset = ?");
            $stmt->bind_param('i', $id_asset);
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new RuntimeException("Erro ao remover item do lote.");
            }
            break;

        case 'rename':
            $id_lote = (int)($_POST['id_lote'] ?? 0);
            $novo_nome = $_POST['nome_lote'] ?? '';
            if (!$id_lote || empty($novo_nome)) {
                throw new InvalidArgumentException("Dados inválidos para renomear.");
            }

            $stmt = $conn->prepare("UPDATE lotes_leilao SET nome_lote = ? WHERE id_lote = ?");
            $stmt->bind_param('si', $novo_nome, $id_lote);
            if ($stmt->execute()) {
                if (isset($_SESSION['active_lote']) && $_SESSION['active_lote']['id'] == $id_lote) {
                    $_SESSION['active_lote']['nome'] = $novo_nome;
                }
                echo json_encode(['success' => true]);
            } else {
                throw new RuntimeException("Erro ao renomear lote.");
            }
            break;

        case 'reopen':
            $id_lote = (int)($_POST['id_lote'] ?? 0);
            if (!$id_lote) {
                throw new InvalidArgumentException("ID do lote inválido.");
            }

            $conn->begin_transaction();
            // 1. Reabrir lote selecionado
            $stmt = $conn->prepare("UPDATE lotes_leilao SET status = 'Aberto' WHERE id_lote = ?");
            $stmt->bind_param('i', $id_lote);
            
            // 2. Buscar nome para a sessão
            $res = $conn->query("SELECT nome_lote FROM lotes_leilao WHERE id_lote = $id_lote");
            $row = $res->fetch_assoc();

            if ($stmt->execute()) {
                $_SESSION['active_lote'] = [
                    'id' => $id_lote,
                    'nome' => $row['nome_lote']
                ];
                $conn->commit();
                echo json_encode(['success' => true]);
            } else {
                $conn->rollback();
                throw new RuntimeException("Erro ao reabrir lote.");
            }
            break;

        case 'auction':
            $id_lote = (int)($_POST['id_lote'] ?? 0);
            if (!$id_lote) {
                throw new InvalidArgumentException("ID do lote não fornecido.");
            }

            $conn->begin_transaction();

            // 1. Buscar ativos do lote
            $query = "SELECT * FROM ativos WHERE id_lote = ?";
            $stmtFetch = $conn->prepare($query);
            $stmtFetch->bind_param('i', $id_lote);
            $stmtFetch->execute();
            $result = $stmtFetch->get_result();

            if ($result->num_rows > 0) {
                // 2. Inserir em venda
                $queryVenda = "INSERT INTO venda (id_asset, categoria, fabricante, modelo, tag, hostName, valor, macAdress, status, assigned_to, centroDeCusto, dataAtivacao, descricao, data_venda)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Leiloado', ?, ?, ?, ?, NOW())";
                $stmtVenda = $conn->prepare($queryVenda);

                while ($ativo = $result->fetch_assoc()) {
                    $stmtVenda->bind_param('isssssssisss', 
                        $ativo['id_asset'], $ativo['categoria'], $ativo['fabricante'], $ativo['modelo'], 
                        $ativo['tag'], $ativo['hostName'], $ativo['valor'], $ativo['macAdress'], 
                        $ativo['assigned_to'], $ativo['centroDeCusto'], $ativo['dataAtivacao'], $ativo['descricao']
                    );
                    $stmtVenda->execute();
                }

                // 3. Remover de ativos
                $stmtDel = $conn->prepare("DELETE FROM ativos WHERE id_lote = ?");
                $stmtDel->bind_param('i', $id_lote);
                $stmtDel->execute();

                // 4. Marcar lote como Leiloado
                $stmtMark = $conn->prepare("UPDATE lotes_leilao SET status = 'Leiloado' WHERE id_lote = ?");
                $stmtMark->bind_param('i', $id_lote);
                $stmtMark->execute();

                $conn->commit();
                echo json_encode(['success' => true]);
            } else {
                throw new RuntimeException("Nenhum ativo encontrado para este lote.");
            }
            break;

        default:
            throw new InvalidArgumentException("Ação inválida.");
    }
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
