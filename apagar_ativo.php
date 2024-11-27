<?php
// Dados de conexão com o banco de dados
include 'conexao.php';

try {
    // Obtém o valor de $id da URL e valida como inteiro
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        throw new Exception("ID inválido.");
    }

    // Verificar se o ativo existe antes de excluir
    $sql_check = "SELECT * FROM ativos WHERE id_asset = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        throw new Exception("Ativo não encontrado.");
    }

    // Excluir o ativo da tabela 'ativos'
    $sql_delete = "DELETE FROM ativos WHERE id_asset = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id);

    if (!$stmt_delete->execute()) {
        throw new Exception("Erro ao deletar registro: " . $stmt_delete->error);
    }

    // Redirecionar após sucesso
    echo "<script>
            alert('Registro excluído com sucesso.');
            window.location.href = 'equipamentos.php';
          </script>";
} catch (Exception $e) {
    // Mensagem de erro em caso de falha
    echo "<script>
            alert('Erro: " . $e->getMessage() . "');
            window.location.href = 'equipamentos.php';
          </script>";
}

// Fecha a conexão
$conn->close();
?>
