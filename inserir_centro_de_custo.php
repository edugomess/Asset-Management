<?php
include 'conexao.php';

$nomeSetor = $_POST['nomeSetor'];
$codigo = $_POST['codigo'];
$ramal = $_POST['ramal'];
$unidade = $_POST['unidade'];
$emailGestor = $_POST['emailGestor'];
$gestor = $_POST['gestor'];
$status = $_POST['status'];
$descricao = isset($_POST['descricao']) ? $_POST['descricao'] : '';

$imagem = '';

if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'assets/img/centros/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileInfo = pathinfo($_FILES['imagem']['name']);
    $extension = strtolower($fileInfo['extension']);
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($extension, $allowedExtensions)) {
        $newFileName = 'cc_' . time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $targetPath)) {
            $imagem = '/' . $targetPath;
        }
    }
}

$sql = "INSERT INTO centro_de_custo (nomeSetor, codigo, ramal, unidade, emailGestor, gestor, status, descricao, imagem)
    VALUES ('$nomeSetor', '$codigo', '$ramal', '$unidade', '$emailGestor', '$gestor', '$status', '$descricao', '$imagem')";

$inserir = mysqli_query($conn, $sql);

if ($inserir) {
    echo "<script>
            alert('Centro de Custo cadastrado com sucesso!');
            window.location.href = 'centro_de_custo.php';
          </script>";
    exit();
}
else {
    echo "Erro ao inserir dados: " . mysqli_error($conn);
}
?>
