<?php
// Conexão com o banco de dados
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar se o ID foi recebido corretamente
    if (!isset($_POST['id_centro_de_custo']) || empty($_POST['id_centro_de_custo'])) {
        echo "ID do centro de custo não está definido ou está vazio.";
        exit;
    }

    $id_centro_de_custo = $_POST['id_centro_de_custo'];
    $nomeSetor = $_POST['nomeSetor'];
    $codigo = $_POST['codigo'];
    $ramal = $_POST['ramal'];
    $unidade = $_POST['unidade'];
    $emailGestor = $_POST['emailGestor'];
    $gestor = $_POST['gestor'];
    $status = $_POST['status'];

    // Atualizar no banco de dados
    $query = "UPDATE centro_de_custo 
              SET  
                  nomeSetor='$nomeSetor', 
                  codigo='$codigo', 
                  ramal='$ramal', 
                  unidade='$unidade', 
                  emailGestor='$emailGestor', 
                  gestor='$gestor', 
                  status='$status' 
              WHERE id_centro_de_custo = '$id_centro_de_custo'";

    $update = mysqli_query($conn, $query);

    if ($update) {
        echo "<script>
                alert('Centro de custo atualizado com sucesso!');
                window.location.href = 'centro_de_custo.php';
              </script>";
        exit();
    } else {
        echo "Erro ao atualizar dados: " . mysqli_error($conn);
    }
}
?>

