<?php
include 'auth.php';
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id_licenca']);
    $software = mysqli_real_escape_string($conn, $_POST['software']);
    $fabricante = mysqli_real_escape_string($conn, $_POST['fabricante']);
    $tipo = mysqli_real_escape_string($conn, $_POST['tipo']);
    $chave = mysqli_real_escape_string($conn, $_POST['chave']);
    $quantidade_total = intval($_POST['quantidade_total']);
    $valor_unitario = floatval($_POST['valor_unitario']);
    $data_aquisicao = !empty($_POST['data_aquisicao']) ? "'" . mysqli_real_escape_string($conn, $_POST['data_aquisicao']) . "'" : "NULL";
    $data_expiracao = !empty($_POST['data_expiracao']) ? "'" . mysqli_real_escape_string($conn, $_POST['data_expiracao']) . "'" : "NULL";
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $id_centro_custo = !empty($_POST['id_centro_custo']) ? intval($_POST['id_centro_custo']) : "NULL";

    $sql = "UPDATE licencas SET 
            software = '$software', 
            fabricante = '$fabricante', 
            tipo = '$tipo', 
            chave = '$chave', 
            quantidade_total = $quantidade_total, 
            valor_unitario = $valor_unitario, 
            id_centro_custo = $id_centro_custo,
            data_aquisicao = $data_aquisicao, 
            data_expiracao = $data_expiracao, 
            status = '$status' 
            WHERE id_licenca = $id";

    if (mysqli_query($conn, $sql)) {
        header("Location: licencas.php?updated=success");
    } else {
        echo "Erro ao atualizar: " . mysqli_error($conn);
    }
}
$conn->close();
?>