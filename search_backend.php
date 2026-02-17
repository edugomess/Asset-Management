<?php
include 'conexao.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];
$search = "%" . mysqli_real_escape_string($conn, $query) . "%";

// 1. Search Users (Usuarios)
$sql_users = "SELECT id_usuarios, nome, sobrenome, email FROM usuarios WHERE nome LIKE '$search' OR sobrenome LIKE '$search' OR email LIKE '$search' LIMIT 5";
$res_users = mysqli_query($conn, $sql_users);
if ($res_users) {
    while ($row = mysqli_fetch_assoc($res_users)) {
        $results[] = [
            'category' => 'Usuários',
            'label' => $row['nome'] . ' ' . $row['sobrenome'] . ' (' . $row['email'] . ')',
            'url' => 'usuarios.php?search=' . urlencode($row['nome']),
            'type' => 'user'
        ];
    }
}

// 2. Search Assets (Ativos - 'ativos' table)
// Correct columns: modelo, tag, hostName (from cadastro_de_equipamentos.php)
$sql_assets = "SELECT id_asset, modelo, tag, hostName FROM ativos WHERE modelo LIKE '$search' OR tag LIKE '$search' OR hostName LIKE '$search' LIMIT 5";
$res_assets = mysqli_query($conn, $sql_assets);
if ($res_assets) {
    while ($row = mysqli_fetch_assoc($res_assets)) {
        $results[] = [
            'category' => 'Ativos',
            'label' => $row['modelo'] . ' - Tag: ' . $row['tag'],
            'url' => 'equipamentos.php?search=' . urlencode($row['tag']),
            'type' => 'asset'
        ];
    }
}

// 3. Search Tickets (Chamados - 'chamados' table)
// Correct columns: id, titulo (from editar_chamado.php)
$sql_tickets = "SELECT id, titulo FROM chamados WHERE titulo LIKE '$search' OR id LIKE '$search' LIMIT 5";
$res_tickets = mysqli_query($conn, $sql_tickets);
if ($res_tickets) {
    while ($row = mysqli_fetch_assoc($res_tickets)) {
        $results[] = [
            'category' => 'Chamados',
            'label' => '#' . $row['id'] . ' - ' . $row['titulo'],
            'url' => 'chamados.php?filtro_status=todos&search=' . urlencode($row['id']),
            'type' => 'ticket'
        ];
    }
}

// 4. Search Cost Centers (Centro de Custo - 'centro_de_custo' table)
// Correct columns: nomeSetor, codigo (from cadastro_de centro_de_custo.php)
$sql_cost = "SELECT id_centro_de_custo, nomeSetor, codigo FROM centro_de_custo WHERE nomeSetor LIKE '$search' OR codigo LIKE '$search' LIMIT 5";
$res_cost = mysqli_query($conn, $sql_cost);
if ($res_cost) {
    while ($row = mysqli_fetch_assoc($res_cost)) {
        $results[] = [
            'category' => 'Centro de Custo',
            'label' => $row['nomeSetor'] . ' (' . $row['codigo'] . ')',
            'url' => 'centro_de_custo.php?search=' . urlencode($row['nomeSetor']),
            'type' => 'cost_center'
        ];
    }
}

// 5. Search Suppliers (Fornecedores - 'fornecedor' table)
// Correct columns: nomeEmpresa, email, cnpj (from cadastro_de_fornecedor.php)
$sql_suppliers = "SELECT id_fornecedor, nomeEmpresa, email, cnpj FROM fornecedor WHERE nomeEmpresa LIKE '$search' OR email LIKE '$search' OR cnpj LIKE '$search' LIMIT 5";
$res_suppliers = mysqli_query($conn, $sql_suppliers);
if ($res_suppliers) {
    while ($row = mysqli_fetch_assoc($res_suppliers)) {
        $results[] = [
            'category' => 'Fornecedores',
            'label' => $row['nomeEmpresa'],
            'url' => 'fornecedores.php?search=' . urlencode($row['nomeEmpresa']),
            'type' => 'supplier'
        ];
    }
}

echo json_encode($results);
mysqli_close($conn);
?>
