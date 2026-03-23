<?php
include 'conexao.php';
$categoria = 'Monitor';
$sql_check = "SELECT * FROM categoria WHERE categoria = '$categoria'";
$res_check = $conn->query($sql_check);
if ($res_check->num_rows == 0) {
    if ($conn->query("INSERT INTO categoria (categoria) VALUES ('$categoria')")) {
        echo "Categoria '$categoria' adicionada com sucesso!";
    } else {
        echo "Erro ao adicionar categoria: " . $conn->error;
    }
} else {
    echo "Categoria '$categoria' já existe!";
}
$conn->close();
?>