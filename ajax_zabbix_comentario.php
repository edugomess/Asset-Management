<?php
require 'conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
    $falso_positivo = isset($_POST['falso_positivo']) && $_POST['falso_positivo'] == '1' ? 1 : 0;

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE zabbix_historico_alertas SET comentario_usuario = ?, falso_positivo = ? WHERE id = ?");
        $stmt->bind_param("sii", $comentario, $falso_positivo, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Comentário salvo com sucesso.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco de dados.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'ID inválido.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
}
?>
