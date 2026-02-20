<?php
include 'auth.php';
include 'conexao.php';

header('Content-Type: application/json');

$id_licenca = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_licenca > 0) {
    $sql = "SELECT u.nome, u.sobrenome, u.email, al.data_atribuicao, al.id_atribuicao 
            FROM atribuicoes_licencas al 
            JOIN usuarios u ON al.id_usuario = u.id_usuarios 
            WHERE al.id_licenca = $id_licenca 
            ORDER BY al.data_atribuicao DESC";

    $result = mysqli_query($conn, $sql);
    $atribuicoes = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $atribuicoes[] = [
            'id' => $row['id_atribuicao'],
            'usuario' => $row['nome'] . ' ' . $row['sobrenome'],
            'email' => $row['email'],
            'data' => date('d/m/Y H:i', strtotime($row['data_atribuicao']))
        ];
    }

    echo json_encode(['success' => true, 'atribuicoes' => $atribuicoes]);
} else {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
}

mysqli_close($conn);
?>