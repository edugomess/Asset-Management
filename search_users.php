<?php
include 'conexao.php'; // Inclui a conexão com o banco de dados

header('Content-Type: application/json');

// Obtenha o texto da pesquisa
$query = isset($_GET['query']) ? mysqli_real_escape_string($conn, $_GET['query']) : '';

// Verifique se há uma entrada de pesquisa
if (!empty($query)) {
    // Consulte os usuários no banco de dados
    $sql = "SELECT id, nome FROM usuarios WHERE nome LIKE '%$query%' LIMIT 10";
    $result = mysqli_query($conn, $sql);

    $users = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = [
                'id' => $row['id'],
                'name' => $row['nome']
            ];
        }
    }

    // Retorne os resultados como JSON
    echo json_encode($users);
} else {
    echo json_encode([]); // Retorna vazio se não houver query
}

mysqli_close($conn);
?>
