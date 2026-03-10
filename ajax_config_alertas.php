<?php
include 'auth.php';
include 'conexao.php';

// Restrição de acesso: Apenas Administrador
if ($_SESSION['nivelUsuario'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field = $_POST['field'] ?? '';
    $value = isset($_POST['value']) ? (int) $_POST['value'] : 0;

    // Lista de campos permitidos para evitar SQL Injection
    $allowed_fields = ['chamados_ativo', 'manutencao_ativo', 'whatsapp_ativo', 'email_ativo'];

    if (in_array($field, $allowed_fields)) {
        $sql = "UPDATE configuracoes_alertas SET $field = $value WHERE id = 1";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Campo inválido']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
}
