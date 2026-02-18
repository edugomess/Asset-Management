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

    $sql = "UPDATE centro_de_custo SET 
            nomeSetor = '$nomeSetor',
            codigo = '$codigo',
            ramal = '$ramal',
            unidade = '$unidade',
            emailGestor = '$emailGestor',
            gestor = '$gestor',
            status = '$status'
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
