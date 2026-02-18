<?php
include 'auth.php';
include_once 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
    $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
    $usuario_id = intval($_POST['usuario_id']); // Ensure it's an integer
    $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
    $prioridade = mysqli_real_escape_string($conn, $_POST['prioridade']);

    // Status default is 'Aberto' in DB

    $sql = "INSERT INTO chamados (titulo, categoria, prioridade, descricao, usuario_id) 
            VALUES ('$titulo', '$categoria', '$prioridade', '$descricao', '$usuario_id')";

    if ($conn->query($sql) === TRUE) {
        $last_id = $conn->insert_id;

        // Check if it's an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'id' => $last_id, 'message' => 'Chamado criado com sucesso!']);
            exit();
        }
        else {
            // Normal redirect
            header("Location: chamados.php");
            exit();
        }
    }
    else {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Erro ao criar chamado: ' . $conn->error]);
            exit();
        }
        else {
            echo "Erro: " . $sql . "<br>" . $conn->error;
        }
    }
}
$conn->close();
