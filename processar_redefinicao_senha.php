<?php
/**
 * PROCESSADOR DE REDEFINIÇÃO DE SENHA: processar_redefinicao_senha.php
 * Valida o token e atualiza a senha do usuário.
 */
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if ($nova_senha !== $confirmar_senha) {
        echo "<script>alert('" . __('As senhas não coincidem.') . "'); window.history.back();</script>";
        exit();
    }

    if (strlen($nova_senha) < 4) {
        echo "<script>alert('" . __('A senha deve ter pelo menos 4 caracteres.') . "'); window.history.back();</script>";
        exit();
    }

    // 1. Validar token e expiração
    $stmt = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE reset_token = ? AND reset_token_expira > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id_usuarios'];
        
        // 2. Atualizar Senha (SHA1 conforme padrão do sistema)
        $senha_hash = sha1($nova_senha);
        
        // 3. Persistir nova senha e LIMPAR o token
        $updateStmt = $conn->prepare("UPDATE usuarios SET senha = ?, reset_token = NULL, reset_token_expira = NULL WHERE id_usuarios = ?");
        $updateStmt->bind_param("si", $senha_hash, $user_id);
        
        if ($updateStmt->execute()) {
             echo "<script>alert('" . __('Senha redefinida com sucesso! Você já pode fazer login.') . "'); window.location.href = 'login.php';</script>";
        } else {
             echo "<script>alert('" . __('Erro ao atualizar a senha. Tente novamente mais tarde.') . "'); window.location.href = 'login.php';</script>";
        }
    } else {
        echo "<script>alert('" . __('O link de recuperação é inválido ou expirou.') . "'); window.location.href = 'esqueceu_senha.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
