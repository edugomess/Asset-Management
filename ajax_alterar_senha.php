<?php
/**
 * AJAX HANDLER: ajax_alterar_senha.php
 * Processa a alteração de senha de um usuário.
 */
include_once 'auth.php';
include_once 'conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    exit();
}

$id_usuario = isset($_POST['id_usuario_senha']) ? intval($_POST['id_usuario_senha']) : 0;
$senha_atual_informada = isset($_POST['senha_atual']) ? $_POST['senha_atual'] : '';
$nova_senha = isset($_POST['nova_senha']) ? $_POST['nova_senha'] : '';
$confirmar_senha = isset($_POST['confirmar_senha']) ? $_POST['confirmar_senha'] : '';

if ($id_usuario <= 0 || empty($senha_atual_informada) || empty($nova_senha)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, preencha todos os campos.']);
    exit();
}

if ($nova_senha !== $confirmar_senha) {
    echo json_encode(['success' => false, 'message' => 'As novas senhas não coincidem.']);
    exit();
}

if (strlen($nova_senha) < 4) {
    echo json_encode(['success' => false, 'message' => 'A nova senha deve ter pelo menos 4 caracteres.']);
    exit();
}

// 1. Verificar se o usuário existe e obter a senha atual do banco
$stmt = $conn->prepare("SELECT senha FROM usuarios WHERE id_usuarios = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado.']);
    exit();
}

$user = $result->fetch_assoc();
$senha_db = $user['senha'];

// 2. Validar a senha atual (usando sha1 para compatibilidade com login.php)
if (sha1($senha_atual_informada) !== $senha_db) {
    echo json_encode(['success' => false, 'message' => 'A senha atual está incorreta.']);
    exit();
}

// 3. Atualizar para a nova senha
$nova_senha_hash = sha1($nova_senha);
$update_stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id_usuarios = ?");
$update_stmt->bind_param("si", $nova_senha_hash, $id_usuario);

if ($update_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar a senha no banco de dados.']);
}

$stmt->close();
$update_stmt->close();
$conn->close();
?>
