<?php
include 'conexao.php';

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$sql = "SELECT id_centro_de_custo, nomeSetor FROM centro_de_custo";
$result = $conn->query($sql);

echo "<h2>Verificando Tabela centro_de_custo</h2>";
if ($result) {
    if ($result->num_rows > 0) {
        echo "<table border='1'><tr><th>ID</th><th>Nome</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["id_centro_de_custo"] . "</td><td>" . $row["nomeSetor"] . "</td></tr>";
        }
        echo "</table>";
    }
    else {
        echo "0 resultados encontrados na tabela centro_de_custo.";
    }
}
else {
    echo "Erro na query: " . $conn->error;
}
$conn->close();
?>
