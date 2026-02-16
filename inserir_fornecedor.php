<?php
include 'conexao.php';


$nomeEmpresa = $_POST['nomeEmpresa'];
$cnpj = $_POST['cnpj'];
$email = $_POST['email'];
$telefone = $_POST['telefone'];
$servico = $_POST['servico'];
$site = $_POST['site'];
$status = $_POST['status'];

$sql = "INSERT INTO fornecedor (nomeEmpresa, cnpj, email, telefone, servico, site, status)
    VALUES ('$nomeEmpresa', '$cnpj', '$email', '$telefone', '$servico', '$site', '$status')";

$inserir = mysqli_query($conn, $sql);

if ($inserir) {
    echo "<script>
            alert('Fornecedor cadastrado com sucesso!');
            window.location.href = 'fornecedores.php';
          </script>";
    exit();
} else {
    echo "Erro ao inserir dados: " . mysqli_error($conn);
}

?>
