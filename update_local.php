<?php
/**
 * PROCESSAMENTO DE EDIÇÃO DE LOCAL: update_local.php
 * Recebe os dados do formulário de editar_local.php e atualiza o banco de dados.
 */
include_once 'auth.php';
include_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_local = isset($_POST['id_local']) ? intval($_POST['id_local']) : 0;
    $nome_local = mysqli_real_escape_string($conn, $_POST['nome_local']);
    $tipo_local = mysqli_real_escape_string($conn, $_POST['tipo_local']);
    $id_parent_local = !empty($_POST['id_parent_local']) ? intval($_POST['id_parent_local']) : 'NULL';

    if ($id_local > 0 && !empty($nome_local)) {
        // SQL dinâmico para lidar com NULL no id_parent_local
        $parentSql = ($id_parent_local === 'NULL') ? "NULL" : $id_parent_local;
        
        $sql = "UPDATE locais SET 
                nome_local = '$nome_local', 
                tipo_local = '$tipo_local', 
                id_parent_local = $parentSql 
                WHERE id_local = $id_local";

        if ($conn->query($sql) === TRUE) {
            header("Location: locais.php?msg=success_edit");
        } else {
            echo "Erro ao atualizar: " . $conn->error;
        }
    } else {
        header("Location: locais.php?msg=error_data");
    }
} else {
    header("Location: locais.php");
}

$conn->close();
?>
