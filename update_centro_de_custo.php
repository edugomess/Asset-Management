<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_centro_de_custo = intval($_POST['id_centro_de_custo']);
    $nomeSetor = $_POST['nomeSetor'];
    $codigo = $_POST['codigo'];
    $ramal = $_POST['ramal'];
    $unidade = $_POST['unidade'];
    $emailGestor = $_POST['emailGestor'];
    $gestor = $_POST['gestor'];
    $status = $_POST['status'];
    $descricao = isset($_POST['descricao']) ? $_POST['descricao'] : '';

    $imagemUpdate = "";
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
                $imagemUpdate = ", imagem = '$imagem'";
            }
        }
    }

    $sql = "UPDATE centro_de_custo SET 
            nomeSetor = '$nomeSetor',
            codigo = '$codigo',
            ramal = '$ramal',
            unidade = '$unidade',
            emailGestor = '$emailGestor',
            gestor = '$gestor',
            status = '$status',
            descricao = '$descricao'
            $imagemUpdate
            WHERE id_centro_de_custo = $id_centro_de_custo";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Centro de Custo atualizado com sucesso!');
                window.location.href = 'centro_de_custo.php';
              </script>";
    }
    else {
        echo "Erro ao atualizar: " . $conn->error;
    }

    $conn->close();
}
?>
