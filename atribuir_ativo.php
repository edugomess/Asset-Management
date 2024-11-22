<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ativo_id = $_POST['ativo_id'];
    $usuario = $_POST['usuario'];

    // Aqui, você precisa adaptar a consulta para buscar o ID do usuário com base no nome ou qualquer outro critério
    $query_usuario = "SELECT id FROM usuarios WHERE nome = ?";
    $stmt = $conn->prepare($query_usuario);
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $result_usuario = $stmt->get_result();
    $row_usuario = $result_usuario->fetch_assoc();

    if ($row_usuario) {
        $usuario_id = $row_usuario['id'];

        // Atualiza o registro do ativo para atribuí-lo ao usuário
        $query = "UPDATE ativos SET usuario_id = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $usuario_id, $ativo_id);
        if ($stmt->execute()) {
            echo "Ativo atribuído com sucesso!";
        } else {
            echo "Erro ao atribuir ativo.";
        }
    } else {
        echo "Usuário não encontrado.";
    }

    $stmt->close();
    $conn->close();
}
?>
