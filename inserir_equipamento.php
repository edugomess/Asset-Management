<?php
include 'conexao.php';
session_start();

$categoria = $_POST['categoria'];
$fabricante = $_POST['fabricante'];
$modelo = $_POST['modelo'];
$tag = $_POST['tag'];
$hostName = $_POST['hostName'];
$valor = $_POST['valor'];
$macAdress = $_POST['macAdress'];
$status = $_POST['status'];
$dataAtivacao = $_POST['dataAtivacao'];
$centroDeCusto = $_POST['centroDeCusto'];
$descricao = $_POST['descricao'];

$imagem = '';
if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'assets/img/ativos/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileInfo = pathinfo($_FILES['imagem']['name']);
    $extension = strtolower($fileInfo['extension']);
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($extension, $allowedExtensions)) {
        // We might need to generate a temp ID or just use timestamp since we don't have asset ID yet
        $newFileName = 'ativo_new_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $targetPath)) {
            $imagem = '/' . $targetPath;
        }
    }
}

$sql = "INSERT INTO ativos (categoria, fabricante, modelo, tag, hostName, valor, macAdress, status, centroDeCusto, descricao, imagem, dataAtivacao)
        VALUES ('$categoria', '$fabricante', '$modelo', '$tag', '$hostName', '$valor', '$macAdress', '$status', '$centroDeCusto', '$descricao', '$imagem', '$dataAtivacao')";

$inserir = mysqli_query($conn, $sql);

if ($inserir) {
    $ativo_id = mysqli_insert_id($conn);
    $usuario_id = isset($_SESSION['id_usuarios']) ? $_SESSION['id_usuarios'] : 'NULL'; // Assumes session is started
    $acao = 'Criação';
    $detalhes = 'Ativo criado no sistema.';

    $sql_historico = "INSERT INTO historico_ativos (ativo_id, usuario_id, acao, detalhes) VALUES ('$ativo_id', $usuario_id, '$acao', '$detalhes')";
    mysqli_query($conn, $sql_historico);

    echo "<script>
            alert('Ativo cadastrado com sucesso!');
            window.location.href = 'equipamentos.php';
          </script>";
    exit();
}
else {
    echo "Erro ao inserir dados: " . mysqli_error($conn);
}
?>
