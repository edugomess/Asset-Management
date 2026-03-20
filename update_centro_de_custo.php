<?php
/**
 * PROCESSAMENTO DE EDIÇÃO DE CENTRO DE CUSTO: update_centro_de_custo.php
 * Atualiza os dados do setor.
 */
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_centro_de_custo = intval($_POST['id_centro_de_custo']);
    $nomeSetor = mysqli_real_escape_string($conn, $_POST['nomeSetor']);
    $codigo = mysqli_real_escape_string($conn, $_POST['codigo']);
    $ramal = mysqli_real_escape_string($conn, $_POST['ramal']);
    $unidade = mysqli_real_escape_string($conn, $_POST['unidade']);
    $emailGestor = mysqli_real_escape_string($conn, $_POST['emailGestor']);
    $gestor = mysqli_real_escape_string($conn, $_POST['gestor']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $descricao = isset($_POST['descricao']) ? mysqli_real_escape_string($conn, $_POST['descricao']) : '';

    $sql = "UPDATE centro_de_custo SET 
            nomeSetor = '$nomeSetor',
            codigo = '$codigo',
            ramal = '$ramal',
            unidade = '$unidade',
            emailGestor = '$emailGestor',
            gestor = '$gestor',
            status = '$status',
            descricao = '$descricao'
            WHERE id_centro_de_custo = $id_centro_de_custo";

    if ($conn->query($sql) === TRUE) {
        // Redireciona de volta para a listagem com mensagem de sucesso
        header("Location: centro_de_custo.php?msg=" . urlencode(__('Atualizado com sucesso!')));
    } else {
        header("Location: centro_de_custo.php?msg=" . urlencode(__('Erro ao atualizar: ')) . $conn->error);
    }

    $conn->close();
}
?>