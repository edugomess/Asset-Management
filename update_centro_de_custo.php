<?php
// Conexão com o banco de dados
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id_centro_de_custo'])) {
        $id_centro_de_custo = $_POST['id_centro_de_custo'];
    } else {
        echo 'id_centro_de_custo não está definido.';
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

    // Verificar se foi enviada uma nova imagem
    if (!empty($_FILES['imagem']['name'])) {
        // Processar a imagem (salvar no servidor, etc.)
    }

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
                alert('Fornecedor atualizado com sucesso!');
                window.location.href = 'centro_de_custo.php';
              </script>";
        exit();
    } else {
        echo "Erro ao atualizar dados: " . mysqli_error($conn);
    }
}
?>
