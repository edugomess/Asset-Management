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

// Mapeia o nível de usuário numérico para o ENUM do banco de dados
$nivelMapeamento = [
    '1' => 'Admin',
    '2' => 'Suporte',
    '3' => 'Usuário'
];
$nivelUsuarioEnum = isset($nivelMapeamento[$nivelUsuario]) ? $nivelMapeamento[$nivelUsuario] : 'Usuário';

// Prepara a consulta
$cpfRaw = isset($_POST['cpf']) ? $_POST['cpf'] : '';
$cpfClean = preg_replace('/[^0-9]/', '', $cpfRaw);

$sql = "INSERT INTO usuarios (nome, sobrenome, usuarioAD, funcao, dataNascimento, email, cpf, centro de custo, matricula, telefone, senha, nivelUsuario, unidade, status, foto_perfil)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Nota: Usei 'centroDeCusto' conforme o nome da coluna no DESCRIBE recebido anteriormente.
$sql = "INSERT INTO usuarios (nome, sobrenome, usuarioAD, funcao, dataNascimento, email, cpf, centroDeCusto, matricula, telefone, senha, nivelUsuario, unidade, status, foto_perfil)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssssssss", $nome, $sobrenome, $usuarioAD, $funcao, $dataNascimento, $email, $cpfClean, $centroDeCusto, $matricula, $telefone, $senhaHash, $nivelUsuarioEnum, $unidade, $status, $foto_perfil);

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