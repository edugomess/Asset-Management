<?php
/**
 * AJAX LOOKUP: ajax_buscar_ativo.php
 * Busca informações de um ativo com base na Service Tag (campo 'tag' na tabela ativos).
 */
include 'conexao.php';
include 'language.php';

header('Content-Type: application/json');

$tag = isset($_GET['tag']) ? mysqli_real_escape_string($conn, $_GET['tag']) : '';

if (empty($tag)) {
    echo json_encode(['success' => false, 'message' => __('Tag não informada.')]);
    exit;
}

$sql = "SELECT id_asset, modelo, fabricante, categoria, status FROM ativos WHERE tag LIKE '$tag%' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $ativo = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'ativo' => [
            'id' => $ativo['id_asset'],
            'modelo' => $ativo['modelo'],
            'fabricante' => $ativo['fabricante'],
            'categoria' => __($ativo['categoria']),
            'status' => __($ativo['status']),
            'link_perfil' => 'perfil_ativo.php?id=' . $ativo['id_asset']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => __('Ativo não encontrado.')]);
}

$conn->close();
?>
