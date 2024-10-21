<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuarios = $_POST['id_usuarios'];
    $nome = $_POST['nome'];
    $sobrenome = $_POST['sobrenome'];
    $usuarioAD = $_POST['usuarioAD'];
    $funcao = $_POST['funcao'];
    $dataNascimento = $_POST['dataNascimento'];
    $email = $_POST['email'];
    $centroDeCusto = $_POST['centroDeCusto'];
    $matricula = $_POST['matricula'];
    $telefone = $_POST['telefone'];
    $senha = $_POST['senha']; // Nova senha enviada no formulário
    $unidade = $_POST['unidade']; // Nova unidade enviada no formulário
    
    // Verificar se o usuarioAD já existe no banco de dados, exceto para o próprio usuário
    $query_check = "SELECT id_usuarios FROM usuarios WHERE usuarioAD = '$usuarioAD' AND id_usuarios != '$id_usuarios'";
    $result_check = mysqli_query($conn, $query_check);

    if (mysqli_num_rows($result_check) > 0) {
        echo "<script>
                alert('Erro: O nome de usuário AD já está em uso. Escolha outro.');
                window.history.back();
              </script>";
        exit();
    }

    // Verificar se foi fornecida uma nova senha
    if (!empty($senha)) {
        // Aplicar hashing na senha antes de atualizar
        $senha_hashed = password_hash($senha, PASSWORD_DEFAULT);

        // Atualizar no banco de dados incluindo a senha
        $query_update = "UPDATE usuarios 
                         SET nome='$nome', 
                             sobrenome='$sobrenome', 
                             usuarioAD='$usuarioAD', 
                             funcao='$funcao', 
                             dataNascimento='$dataNascimento', 
                             email='$email', 
                             centroDeCusto='$centroDeCusto', 
                             matricula='$matricula', 
                             telefone='$telefone',
                             unidade='$unidade', 
                             senha='$senha_hashed' 
                         WHERE id_usuarios = '$id_usuarios'";
    } else {
        // Se a senha não foi alterada, não atualize o campo de senha
        $query_update = "UPDATE usuarios 
                         SET nome='$nome', 
                             sobrenome='$sobrenome', 
                             usuarioAD='$usuarioAD', 
                             funcao='$funcao', 
                             dataNascimento='$dataNascimento', 
                             email='$email', 
                             centroDeCusto='$centroDeCusto', 
                             matricula='$matricula', 
                             telefone='$telefone',
                             unidade='$unidade'
                         WHERE id_usuarios = '$id_usuarios'";
    }

    if (mysqli_query($conn, $query_update)) {
        echo "<script>
                alert('Usuário atualizado com sucesso!');
                window.location.href = 'usuarios.php';
              </script>";
    } else {
        echo 'Erro ao atualizar dados: ' . mysqli_error($conn);
    }
}
?>
