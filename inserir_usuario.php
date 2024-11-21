<?php
include 'conexao.php';

$nome = $_POST['nome'];
$sobrenome = $_POST['sobrenome'];
$usuarioAD = $_POST['usuarioAD'];
$funcao = $_POST['funcao'];
$dataNascimento = $_POST['dataNascimento'];
$email = $_POST['email'];
$centroDeCusto = $_POST['centroDeCusto'];
$matricula = $_POST['matricula'];
$telefone = $_POST['telefone'];
$tipoContrato = $_POST['tipoContrato'];
$senha = $_POST['senha'];
$confirmarSenha = $_POST['confirmarSenha'];
$nivelUsuario = $_POST['nivelUsuario'];
$unidade = $_POST['unidade'];
$status = $_POST['status'];

// Verifica se as senhas coincidem
if ($senha !== $confirmarSenha) {
    echo "<script>alert('As senhas não coincidem.');</script>";
    exit();
}

// Aplica SHA-1 na senha
$senhaHash = sha1($senha);

// Prepara a consulta
$sql = "INSERT INTO usuarios (nome, sobrenome, usuarioAD, funcao, dataNascimento, email, centroDeCusto, matricula, telefone, senha, nivelUsuario, unidade, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssssss", $nome, $sobrenome, $usuarioAD, $funcao, $dataNascimento, $email, $centroDeCusto, $matricula, $telefone, $senhaHash, $nivelUsuario, $unidade, $status);

if ($stmt->execute()) {
    echo "<script>
            alert('Usuário cadastrado com sucesso!');
            window.location.href = 'usuarios.php';
          </script>";
    exit();
} else {
    echo "Erro ao inserir dados: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
