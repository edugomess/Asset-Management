<?php
// Dados de conexão com o banco de dados
include 'conexao.php';

// Obtém o valor de $id da URL
$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Certifique-se de que $id é um número inteiro para evitar injeção de SQL
$id = intval($id);

// Executa a consulta diretamente
$result = mysqli_query($conn, "DELETE FROM fornecedor WHERE id_fornecedor = $id");

// Verifica se a consulta foi bem-sucedida
if ($result) {
    echo "Fornecedor deletado com sucesso";
} else {
    echo "Erro ao deletar registro: " . mysqli_error($conn);
}

// Fecha a conexão
$conn->close();



if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
} else {
    die("ID não fornecido.");
}

// Exibir a mensagem de confirmação
echo "<script>
        if (confirm('Isso irá apagar um registro! Deseja continuar?')) {
            window.location.href = 'confirmar_exclusao.php?id=$id'; // Redireciona para confirmar exclusão
        } else {
            window.location.href = 'fornecedores.php'; // Redireciona para a página desejada
        }
      </script>";
exit();
?>



