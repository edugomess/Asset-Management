<?php
$conn = new mysqli('localhost', 'root', '', 'db_asset_mgt');
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
    exit;
}

echo "Testing connection...\n";
$res = $conn->query("SELECT 1");
if ($res) {
    echo "Connection OK\n";
} else {
    echo "Query failed: " . $conn->error . "\n";
}

echo "\nChecking configuracoes_depreciacao table:\n";
$res = $conn->query("DESCRIBE configuracoes_depreciacao");
if ($res) {
    while($row = $res->fetch_assoc()) {
        echo $row['Field'] . " " . $row['Type'] . "\n";
    }
} else {
    echo "Error describing table: " . $conn->error . "\n";
}

echo "\nChecking for server info:\n";
echo "Server info: " . $conn->server_info . "\n";
?>
