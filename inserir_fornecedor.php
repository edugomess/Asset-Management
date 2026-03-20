<?php
/**
 * PROCESSADOR DE FORNECEDOR: inserir_fornecedor.php
 * Salva os dados cadastrais de novas empresas parceiras e redireciona para a listagem.
 */
include 'conexao.php';


$nomeEmpresa = mysqli_real_escape_string($conn, $_POST['nomeEmpresa']);
$cnpj = mysqli_real_escape_string($conn, $_POST['cnpj']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$telefone = mysqli_real_escape_string($conn, $_POST['telefone']);
$servico = mysqli_real_escape_string($conn, $_POST['servico']);
$site = mysqli_real_escape_string($conn, $_POST['site']);
$status = mysqli_real_escape_string($conn, $_POST['status']);

// Upload da imagem ou uso do placeholder padrão
$imagem = '/assets/img/no-image.png'; // Valor padrão solicitado pelo usuário

if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'assets/img/fornecedores/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileInfo = pathinfo($_FILES['imagem']['name']);
    $extension = strtolower($fileInfo['extension']);
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($extension, $allowedExtensions)) {
        $newFileName = 'fornecedor_' . time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $targetPath)) {
            $imagem = '/' . $targetPath;
        }
    }
}

$sql = "INSERT INTO fornecedor (nomeEmpresa, cnpj, email, telefone, servico, site, status, imagem)
    VALUES ('$nomeEmpresa', '$cnpj', '$email', '$telefone', '$servico', '$site', '$status', '$imagem')";

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