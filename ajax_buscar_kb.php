<?php
/**
 * BUSCA EM TEMPO REAL NA BASE DE CONHECIMENTO: ajax_buscar_kb.php
 * Endpoint acionado durante a criação de chamados para sugerir soluções automáticas.
 */
include 'conexao.php';
header('Content-Type: application/json');

// Captura a busca e protege contra Injeção SQL básica
$query = isset($_GET['query']) ? mysqli_real_escape_string($conn, $_GET['query']) : '';

// Requisito mínimo de caracteres para evitar processamento desnecessário
if (strlen($query) < 3) {
    echo json_encode([]);
    exit;
}

// Busca por proximidade no título ou nas palavras-chave
$sql = "SELECT id, titulo, conteudo FROM base_conhecimento 
        WHERE titulo LIKE '%$query%' OR palavras_chave LIKE '%$query%' 
        ORDER BY votos_uteis DESC LIMIT 3";
$result = $conn->query($sql);

$articles = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $articles[] = [
            'id' => $row['id'],
            'titulo' => $row['titulo'],
            'conteudo' => mb_substr(strip_tags($row['conteudo']), 0, 150) . (mb_strlen($row['conteudo']) > 150 ? '...' : '')
        ];
    }
}

echo json_encode($articles);
?>
