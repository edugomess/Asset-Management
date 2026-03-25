<?php
include 'conexao.php';
$result = $conn->query("DESCRIBE atribuicoes");
if ($result) {
    while($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Erro: " . $conn->error;
}
$conn->close();
?>
