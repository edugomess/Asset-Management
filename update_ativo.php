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
    $hostName = $_POST['hostName'];
    $ip = $_POST['ip'];
    $status = $_POST['status'];
    $macAdress = $_POST['macAdress'];
    $centroDeCusto = $_POST['centroDeCusto'];
    $descricao = $_POST['descricao'];

    // Verificar se foi enviada uma nova imagem
    if (!empty($_FILES['imagem']['name'])) {
        // Processar a imagem (salvar no servidor, etc.)
    }

    // Atualizar no banco de dados
    $query = "UPDATE ativos 
    SET  
        categoria='$categoria',
        fabricante='$fabricante', 
        modelo='$modelo', 
        hostName='$hostName', 
        ip='$ip', 
        status='$status', 
        macAdress='$macAdress',
        centroDeCusto='$centroDeCusto', 
        descricao='$descricao' 
    WHERE id_asset = '$id_asset'";

    $update = mysqli_query($conn, $query); // Corrigido aqui de $sql para $query

    if ($update) {
        echo "<script>
                alert('Ativo atualizado com sucesso!');
                window.location.href = 'equipamentos.php';
              </script>";
        exit();
    } else {
        echo "Erro ao atualizar dados: " . mysqli_error($conn);
    }
}
?>
