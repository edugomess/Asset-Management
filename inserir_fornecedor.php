<?php
/**
 * PROCESSADOR DE FORNECEDOR: inserir_fornecedor.php
 * Salva os dados cadastrais de novas empresas parceiras e redireciona para a listagem.
 */
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

if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('" . __('Fornecedor cadastrado com sucesso!') . "');
                window.location.href = 'fornecedores.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('" . __('Erro ao cadastrar fornecedor: ') . "' + " . json_encode(mysqli_error($conn)) . ");
                window.history.back();
              </script>";
    }

?>