<?php
/**
 * AJAX LOOKUP: ajax_buscar_local.php
 * Busca locais pelo nome para seleção de atribuição.
 */
include 'conexao.php';

header('Content-Type: application/json');

$query = isset($_GET['query']) ? mysqli_real_escape_string($conn, $_GET['query']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id_local, nome_local, tipo_local FROM locais 
        WHERE nome_local LIKE '%$query%' OR tipo_local LIKE '%$query%'
        LIMIT 5";
$result = $conn->query($sql);

$locais = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $locais[] = [
            'id_local' => $row['id_local'],
            'nome_local' => $row['nome_local'],
            'tipo_local' => $row['tipo_local']
        ];
    }
}

echo json_encode($locais);
$conn->close();
?>
