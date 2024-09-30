<?php
// Conexão com o banco de dados
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id_asset'])) {
        $id_asset = $_POST['id_asset'];
    } else {
        echo "id_asset não está definido.";
        exit;
    }

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

    // Verificar se foi enviada uma nova imagem
    if (!empty($_FILES['imagem']['name'])) {
        // Processar a imagem (salvar no servidor, etc.)
    }

    // Atualizar no banco de dados
    $query = "UPDATE ativos 
    SET categoria='$categoria', 
        fabricante='$fabricante', 
        modelo='$modelo', 
        tag='$tag', 
        hostName='$hostName', 
        ip='$ip', 
        macAdress='$macAdress', 
        status='$status', 
        dataAtivacao='$dataAtivacao', 
        centroDeCusto='$centroDeCusto', 
        descricao='$descricao' 
    WHERE id_asset = '$id_asset'";

    if (mysqli_query($conn, $query)) {
        echo "Equipamento atualizado com sucesso!";
    } else {
        echo "Erro ao atualizar: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
