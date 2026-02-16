<?php
// Dados de conexão com o banco de dados
include 'conexao.php';

// Obtém o valor de $id da URL
$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Certifique-se de que $id é um número inteiro para evitar injeção de SQL
$id = intval($id);

// Executa a consulta diretamente
$result = mysqli_query($conn, "DELETE FROM usuarios WHERE id_usuarios = $id");

// Verifica se a consulta foi bem-sucedida
if ($result) {
    echo "Centro de custo deletado com sucesso";
} else {
    echo "Erro ao deletar registro: " . mysqli_error($conn);
}

// Fecha a conexão
$conn->close();

echo "<script>
        if (confirm('Isso irá apagar um registro! Deseja continuar?')) {
            window.location.href = 'usuarios.php';
        } else {
            window.location.href = 'index.php'; // Altere para a página desejada
        }
      </script>";
exit();
?>
