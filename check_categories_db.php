<?php
include 'conexao.php';
$sql = "SELECT categoria FROM categoria";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo $row["categoria"] . "\n";
    }
} else {
    echo "0 results";
}
$conn->close();
?>