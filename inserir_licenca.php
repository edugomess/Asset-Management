<?php
include 'auth.php';
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $software = mysqli_real_escape_string($conn, $_POST['software']);
    $fabricante = mysqli_real_escape_string($conn, $_POST['fabricante']);
    $tipo = mysqli_real_escape_string($conn, $_POST['tipo']);
    $chave = mysqli_real_escape_string($conn, $_POST['chave']);
    $quantidade_total = intval($_POST['quantidade_total']);
    $valor_unitario = floatval($_POST['valor_unitario']);
    $data_aquisicao = !empty($_POST['data_aquisicao']) ? $_POST['data_aquisicao'] : null;
    $data_expiracao = !empty($_POST['data_expiracao']) ? $_POST['data_expiracao'] : null;

    $sql = "INSERT INTO licencas (software, fabricante, tipo, chave, quantidade_total, valor_unitario, data_aquisicao, data_expiracao, status) 
            VALUES ('$software', '$fabricante', '$tipo', '$chave', $quantidade_total, $valor_unitario, " .
        ($data_aquisicao ? "'$data_aquisicao'" : "NULL") . ", " .
        ($data_expiracao ? "'$data_expiracao'" : "NULL") . ", 'Ativa')";

    if ($conn->query($sql) === TRUE) {
        header("Location: licencas.php?status=success");
    } else {
        echo "Erro ao cadastrar licença: " . $conn->error;
    }
}
$conn->close();
?>