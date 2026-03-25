<?php
include 'auth.php';
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_local = mysqli_real_escape_string($conn, $_POST['nome_local']);
    $tipo_local = mysqli_real_escape_string($conn, $_POST['tipo_local']);
    $id_parent_local = !empty($_POST['id_parent_local']) ? intval($_POST['id_parent_local']) : 'NULL';

    $sql = "INSERT INTO locais (nome_local, tipo_local, id_parent_local) VALUES ('$nome_local', '$tipo_local', $id_parent_local)";
    if ($conn->query($sql) === TRUE) {
        header("Location: locais.php?msg=success");
    } else {
        header("Location: locais.php?msg=error");
    }
}
$conn->close();
?>
