
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

$sql = "INSERT INTO usuarios (nome, sobrenome, usuarioAD, funcao, dataNascimento, email, centroDeCusto, matricula, telefone, senha, confirmarSenha, nivelUsuario, unidade, status)
    VALUES ('$nome', '$sobrenome', '$usuarioAD', '$funcao', '$dataNascimento', '$email', '$centroDeCusto', '$matricula', '$telefone', '$senha', '$confirmarSenha', '$nivelUsuario', '$unidade', '$status')";

$inserir = mysqli_query($conn, $sql);

if ($inserir) {
    echo "<script>
            alert('Usuário cadastrado com sucesso!');
            window.location.href = 'usuarios.php';
          </script>";
    exit();
} else {
    echo "Erro ao inserir dados: " . mysqli_error($conn);
}

?>