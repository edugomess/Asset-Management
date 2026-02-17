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
$sql_users = "SELECT id_usuarios, nome, email FROM usuarios WHERE nome LIKE '$search' OR email LIKE '$search' LIMIT 5";
$res_users = mysqli_query($conn, $sql_users);
if ($res_users) {
    while ($row = mysqli_fetch_assoc($res_users)) {
        $results[] = [
            'category' => 'Usuários',
            'label' => $row['nome'] . ' (' . $row['email'] . ')',
            'url' => 'usuarios.php?search=' . urlencode($row['nome']), // Assuming usuarios.php handles search param or we link to edit
            'type' => 'user'
        ];
    }
}

// 2. Search Assets (Ativos/Equipamentos)
// checking equipamentos.php schema: nomeModelo, serviceTag, patrimonio
$sql_assets = "SELECT id_asset, nomeModelo, serviceTag, patrimonio FROM ativos WHERE nomeModelo LIKE '$search' OR serviceTag LIKE '$search' OR patrimonio LIKE '$search' LIMIT 5";
$res_assets = mysqli_query($conn, $sql_assets);
if ($res_assets) {
    while ($row = mysqli_fetch_assoc($res_assets)) {
        $results[] = [
            'category' => 'Ativos',
            'label' => $row['nomeModelo'] . ' - Tag: ' . $row['serviceTag'],
            'url' => 'equipamentos.php?search=' . urlencode($row['serviceTag']),
            'type' => 'asset'
        ];
    }
}

// 3. Search Tickets (Chamados)
// checking chamados.php schema: id_chamado, titulo, descricao
$sql_tickets = "SELECT id_chamado, titulo FROM chamados WHERE titulo LIKE '$search' OR id_chamado LIKE '$search' LIMIT 5";
$res_tickets = mysqli_query($conn, $sql_tickets);
if ($res_tickets) {
    while ($row = mysqli_fetch_assoc($res_tickets)) {
        $results[] = [
            'category' => 'Chamados',
            'label' => '#' . $row['id_chamado'] . ' - ' . $row['titulo'],
            'url' => 'chamados.php?search=' . urlencode($row['id_chamado']),
            'type' => 'ticket'
        ];
    }
}

// 4. Search Cost Centers (Centro de Custo)
// checking centro_de_custo.php schema: nomeSetor, codigo
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

// 5. Search Suppliers (Fornecedores)
// checking fornecedores.php schema: nomeEmpresa, email
$sql_suppliers = "SELECT id_fornecedor, nomeEmpresa, email FROM fornecedor WHERE nomeEmpresa LIKE '$search' OR email LIKE '$search' LIMIT 5";
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
