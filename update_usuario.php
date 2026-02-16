<?php
include 'auth.php';
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

    // Upload da foto de perfil
    $foto_perfil_update = "";
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        $diretorio = "assets/img/avatars/";
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0777, true);
        }
        $extensao = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $nome_arquivo = uniqid() . "." . $extensao;
        $caminho_arquivo = $diretorio . $nome_arquivo;

        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $caminho_arquivo)) {
            $foto_perfil_path = "/" . $caminho_arquivo;
            $foto_perfil_update = ", foto_perfil='$foto_perfil_path'";

            // Atualiza a sessão se o usuário estiver editando o próprio perfil
            // session_start(); -- Removido pois já foi iniciado no auth.php
            if (isset($_SESSION['id_usuarios']) && $_SESSION['id_usuarios'] == $id_usuarios) {
                $_SESSION['foto_perfil'] = $foto_perfil_path;
            }
        }
    }

    // Verificar se foi fornecida uma nova senha
    if (!empty($senha)) {
        // Aplicar hashing na senha antes de atualizar (SHA1 para manter consistência)
        $senha_hashed = sha1($senha);

        // Atualizar no banco de dados incluindo a senha e foto
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
                             $foto_perfil_update
                         WHERE id_usuarios = '$id_usuarios'";
    }
    else {
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
                             $foto_perfil_update
                         WHERE id_usuarios = '$id_usuarios'";
    }

    if (mysqli_query($conn, $query_update)) {
        echo "<script>
                alert('Usuário atualizado com sucesso!');
                window.location.href = 'usuarios.php';
              </script>";
    }
    else {
        echo 'Erro ao atualizar dados: ' . mysqli_error($conn);
    }
}
?>
