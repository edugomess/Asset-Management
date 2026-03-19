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
    $status = $_POST['status']; // Status update
    $nivelUsuario = $_POST['nivelUsuario']; // Nível de usuário enviado no formulário
    $tipoContrato = $_POST['tipoContrato']; // Novo tipo de contrato

    $cpf = isset($_POST['cpf']) ? preg_replace('/[^\d]/', '', $_POST['cpf']) : '';

    // Mapeia o nível de usuário numérico para o ENUM do banco de dados
    $nivelMapeamento = [
        '1' => 'Admin',
        '2' => 'Suporte',
        '3' => 'Usuário'
    ];
    $nivelUsuarioEnum = isset($nivelMapeamento[$nivelUsuario]) ? $nivelMapeamento[$nivelUsuario] : 'Usuário';

    // Verificar se o usuarioAD já existe no banco de dados, exceto para o próprio usuário
    $query_check = "SELECT id_usuarios FROM usuarios WHERE usuarioAD = '$usuarioAD' AND id_usuarios != '$id_usuarios'";
    $result_check = mysqli_query($conn, $query_check);

    if (mysqli_num_rows($result_check) > 0) {
        echo "<script>
                alert('<?php echo __('Erro: O nome de usuário AD já está em uso. Escolha outro.'); ?>');
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

    // --- TRAVA DE SEGURANÇA PARA USUÁRIO COMUM ---
    if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
        // Se não for Admin/Suporte, só permitimos atualizar a FOTO.
        // Vamos forçar os outros campos a serem os valores atuais do banco ou simplesmente não incluí-los no UPDATE.
        // Para garantir, vamos buscar os dados atuais se necessário, ou apenas montar a query com a FOTO.

        if ($foto_perfil_update != "") {
            // Remove a vírgula inicial do $foto_perfil_update para montar a query
            $foto_sql = str_replace(", foto_perfil", "foto_perfil", $foto_perfil_update);
            $query_update = "UPDATE usuarios SET $foto_sql WHERE id_usuarios = '$id_usuarios'";
        } else {
            // Se não enviou foto, não faz nada (ou apenas confirma sucesso para não dar erro no JS)
            echo "<script>
                    alert('<?php echo __('Nenhuma alteração realizada (somente a foto pode ser alterada).'); ?>');
                    window.location.href = 'profile.php';
                  </script>";
            exit();
        }
    } else {
        // --- LÓGICA ORIGINAL PARA ADMIN/SUPORTE ---
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
                                 status='$status',
                                 cpf='$cpf',
                                 nivelUsuario='$nivelUsuarioEnum',
                                 tipoContrato='$tipoContrato',
                                 senha='$senha_hashed'
                                 $foto_perfil_update
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
                                 unidade='$unidade',
                                 cpf='$cpf',
                                 nivelUsuario='$nivelUsuarioEnum',
                                 tipoContrato='$tipoContrato',
                                 status='$status'
                                 $foto_perfil_update
                             WHERE id_usuarios = '$id_usuarios'";
        }
    }

    if (mysqli_query($conn, $query_update)) {
        $redirect = ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') ? 'profile.php' : 'usuarios.php';
        echo "<script>
                alert('<?php echo __('Atualizado com sucesso!'); ?>');
                window.location.href = '$redirect';
              </script>";
    } else {
        echo 'Erro ao atualizar dados: ' . mysqli_error($conn);
    }
}
?>