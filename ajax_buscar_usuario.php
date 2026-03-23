<?php
/**
 * AJAX LOOKUP: ajax_buscar_usuario.php
 * Busca usuários pelo nome ou sobrenome para seleção de gestor.
 */
include 'conexao.php';

header('Content-Type: application/json');

$query = isset($_GET['query']) ? mysqli_real_escape_string($conn, $_GET['query']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id_usuarios, nome, sobrenome, email, funcao FROM usuarios 
        WHERE nome LIKE '%$query%' OR sobrenome LIKE '%$query%' OR email LIKE '%$query%'
        LIMIT 5";
$result = $conn->query($sql);

$usuarios = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = [
            'id' => $row['id_usuarios'],
            'nome_completo' => $row['nome'] . ' ' . $row['sobrenome'],
            'email' => $row['email'],
            'funcao' => $row['funcao']
        ];
    }
}

echo json_encode($usuarios);
$conn->close();
?>
