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

// Upload da foto de perfil
$foto_perfil = null;
if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
    $diretorio = "assets/img/avatars/";
    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0777, true);
    }
    $extensao = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
    $nome_arquivo = uniqid() . "." . $extensao;
    $caminho_arquivo = $diretorio . $nome_arquivo;

    if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $caminho_arquivo)) {
        $foto_perfil = "/" . $caminho_arquivo; // Caminho relativo para armazenar no banco
    }
}

// Aplica SHA-1 na senha
$senhaHash = sha1($senha);

// Prepara a consulta
$sql = "INSERT INTO usuarios (nome, sobrenome, usuarioAD, funcao, dataNascimento, email, centroDeCusto, matricula, telefone, senha, nivelUsuario, unidade, status, foto_perfil)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssssssss", $nome, $sobrenome, $usuarioAD, $funcao, $dataNascimento, $email, $centroDeCusto, $matricula, $telefone, $senhaHash, $nivelUsuario, $unidade, $status, $foto_perfil);

if ($stmt->execute()) {
    echo "<script>
            alert('Usuário cadastrado com sucesso!');
            window.location.href = 'usuarios.php';
          </script>";
    exit();
}
else {
    echo "Erro ao inserir dados: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
