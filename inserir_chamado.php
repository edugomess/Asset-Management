<?php
include 'auth.php';
include_once 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
    $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
    $usuario_id = intval($_POST['usuario_id']);
    $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
    $prioridade = mysqli_real_escape_string($conn, $_POST['prioridade']);

    // Processar upload de anexo (se houver)
    $anexo_path = null;
    if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/chamados/';

        // Criar pasta se não existir
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file = $_FILES['anexo'];
        $file_size = $file['size'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validar extensão
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'pdf', 'doc', 'docx'];
        if (!in_array($file_ext, $allowed_ext)) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Tipo de arquivo não permitido. Use: ' . implode(', ', $allowed_ext)]);
                exit();
            }
            die("Tipo de arquivo não permitido.");
        }

        // Validar tamanho (5MB max)
        if ($file_size > 5 * 1024 * 1024) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Arquivo muito grande. Tamanho máximo: 5MB.']);
                exit();
            }
            die("Arquivo muito grande. Máximo 5MB.");
        }

        // Gerar nome único para o arquivo
        $unique_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
        $dest_path = $upload_dir . $unique_name;

        if (move_uploaded_file($file_tmp, $dest_path)) {
            $anexo_path = 'uploads/chamados/' . $unique_name;
        }
    }

    // Montar SQL com ou sem anexo
    if ($anexo_path) {
        $anexo_escaped = mysqli_real_escape_string($conn, $anexo_path);
        $sql = "INSERT INTO chamados (titulo, categoria, prioridade, descricao, usuario_id, anexo) 
                VALUES ('$titulo', '$categoria', '$prioridade', '$descricao', '$usuario_id', '$anexo_escaped')";
    }
    else {
        $sql = "INSERT INTO chamados (titulo, categoria, prioridade, descricao, usuario_id) 
                VALUES ('$titulo', '$categoria', '$prioridade', '$descricao', '$usuario_id')";
    }

    if ($conn->query($sql) === TRUE) {
        $last_id = $conn->insert_id;

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'id' => $last_id, 'message' => 'Chamado criado com sucesso!']);
            exit();
        }
        else {
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
