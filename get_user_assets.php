<?php
include 'conexao.php';

ifb (isset($_GET['userId'])) {
    $userId = intval($_GET['userId']);

    // Consulta para obter os ativos atribuídos ao usuário
    $sql = "SELECT categoria, tag FROM ativos WHERE assigned_to = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $assets = [];
    while ($row = $result->fetch_assoc()) {
        $assets[] = $row;
    }

    // Retorna os dados em formato JSON
    echo json_encode($assets);
}
?>
