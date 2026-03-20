<?php
/**
 * PROCESSADOR DE CENTRO DE CUSTO: inserir_centro_de_custo.php
 * Cria novos setores/centros de custo para organização financeira e de ativos.
 */
include 'conexao.php';

$nomeSetor = mysqli_real_escape_string($conn, $_POST['nomeSetor']);
$codigo = mysqli_real_escape_string($conn, $_POST['codigo']);
$ramal = mysqli_real_escape_string($conn, $_POST['ramal']);
$unidade = mysqli_real_escape_string($conn, $_POST['unidade']);
$emailGestor = mysqli_real_escape_string($conn, $_POST['emailGestor']);
$gestor = mysqli_real_escape_string($conn, $_POST['gestor']);
$status = mysqli_real_escape_string($conn, $_POST['status']);
$descricao = isset($_POST['descricao']) ? mysqli_real_escape_string($conn, $_POST['descricao']) : '';

$sql = "INSERT INTO centro_de_custo (nomeSetor, codigo, ramal, unidade, emailGestor, gestor, status, descricao)
    VALUES ('$nomeSetor', '$codigo', '$ramal', '$unidade', '$emailGestor', '$gestor', '$status', '$descricao')";

if (mysqli_query($conn, $sql)) {
    echo "<script>
            alert('" . __('Centro de Custo cadastrado com sucesso!') . "');
            window.location.href = 'centros_de_custo.php';
          </script>";
    exit();
} else {
    echo "<script>
            alert('" . __('Erro ao cadastrar Centro de Custo: ') . "' + " . json_encode(mysqli_error($conn)) . ");
            window.history.back();
          </script>";
}
?>