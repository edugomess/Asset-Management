<?php
/**
 * PROCESSADOR DE ATIVOS: inserir_equipamento.php
 * Executa a inserção de novos equipamentos no inventário, tratando o upload da imagem do ativo.
 */
include 'conexao.php';
session_start();

$categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
$fabricante = mysqli_real_escape_string($conn, $_POST['fabricante']);
$modelo = mysqli_real_escape_string($conn, $_POST['modelo']);
$tag = mysqli_real_escape_string($conn, $_POST['tag']);
$hostName = mysqli_real_escape_string($conn, $_POST['hostName']);
$valor = mysqli_real_escape_string($conn, $_POST['valor']);
$macAdress = mysqli_real_escape_string($conn, $_POST['macAdress']);
$numero_serie = mysqli_real_escape_string($conn, $_POST['numero_serie']);
$setor = mysqli_real_escape_string($conn, $_POST['setor']);
$tier = isset($_POST['tier']) ? mysqli_real_escape_string($conn, $_POST['tier']) : null;
$centroDeCusto = isset($_POST['centroDeCusto']) ? mysqli_real_escape_string($conn, $_POST['centroDeCusto']) : 'Nenhum';
$fornecedor = isset($_POST['fornecedor']) ? mysqli_real_escape_string($conn, $_POST['fornecedor']) : 'Nenhum';
$descricao = isset($_POST['descricao']) ? mysqli_real_escape_string($conn, $_POST['descricao']) : '';
$dataAtivacao = isset($_POST['dataAtivacao']) ? mysqli_real_escape_string($conn, $_POST['dataAtivacao']) : date('Y-m-d');
$assigned_type = isset($_POST['assigned_type']) ? mysqli_real_escape_string($conn, $_POST['assigned_type']) : 'Usuario';
$assigned_to = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : 'NULL';
$id_local = !empty($_POST['id_local']) ? intval($_POST['id_local']) : 'NULL';
$parent_asset_id = !empty($_POST['parent_asset_id']) ? intval($_POST['parent_asset_id']) : 'NULL';

// Auto-derive status based on assignments
if ($assigned_to !== 'NULL' || $id_local !== 'NULL') {
    $status = 'Em uso';
} else {
    $status = 'Disponível';
}

// Novos campos
$memoria = isset($_POST['memoria']) ? mysqli_real_escape_string($conn, $_POST['memoria']) : null;
$processador = isset($_POST['processador']) ? mysqli_real_escape_string($conn, $_POST['processador']) : null;
$armazenamento = isset($_POST['armazenamento']) ? mysqli_real_escape_string($conn, $_POST['armazenamento']) : null;
$tipo_armazenamento = isset($_POST['tipo_armazenamento']) ? mysqli_real_escape_string($conn, $_POST['tipo_armazenamento']) : null;
$numero_nota_fiscal = isset($_POST['numero_nota_fiscal']) ? mysqli_real_escape_string($conn, $_POST['numero_nota_fiscal']) : null;
$gpu = isset($_POST['gpu']) ? mysqli_real_escape_string($conn, $_POST['gpu']) : null;
$polegadas = isset($_POST['polegadas']) ? mysqli_real_escape_string($conn, $_POST['polegadas']) : null;
$is_scanner = isset($_POST['is_scanner']) ? mysqli_real_escape_string($conn, $_POST['is_scanner']) : null;
$imei = isset($_POST['imei']) ? mysqli_real_escape_string($conn, $_POST['imei']) : null;
$sim_card = isset($_POST['sim_card']) ? mysqli_real_escape_string($conn, $_POST['sim_card']) : null;

// Validação rigorosa de Chave de Acesso NF-e
if (empty($numero_nota_fiscal) || strlen($numero_nota_fiscal) !== 44 || !ctype_digit($numero_nota_fiscal)) {
    if (isset($_POST['ajax'])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro: A Chave de Acesso é obrigatória e deve ter exatamente 44 dígitos numéricos.']);
        exit();
    }
    echo "<script>alert('Erro: A Chave de Acesso é obrigatória e deve ter exatamente 44 dígitos numéricos.'); window.history.back();</script>";
    exit();
}

$imagem = '/assets/img/no-image.png';
$anexo_nota_fiscal = null;

// Upload da Imagem do Ativo
if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'assets/img/ativos/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $extension = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
    $newFileName = 'ativo_' . time() . '_' . rand(100, 999) . '.' . $extension;
    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $uploadDir . $newFileName)) {
        $imagem = '/' . $uploadDir . $newFileName;
    }
}

// Upload do Anexo da Nota Fiscal
if (isset($_FILES['anexo_nota_fiscal']) && $_FILES['anexo_nota_fiscal']['error'] === UPLOAD_ERR_OK) {
    $uploadDirNF = 'assets/img/notas/';
    if (!file_exists($uploadDirNF)) {
        mkdir($uploadDirNF, 0777, true);
    }
    $extensionNF = strtolower(pathinfo($_FILES['anexo_nota_fiscal']['name'], PATHINFO_EXTENSION));
    $newFileNameNF = 'nf_' . time() . '_' . rand(100, 999) . '.' . $extensionNF;
    if (move_uploaded_file($_FILES['anexo_nota_fiscal']['tmp_name'], $uploadDirNF . $newFileNameNF)) {
        $anexo_nota_fiscal = '/' . $uploadDirNF . $newFileNameNF;
    }
}

$sql = "INSERT INTO ativos (
            categoria, fabricante, modelo, tag, numero_serie, hostName, valor, macAdress, status, 
            centroDeCusto, setor, fornecedor, descricao, imagem, dataAtivacao, 
            memoria, processador, armazenamento, tipo_armazenamento, 
            numero_nota_fiscal, anexo_nota_fiscal, tier, gpu, polegadas, is_scanner,
            imei, sim_card,
            assigned_type, assigned_to, id_local, parent_asset_id
        ) VALUES (
            '$categoria', '$fabricante', '$modelo', '$tag', '$numero_serie', '$hostName', '$valor', '$macAdress', '$status', 
            '$centroDeCusto', '$setor', '$fornecedor', '$descricao', '$imagem', '$dataAtivacao', 
            " . ($memoria ? "'$memoria'" : "NULL") . ", 
            " . ($processador ? "'$processador'" : "NULL") . ", 
            " . ($armazenamento ? "'$armazenamento'" : "NULL") . ", 
            " . ($tipo_armazenamento ? "'$tipo_armazenamento'" : "NULL") . ", 
            " . ($numero_nota_fiscal ? "'$numero_nota_fiscal'" : "NULL") . ", 
            " . ($anexo_nota_fiscal ? "'$anexo_nota_fiscal'" : "NULL") . ",
            " . ($tier ? "'$tier'" : "NULL") . ",
            " . ($gpu ? "'$gpu'" : "NULL") . ",
            " . ($polegadas ? "'$polegadas'" : "NULL") . ",
            " . ($is_scanner ? "'$is_scanner'" : "NULL") . ",
            " . ($imei ? "'$imei'" : "NULL") . ",
            " . ($sim_card ? "'$sim_card'" : "NULL") . ",
            '$assigned_type', 
            $assigned_to, 
            $id_local, 
            $parent_asset_id
        )";

$inserir = mysqli_query($conn, $sql);

if ($inserir) {
    $ativo_id = mysqli_insert_id($conn);
    
    // Gerar Service Tag padronizada: TAG-XXXXXX (ex: TAG-000123)
    $generated_tag = 'TAG-' . str_pad($ativo_id, 6, '0', STR_PAD_LEFT);
    
    // Atualizar o registro com a nova tag gerada
    $sql_update_tag = "UPDATE ativos SET tag = '$generated_tag' WHERE id_asset = $ativo_id";
    mysqli_query($conn, $sql_update_tag);

    $usuario_id = isset($_SESSION['id_usuarios']) ? $_SESSION['id_usuarios'] : 'NULL';
    $sql_historico = "INSERT INTO historico_ativos (ativo_id, usuario_id, acao, detalhes) VALUES ('$ativo_id', $usuario_id, 'Criação', 'Ativo criado e Service Tag gerada automaticamente: $generated_tag')";
    mysqli_query($conn, $sql_historico);

    if (isset($_POST['ajax'])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'id_asset' => $ativo_id,
            'tag' => $generated_tag,
            'modelo' => $modelo
        ]);
        exit();
    }
    header("Location: equipamentos.php?new_asset_id=$ativo_id&show_tag=1");
    exit();
} else {
    if (isset($_POST['ajax'])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        exit();
    }
    echo "<script>alert('" . __('Erro ao cadastrar: ') . "' + " . json_encode(mysqli_error($conn)) . "); window.history.back();</script>";
}