<?php
// Conexão com o banco de dados
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id_usuarios'])) {
        $id_usuarios = $_POST['id_usuarios'];
    } else {
        echo "id_asset não está definido.";
        exit;
    }

    $nome = $_POST['nome'];
    $sobrenome = $_POST['sobrenome'];
    $usuarioAD = $_POST['usuarioAD'];
    $funcao = $_POST['funcao'];
    $dataNascimento = $_POST['dataNascimento'];
    $email = $_POST['email'];
    $centroDeCusto = $_POST['centroDeCusto'];
    $matricula = $_POST['matricula'];
    $telefone = $_POST['telefone'];
    $senha = $_POST['senha'];
    $confirmarSenha = $_POST['confirmaSenha'];
    $nivelUsuario = $_POST['nivelUsuario'];
    $unidade = $_POST['unidade'];
    $status = $_POST['status'];

    // Verificar se foi enviada uma nova imagem
    if (!empty($_FILES['imagem']['name'])) {
        // Processar a imagem (salvar no servidor, etc.)
    }

    // Atualizar no banco de dados
    $query = "UPDATE usuarios 
    SET  
         
    nome ='$nome',
    sobrenome = '$sobrenome',
    usuarioAD = '$usuarioAD',
    funcao = '$funcao',
    dataNascimento = '$dataNascimento',
    email = '$email',
    centroDeCusto = '$centroDeCusto',
    matricula = '$matricula',
    telefone = '$telefone',
    senha = '$senha',
    confirmarSenha = '$confirmaSenha',
    nivelUsuario = '$nivelUsuario',
    unidade = '$unidade',
    status = '$status',
    WHERE id_usuarios = '$id_usuarios'";

    $update = mysqli_query($conn, $query); // Corrigido aqui de $sql para $query

    if ($update) {
        echo "<script>
                alert('Usuário atualizado com sucesso!');
                window.location.href = 'usuarios.php';
              </script>";
        exit();
    } else {
        echo "Erro ao atualizar dados: " . mysqli_error($conn);
    }
}
?>
