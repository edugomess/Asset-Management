<?php
/**
 * PROCESSAMENTO DA KB: processar_kb.php
 * Lógica para criar, editar e excluir artigos da Base de Conhecimento.
 */
include_once 'auth.php';
include_once 'conexao.php';

if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    header("Location: index.php");
    exit();
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'create') {
    $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
    $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
    $palavras_chave = mysqli_real_escape_string($conn, $_POST['palavras_chave']);
    $conteudo = mysqli_real_escape_string($conn, $_POST['conteudo']);
    $autor_id = $_SESSION['id_usuarios'];

    $sql = "INSERT INTO base_conhecimento (titulo, categoria, palavras_chave, conteudo, autor_id) 
            VALUES ('$titulo', '$categoria', '$palavras_chave', '$conteudo', '$autor_id')";
    
    if ($conn->query($sql)) {
        header("Location: gerenciar_kb.php?msg=created");
    } else {
        echo "Erro: " . $conn->error;
    }
}

elseif ($action === 'update') {
    $id = intval($_POST['id']);
    $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
    $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
    $palavras_chave = mysqli_real_escape_string($conn, $_POST['palavras_chave']);
    $conteudo = mysqli_real_escape_string($conn, $_POST['conteudo']);

    $sql = "UPDATE base_conhecimento SET 
            titulo = '$titulo', 
            categoria = '$categoria', 
            palavras_chave = '$palavras_chave', 
            conteudo = '$conteudo',
            data_atualizacao = CURRENT_TIMESTAMP
            WHERE id = $id";
    
    if ($conn->query($sql)) {
        header("Location: gerenciar_kb.php?msg=updated");
    } else {
        echo "Erro: " . $conn->error;
    }
}

elseif ($action === 'delete') {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM base_conhecimento WHERE id = $id";
    
    if ($conn->query($sql)) {
        header("Location: gerenciar_kb.php?msg=deleted");
    } else {
        echo "Erro: " . $conn->error;
    }
}

else {
    header("Location: gerenciar_kb.php");
}
?>
