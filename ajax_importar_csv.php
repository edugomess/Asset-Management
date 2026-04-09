<?php
/**
 * HANDLER DE IMPORTAÇÃO CSV: ajax_importar_csv.php
 * Processa o upload de arquivos CSV para Ativos ou Usuários.
 */
header('Content-Type: application/json');
include_once 'auth.php';
include_once 'conexao.php';

// Segurança: Apenas Admin/Suporte
if (!isset($_SESSION['nivelUsuario']) || ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte')) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csv_file'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit();
}

$type = isset($_POST['type']) ? $_POST['type'] : 'ativos';
$file = $_FILES['csv_file']['tmp_name'];

if (!is_uploaded_file($file)) {
    echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo.']);
    exit();
}

$results = ['imported' => 0, 'skipped' => 0, 'errors' => 0];
$handle = fopen($file, "r");

// Ignora o cabeçalho (pula a primeira linha)
// Detecta se é UTF-8 com BOM e pula
$bom = fread($handle, 3);
if ($bom != "\xEF\xBB\xBF") {
    rewind($handle);
}
fgetcsv($handle, 1000, ";");

while (($data = fgetcsv($handle, 2000, ";")) !== FALSE) {
    if (empty($data[0]))
        continue; // Pula linhas vazias

    if ($type === 'usuarios') {
        // Mapeamento Usuários Expandido (14 campos)
        $nome = mysqli_real_escape_string($conn, $data[0] ?? '');
        $sobrenome = mysqli_real_escape_string($conn, $data[1] ?? '');
        $usuarioAD = mysqli_real_escape_string($conn, $data[2] ?? '');
        $matricula = mysqli_real_escape_string($conn, $data[3] ?? '');
        $email = mysqli_real_escape_string($conn, $data[4] ?? '');
        $funcao = mysqli_real_escape_string($conn, $data[5] ?? '');
        $setor = mysqli_real_escape_string($conn, $data[6] ?? '');
        $cc = mysqli_real_escape_string($conn, $data[7] ?? '');
        $unidade = mysqli_real_escape_string($conn, $data[8] ?? '');
        $perfil_input = mysqli_real_escape_string($conn, $data[9] ?? 'Usuário');
        $contrato = mysqli_real_escape_string($conn, $data[10] ?? 'CLT');
        $cpf = preg_replace('/[^0-9]/', '', $data[11] ?? '');
        $tel = mysqli_real_escape_string($conn, $data[12] ?? '');
        $nascimento = mysqli_real_escape_string($conn, $data[13] ?? '');

        // Normalização do Nível de Usuário
        $nivel = 'Usuário';
        if (stripos($perfil_input, 'adm') !== false)
            $nivel = 'Admin';
        elseif (stripos($perfil_input, 'sup') !== false)
            $nivel = 'Suporte';

        if (empty($email) || empty($usuarioAD)) {
            $results['errors']++;
            continue;
        }

        $check = mysqli_query($conn, "SELECT id_usuarios FROM usuarios WHERE email = '$email' OR usuarioAD = '$usuarioAD'");
        if (mysqli_num_rows($check) > 0) {
            $results['skipped']++;
            continue;
        }

        $pwd = sha1('Asset@123'); // Seguindo padrão do inserir_usuario.php
        $sql = "INSERT INTO usuarios (nome, sobrenome, usuarioAD, matricula, email, funcao, setor, centroDeCusto, unidade, nivelUsuario, tipoContrato, cpf, telefone, dataNascimento, senha, status, foto_perfil) 
                VALUES ('$nome', '$sobrenome', '$usuarioAD', '$matricula', '$email', '$funcao', '$setor', '$cc', '$unidade', '$nivel', '$contrato', '$cpf', '$tel', '$nascimento', '$pwd', 'Ativo', '/assets/img/no-image.png')";

        if (mysqli_query($conn, $sql))
            $results['imported']++;
        else
            $results['errors']++;

    } else {
        // Mapeamento Ativos Expandido (27 campos)
        $tag = mysqli_real_escape_string($conn, $data[0] ?? '');
        $fab = mysqli_real_escape_string($conn, $data[1] ?? '');
        $mod = mysqli_real_escape_string($conn, $data[2] ?? '');
        $cat = mysqli_real_escape_string($conn, $data[3] ?? '');
        $status = mysqli_real_escape_string($conn, $data[4] ?? 'Disponível');
        $host = mysqli_real_escape_string($conn, $data[5] ?? '');
        $cc = mysqli_real_escape_string($conn, $data[6] ?? 'Nenhum');
        $setor = mysqli_real_escape_string($conn, $data[7] ?? '');
        $ser = mysqli_real_escape_string($conn, $data[8] ?? '');
        $val = floatval(str_replace(['R$', ' ', '.', ','], ['', '', '', '.'], $data[9] ?? 0));
        $mac = mysqli_real_escape_string($conn, $data[10] ?? '');
        $imei = mysqli_real_escape_string($conn, $data[11] ?? '');
        $sim = mysqli_real_escape_string($conn, $data[12] ?? '');
        $proc = mysqli_real_escape_string($conn, $data[13] ?? '');
        $mem = mysqli_real_escape_string($conn, $data[14] ?? '');
        $disk = mysqli_real_escape_string($conn, $data[15] ?? '');
        $disk_type = mysqli_real_escape_string($conn, $data[16] ?? 'SSD');
        $size = mysqli_real_escape_string($conn, $data[17] ?? '');
        $gpu = mysqli_real_escape_string($conn, $data[18] ?? '');
        $scanner = mysqli_real_escape_string($conn, $data[19] ?? 'Não');
        $nf = mysqli_real_escape_string($conn, $data[20] ?? '');
        $forn = mysqli_real_escape_string($conn, $data[21] ?? 'Nenhum');
        $desc = mysqli_real_escape_string($conn, $data[22] ?? '');
        $occ_email = mysqli_real_escape_string($conn, $data[23] ?? '');
        $loc_name = mysqli_real_escape_string($conn, $data[24] ?? '');
        $p_tag = mysqli_real_escape_string($conn, $data[25] ?? '');
        $tier = mysqli_real_escape_string($conn, $data[26] ?? '');

        // Lookups Automáticos
        $occ_id = 'NULL';
        if (!empty($occ_email)) {
            $r_u = mysqli_query($conn, "SELECT id_usuarios FROM usuarios WHERE email = '$occ_email' LIMIT 1");
            if ($u = mysqli_fetch_assoc($r_u))
                $occ_id = $u['id_usuarios'];
        }

        $loc_id = 'NULL';
        if (!empty($loc_name)) {
            $r_l = mysqli_query($conn, "SELECT id_local FROM locais WHERE nome_local = '$loc_name' LIMIT 1");
            if ($l = mysqli_fetch_assoc($r_l))
                $loc_id = $l['id_local'];
        }

        $p_id = 'NULL';
        if (!empty($p_tag)) {
            $r_p = mysqli_query($conn, "SELECT id_asset FROM ativos WHERE tag = '$p_tag' LIMIT 1");
            if ($p = mysqli_fetch_assoc($r_p))
                $p_id = $p['id_asset'];
        }

        $assigned_type = ($occ_id !== 'NULL') ? 'Usuario' : (($loc_id !== 'NULL') ? 'Local' : 'Usuario');

        $check = mysqli_query($conn, "SELECT id_asset FROM ativos WHERE tag = '$tag'");
        if (mysqli_num_rows($check) > 0) {
            $results['skipped']++;
            continue;
        }

        $sql = "INSERT INTO ativos (tag, fabricante, modelo, categoria, status, hostName, centroDeCusto, setor, numero_serie, valor, macAdress, imei, sim_card, processador, memoria, armazenamento, tipo_armazenamento, polegadas, gpu, is_scanner, numero_nota_fiscal, fornecedor, descricao, assigned_type, assigned_to, id_local, parent_asset_id, tier, dataAtivacao, imagem) 
                VALUES ('$tag', '$fab', '$mod', '$cat', '$status', '$host', '$cc', '$setor', '$ser', '$val', '$mac', '$imei', '$sim', '$proc', '$mem', '$disk', '$disk_type', '$size', '$gpu', '$scanner', '$nf', '$forn', '$desc', '$assigned_type', $occ_id, $loc_id, $p_id, '$tier', NOW(), '/assets/img/no-image.png')";

        if (mysqli_query($conn, $sql))
            $results['imported']++;
        else
            $results['errors']++;
    }
}

fclose($handle);

echo json_encode([
    'success' => true,
    'results' => $results
]);
exit();
?>