<?php
/**
 * PROCESSAR VOTO KB: processar_voto_kb.php
 * Incrementa votos úteis ou não úteis para métricas da Base de Conhecimento.
 */
include_once 'auth.php';
include_once 'conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = $_GET['type'] ?? '';

header('Content-Type: application/json');

if ($id > 0 && ($type === 'up' || $type === 'down')) {
    $field = ($type === 'up') ? 'votos_uteis' : 'votos_nao_uteis';
    $sql = "UPDATE base_conhecimento SET $field = $field + 1 WHERE id = $id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
}
?>
