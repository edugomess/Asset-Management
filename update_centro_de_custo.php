<?php
/**
 * PROCESSAMENTO DE EDIÇÃO DE CENTRO DE CUSTO: update_centro_de_custo.php
 * Atualiza os dados do setor, tratando opcionalmente a troca da imagem representativa.
 */
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
        // Assuming '__' is a function for internationalization, and 'centros_de_custo.php' is the correct redirect target.
        // Also, assuming $conn is a mysqli object, so $conn->error is correct for errors.
        header("Location: centros_de_custo.php?msg=" . urlencode(__('Atualizado com sucesso!')));
    } else {
        header("Location: centros_de_custo.php?msg=" . urlencode(__('Erro ao atualizar: ')) . $conn->error);
    }

    $conn->close();
}
?>