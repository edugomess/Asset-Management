<?php
include 'conexao.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
} else {
    die("ID não fornecido.");
}

// Executa a consulta diretamente
$result = mysqli_query($conn, "DELETE FROM fornecedor WHERE id_fornecedor = $id");

// Verifica se a consulta foi bem-sucedida
if ($result) {
    echo "<script>
            alert('Fornecedor deletado com sucesso');
            window.location.href = 'fornecedores.php';
          </script>";
} else {
    echo "<script>
            alert('Erro ao deletar registro: " . mysqli_error($conn) . "');
            window.location.href = 'fornecedores.php';
          </script>";
}

// Fecha a conexão
$conn->close();
?>
