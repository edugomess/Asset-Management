<?php
// Conexão com o banco de dados
include 'conexao.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id_asset'])) {
        $id_asset = $_POST['id_asset'];
    } else {
        echo "id_asset não está definido.";
        exit;
    }

    // Busca dados atuais para histórico (antes do update)
    $sql_old = "SELECT * FROM ativos WHERE id_asset = '$id_asset'";
    $result_old = mysqli_query($conn, $sql_old);
    $old_data = mysqli_fetch_assoc($result_old);

    // Captura e sanitização dos dados básicos
    $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
    $fabricante = mysqli_real_escape_string($conn, $_POST['fabricante']);
    $modelo = mysqli_real_escape_string($conn, $_POST['modelo']);
    $hostName = mysqli_real_escape_string($conn, $_POST['hostName']);
    $valor = mysqli_real_escape_string($conn, $_POST['valor']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $macAdress = mysqli_real_escape_string($conn, $_POST['macAdress']);
    $centroDeCusto = mysqli_real_escape_string($conn, $_POST['centroDeCusto']);
    $setor = mysqli_real_escape_string($conn, $_POST['setor']);
    $fornecedor = mysqli_real_escape_string($conn, $_POST['fornecedor']);
    $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
    $dataAtivacao = mysqli_real_escape_string($conn, $_POST['dataAtivacao']);

    // Novos campos (Hardware e Nota Fiscal)
    $memoria = isset($_POST['memoria']) ? mysqli_real_escape_string($conn, $_POST['memoria']) : null;
    $processador = isset($_POST['processador']) ? mysqli_real_escape_string($conn, $_POST['processador']) : null;
    $armazenamento = isset($_POST['armazenamento']) ? mysqli_real_escape_string($conn, $_POST['armazenamento']) : null;
    $tipo_armazenamento = isset($_POST['tipo_armazenamento']) ? mysqli_real_escape_string($conn, $_POST['tipo_armazenamento']) : null;
    $numero_nota_fiscal = isset($_POST['numero_nota_fiscal']) ? mysqli_real_escape_string($conn, $_POST['numero_nota_fiscal']) : null;

    // Validação básica de Nota Fiscal
    if (empty($numero_nota_fiscal) || strlen($numero_nota_fiscal) < 3) {
        echo "<script>alert('Erro: O número da Nota Fiscal é obrigatório e deve ter pelo menos 3 caracteres.'); window.history.back();</script>";
        exit();
    }

    // Verificar se foi enviada uma nova imagem
    $imagemSql = "";
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'assets/img/ativos/';
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
        }
    }

    // Verificar se foi enviada uma nova nota fiscal
    $notaSql = "";
    if (isset($_FILES['anexo_nota_fiscal']) && $_FILES['anexo_nota_fiscal']['error'] === UPLOAD_ERR_OK) {
        $uploadDirNF = 'assets/img/notas/';
        if (!file_exists($uploadDirNF)) {
            mkdir($uploadDirNF, 0777, true);
        }
        $extensionNF = strtolower(pathinfo($_FILES['anexo_nota_fiscal']['name'], PATHINFO_EXTENSION));
        $newFileNameNF = 'nf_' . $id_asset . '_' . time() . '.' . $extensionNF;
        if (move_uploaded_file($_FILES['anexo_nota_fiscal']['tmp_name'], $uploadDirNF . $newFileNameNF)) {
            $notaPathForDb = '/' . $uploadDirNF . $newFileNameNF;
            $notaSql = ", anexo_nota_fiscal='$notaPathForDb'";
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
        setor='$setor',
        fornecedor='$fornecedor',
        descricao='$descricao',
        dataAtivacao='$dataAtivacao',
        memoria=" . ($memoria ? "'$memoria'" : "NULL") . ",
        processador=" . ($processador ? "'$processador'" : "NULL") . ",
        armazenamento=" . ($armazenamento ? "'$armazenamento'" : "NULL") . ",
        tipo_armazenamento=" . ($tipo_armazenamento ? "'$tipo_armazenamento'" : "NULL") . ",
        numero_nota_fiscal=" . ($numero_nota_fiscal ? "'$numero_nota_fiscal'" : "NULL") . "
        $imagemSql
        $notaSql
    WHERE id_asset = '$id_asset'";

    $update = mysqli_query($conn, $query);

    if ($update) {
        $usuario_id = isset($_SESSION['id_usuarios']) ? $_SESSION['id_usuarios'] : 'NULL';
        $acao = 'Edição';

        // Identificar alterações para o histórico
        $changes = [];
        if ($old_data['categoria'] != $categoria) $changes[] = "Categoria: {$old_data['categoria']} -> $categoria";
        if ($old_data['fabricante'] != $fabricante) $changes[] = "Fabricante: {$old_data['fabricante']} -> $fabricante";
        if ($old_data['modelo'] != $modelo) $changes[] = "Modelo: {$old_data['modelo']} -> $modelo";
        if ($old_data['hostName'] != $hostName) $changes[] = "HostName: {$old_data['hostName']} -> $hostName";
        if ($old_data['valor'] != $valor) $changes[] = "Valor: {$old_data['valor']} -> $valor";
        if ($old_data['status'] != $status) $changes[] = "Status: {$old_data['status']} -> $status";
        if ($old_data['macAdress'] != $macAdress) $changes[] = "MacAdress: {$old_data['macAdress']} -> $macAdress";
        if ($old_data['centroDeCusto'] != $centroDeCusto) $changes[] = "Centro de Custo: {$old_data['centroDeCusto']} -> $centroDeCusto";
        if (($old_data['setor'] ?? '') != $setor) $changes[] = "Setor: " . ($old_data['setor'] ?? 'N/A') . " -> $setor";
        if (($old_data['fornecedor'] ?? '') != $fornecedor) $changes[] = "Fornecedor: " . ($old_data['fornecedor'] ?? 'N/A') . " -> $fornecedor";
        if ($old_data['descricao'] != $descricao) $changes[] = "Descrição alterada";
        
        // Tracking de alterações em hardware
        if (($old_data['memoria'] ?? '') != $memoria) $changes[] = "Memória RAM alterada";
        if (($old_data['processador'] ?? '') != $processador) $changes[] = "Processador alterado";
        if (($old_data['armazenamento'] ?? '') != $armazenamento) $changes[] = "Armazenamento alterado";

        if (!empty($imagemSql)) $changes[] = "Imagem atualizada";
        if (!empty($notaSql)) $changes[] = "Anexo NF atualizado";

        $detalhes = empty($changes) ? "Nenhuma alteração registrada." : implode("; ", $changes);
        $detalhes = mysqli_real_escape_string($conn, $detalhes);

        $sql_historico = "INSERT INTO historico_ativos (ativo_id, usuario_id, acao, detalhes) VALUES ('$id_asset', $usuario_id, '$acao', '$detalhes')";
        mysqli_query($conn, $sql_historico);

        echo "<script>alert('" . __('Atualizado com sucesso!') . "'); window.location.href = 'perfil_ativo.php?id=$id_asset';</script>";
        exit();
    } else {
        echo "<script>alert('" . __('Erro ao atualizar: ') . "' + " . json_encode(mysqli_error($conn)) . "); window.history.back();</script>";
    }
}
?>
