<?php
// Conexão com o banco de dados
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_fornecedor = isset($_POST['id_fornecedor']) ? intval($_POST['id_fornecedor']) : 0;
    
    if ($id_fornecedor <= 0) {
        header("Location: fornecedores.php?msg=" . urlencode('ID Inválido'));
        exit;
    }

    $nomeEmpresa = mysqli_real_escape_string($conn, $_POST['nomeEmpresa']);
    $cnpj = mysqli_real_escape_string($conn, $_POST['cnpj']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telefone = mysqli_real_escape_string($conn, $_POST['telefone']);
    $servico = mysqli_real_escape_string($conn, $_POST['servico']);
    $site = mysqli_real_escape_string($conn, $_POST['site']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $update_image = "";
    // Verificar se foi enviada uma nova imagem
    if (!empty($_FILES['imagem']['name'])) {
        $diretorio = "assets/img/fornecedores/";
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0777, true);
        }

        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $novoNome = "fornecedor_" . $id_fornecedor . "_" . time() . "." . $extensao;
        $caminhoCompleto = $diretorio . $novoNome;

        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoCompleto)) {
            $update_image = ", imagem='$caminhoCompleto'";
        }
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
        $update_image
    WHERE id_fornecedor = '$id_fornecedor'";

    $update = mysqli_query($conn, $query);

    if ($update) {
        header("Location: fornecedores.php?msg=" . urlencode('Atualizado com sucesso!'));
        exit();
    } else {
        header("Location: fornecedores.php?msg=" . urlencode('Erro ao atualizar: ') . mysqli_error($conn));
        exit();
    }
}
?>
