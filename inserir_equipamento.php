<?php
include 'conexao.php';

$categoria = $_POST['categoria'];
$fabricante = $_POST['fabricante'];
$modelo = $_POST['modelo'];
$tag = $_POST['tag'];
$hostName = $_POST['hostName'];
$ip = $_POST['ip'];
$macAdress = $_POST['macAdress'];
$status = $_POST['status'];
$dataAtivacao = $_POST['dataAtivacao'];
$centroDeCusto = $_POST['centroDeCusto'];
$descricao = $_POST['descricao'];

$sql = "INSERT INTO ativos (categoria, fabricante, modelo, tag, hostName, ip, macAdress, status, dataAtivacao, centroDeCusto, descricao)
        VALUES ('$categoria', '$fabricante', '$modelo', '$tag', '$hostName', '$ip', '$macAdress', '$status', '$dataAtivacao', '$centroDeCusto', '$descricao')";

$inserir = mysqli_query($conn, $sql);

if ($inserir) {
    echo "<script>
            alert('Ativo cadastrado com sucesso!');
            window.location.href = 'equipamentos.php';
          </script>";
    exit();
} else {
    echo "Erro ao inserir dados: " . mysqli_error($conn);
}
?>
