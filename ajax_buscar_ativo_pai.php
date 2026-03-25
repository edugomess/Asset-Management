<?php
/**
 * AJAX LOOKUP: ajax_buscar_ativo_pai.php
 * Busca ativos pai pelo nome, modelo ou tag para seleção de atribuição hierárquica.
 */
include 'conexao.php';

header('Content-Type: application/json');

$query = isset($_GET['query']) ? mysqli_real_escape_string($conn, $_GET['query']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id_asset, modelo, tag, categoria FROM ativos 
        WHERE modelo LIKE '%$query%' OR tag LIKE '%$query%' OR categoria LIKE '%$query%'
        LIMIT 5";
$result = $conn->query($sql);

$ativos = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ativos[] = [
            'id_asset' => $row['id_asset'],
            'nome' => $row['modelo'],
            'detalhes' => $row['tag'] . ' - ' . $row['categoria']
        ];
    }
}

echo json_encode($ativos);
$conn->close();
?>
