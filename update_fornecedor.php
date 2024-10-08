<?php
// Conexão com o banco de dados
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id_fornecedor'])) {
        $id_fornecedor = $_POST['id_fornecedor'];
    } else {
        echo 'id_fornecedor não está definido.';
        exit;
    }

    $nomeEmpresa = $_POST['nomeEmpresa'];
    $cnpj = $_POST['cnpj'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $servico = $_POST['servico'];
    $site = $_POST['site'];
    $status = $_POST['status'];

    // Verificar se foi enviada uma nova imagem
    if (!empty($_FILES['imagem']['name'])) {
        // Processar a imagem (salvar no servidor, etc.)
    }

    // Atualizar no banco de dados
    $query = "UPDATE fornecedor 
    SET  
        nomeEmpresa='$nomeEmpresa', 
        cnpj='$cnpj', 
        email='$email', 
        telefone='$telefone', 
        servico='$servico', 
        site='$site',
        status='$status'
    WHERE id_fornecedor = '$id_fornecedor'";  // Removi a vírgula extra aqui

    $update = mysqli_query($conn, $query);

    if ($update) {
        echo "<script>
                alert('Fornecedor atualizado com sucesso!');
                window.location.href = 'fornecedores.php';
              </script>";
        exit();
    } else {
        echo "Erro ao atualizar dados: " . mysqli_error($conn);
    }
}
?>
