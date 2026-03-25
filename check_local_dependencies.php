<?php
/**
 * AJAX: check_local_dependencies.php
 * Verifica e retorna uma lista de ativos e sub-locais vinculados a um ID de local.
 */
include_once 'auth.php';
include_once 'conexao.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(['error' => 'ID inválido']);
    exit();
}

$response = [
    'has_dependencies' => false,
    'assets' => [],
    'children' => []
];

// 1. Buscar ativos vinculados
$sql_assets = "SELECT id_asset, tag, modelo FROM ativos WHERE id_local = $id";
$res_assets = $conn->query($sql_assets);
while ($row = $res_assets->fetch_assoc()) {
    $response['assets'][] = [
        'id' => $row['id_asset'],
        'tag' => $row['tag'],
        'modelo' => $row['modelo']
    ];
}

// 2. Buscar sub-locais vinculados
$sql_children = "SELECT id_local, nome_local, tipo_local FROM locais WHERE id_parent_local = $id";
$res_children = $conn->query($sql_children);
while ($row = $res_children->fetch_assoc()) {
    $response['children'][] = [
        'id' => $row['id_local'],
        'nome' => $row['nome_local'],
        'tipo' => $row['tipo_local']
    ];
}

if (!empty($response['assets']) || !empty($response['children'])) {
    $response['has_dependencies'] = true;
}

echo json_encode($response);
$conn->close();
