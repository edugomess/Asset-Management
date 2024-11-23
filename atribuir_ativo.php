<?php
include 'conexao.php'; // Inclui a conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitiza os dados recebidos para evitar problemas de segurança
    $ativo_id = isset($_POST['ativo_id']) ? (int)$_POST['ativo_id'] : 0;
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';

    // Valida os dados recebidos
    if ($ativo_id <= 0 || empty($usuario)) {
        echo "Dados inválidos. Por favor, verifique o ID do ativo e o nome do usuário.";
        exit;
    }

    // Busca o ID do usuário baseado no nome fornecido
    $query_usuario = "SELECT id FROM usuarios WHERE nome = ?";
    $stmt = $conn->prepare($query_usuario);

    if (!$stmt) {
        echo "Erro ao preparar consulta para buscar o usuário: " . $conn->error;
        exit;
    }

    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $result_usuario = $stmt->get_result();

    if ($result_usuario->num_rows > 0) {
        $row_usuario = $result_usuario->fetch_assoc();
        $usuario_id = $row_usuario['id'];

        // Atualiza o registro do ativo para atribuí-lo ao usuário
        $query = "UPDATE ativos SET assigned_to = ? WHERE id_asset = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            echo "Erro ao preparar consulta para atualizar o ativo: " . $conn->error;
            exit;
        }

        $stmt->bind_param('ii', $usuario_id, $ativo_id);

        if ($stmt->execute()) {
            echo "Ativo atribuído com sucesso!";
        } else {
            echo "Erro ao atribuir ativo: " . $stmt->error;
        }
    } else {
        echo "Usuário não encontrado.";
    }

    // Fecha o statement e a conexão com o banco de dados
    $stmt->close();
    $conn->close();
} else {
    echo "Método de requisição inválido. Use POST.";
}
?>
