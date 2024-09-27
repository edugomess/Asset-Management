<?php
include 'conexao.php';

$nomeSetor = $_POST['nomeSetor'];
$codigo = $_POST['codigo'];
$ramal = $_POST['ramal'];
$unidade = $_POST['unidade'];
$emailGestor = $_POST['emailGestor'];
$gestor = $_POST['gestor'];
$status = $_POST['status'];

$sql = "INSERT INTO centro_de_custo (nomeSetor, codigo, ramal, unidade, emailGestor, gestor, status)
    VALUES ('$nomeSetor', '$codigo', '$ramal', '$unidade', '$emailGestor', '$gestor', '$status')";

$inserir = mysqli_query($conn, $sql);

if ($inserir) {
    echo "<script>
            alert('Centro de Custo cadastrado com sucesso!');
            window.location.href = 'centro_de_custo.php';
          </script>";
    exit();
} else {
    echo "Erro ao inserir dados: " . mysqli_error($conn);
}

?>
