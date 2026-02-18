<?php
// Conexão com o banco de dados
include 'conexao.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id_asset'])) {
        $id_asset = $_POST['id_asset'];
    }
    else {
        echo "id_asset não está definido.";
        exit;
    }

    // Busca dados atuais para histórico (antes do update)
    $sql_old = "SELECT * FROM ativos WHERE id_asset = '$id_asset'";
    $result_old = mysqli_query($conn, $sql_old);
    $old_data = mysqli_fetch_assoc($result_old);
    $categoria = $_POST['categoria'];
    $fabricante = $_POST['fabricante'];
    $modelo = $_POST['modelo'];
    $hostName = $_POST['hostName'];
    $valor = $_POST['valor'];
    $status = $_POST['status'];
    $macAdress = $_POST['macAdress'];
    $centroDeCusto = $_POST['centroDeCusto'];
    $descricao = $_POST['descricao'];
    $dataAtivacao = $_POST['dataAtivacao'];

    // Verificar se foi enviada uma nova imagem
    $imagemSql = "";
    // Check if file is set
    if (isset($_FILES['imagem'])) {
        // Check for specific upload errors
        if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
            $uploadError = $_FILES['imagem']['error'];
            // Ignore error 4 (No file uploaded) as it is optional
            if ($uploadError != UPLOAD_ERR_NO_FILE) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'O arquivo excede o tamanho máximo permitido (upload_max_filesize).',
                    UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o tamanho máximo permitido no formulário.',
                    UPLOAD_ERR_PARTIAL => 'O upload do arquivo foi feito apenas parcialmente.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Faltando uma pasta temporária.',
                    UPLOAD_ERR_CANT_WRITE => 'Falha ao gravar arquivo em disco.',
                    UPLOAD_ERR_EXTENSION => 'Uma extensão do PHP interrompeu o upload do arquivo.',
                ];
                $msg = isset($errorMessages[$uploadError]) ? $errorMessages[$uploadError] : 'Erro desconhecido no upload.';
                echo "<script>alert('Erro no upload: $msg');</script>";
            }
        }
        else {
            // Processing successful upload
            $uploadDir = 'assets/img/ativos/';
            // Ensure directory exists - absolute path is safer sometimes but relative should work if CWD is htdocs
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileInfo = pathinfo($_FILES['imagem']['name']);
            $extension = strtolower($fileInfo['extension']);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($extension, $allowedExtensions)) {
                $newFileName = 'ativo_' . $id_asset . '_' . time() . '.' . $extension;
                $targetPath = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $targetPath)) {
                    $imagePathForDb = '/' . $targetPath;
                    $imagemSql = ", imagem='$imagePathForDb'";
                }
                else {
                    $lastError = error_get_last();
                    echo "<script>alert('Erro ao mover arquivo para: $targetPath. Detalhes: " . ($lastError['message'] ?? '') . "');</script>";
                }
            }
            else {
                echo "<script>alert('Formato de imagem inválido. Permitidos: jpg, jpeg, png, gif, webp');</script>";
            }
        }
    }

    // Atualizar no banco de dados
    $query = "UPDATE ativos 
    SET  
        categoria='$categoria',
        fabricante='$fabricante', 
        modelo='$modelo', 
        hostName='$hostName', 
        valor='$valor', 
        status='$status', 
        macAdress='$macAdress',
        centroDeCusto='$centroDeCusto', 
        descricao='$descricao',
        dataAtivacao='$dataAtivacao'
        $imagemSql
    WHERE id_asset = '$id_asset'";

    $update = mysqli_query($conn, $query); // Corrigido aqui de $sql para $query

    if ($update) {
        $usuario_id = isset($_SESSION['id_usuarios']) ? $_SESSION['id_usuarios'] : 'NULL';
        $acao = 'Edição';

        // Identificar alterações
        $changes = [];
        if ($old_data['categoria'] != $categoria)
            $changes[] = "Categoria: {$old_data['categoria']} -> $categoria";
        if ($old_data['fabricante'] != $fabricante)
            $changes[] = "Fabricante: {$old_data['fabricante']} -> $fabricante";
        if ($old_data['modelo'] != $modelo)
            $changes[] = "Modelo: {$old_data['modelo']} -> $modelo";
        if ($old_data['hostName'] != $hostName)
            $changes[] = "HostName: {$old_data['hostName']} -> $hostName";
        if ($old_data['valor'] != $valor)
            $changes[] = "Valor: {$old_data['valor']} -> $valor";
        if ($old_data['status'] != $status)
            $changes[] = "Status: {$old_data['status']} -> $status";
        if ($old_data['macAdress'] != $macAdress)
            $changes[] = "MacAdress: {$old_data['macAdress']} -> $macAdress";
        if ($old_data['centroDeCusto'] != $centroDeCusto)
            $changes[] = "Centro de Custo: {$old_data['centroDeCusto']} -> $centroDeCusto";
        if ($old_data['descricao'] != $descricao)
            $changes[] = "Descrição alterada";
        if ($old_data['dataAtivacao'] != $dataAtivacao)
            $changes[] = "Data Ativação: {$old_data['dataAtivacao']} -> $dataAtivacao";
        if (!empty($imagemSql))
            $changes[] = "Imagem atualizada";

        $detalhes = empty($changes) ? "Nenhuma alteração registrada." : implode("; ", $changes);

        $sql_historico = "INSERT INTO historico_ativos (ativo_id, usuario_id, acao, detalhes) VALUES ('$id_asset', $usuario_id, '$acao', '$detalhes')";
        mysqli_query($conn, $sql_historico);

        echo "<script>
                alert('Ativo atualizado com sucesso!');
                window.location.href = 'equipamentos.php';
              </script>";
        exit();
    }
    else {
        echo "Erro ao atualizar dados: " . mysqli_error($conn);
    }
}
?>
