<?php
include 'conexao.php';

// Simulate the POST
$_SERVER["REQUEST_METHOD"] = "POST";
$_POST['idioma_config'] = 1;
$_POST['idioma'] = 'en-US';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';

// We bypass exit by writing the logic directly to see what it does
$idioma = $_POST['idioma'] ?? 'pt-BR';
$idioma = mysqli_real_escape_string($conn, $idioma);

$sql = "UPDATE configuracoes_alertas SET idioma = '$idioma' WHERE id = 1";
$success = mysqli_query($conn, $sql);

if ($success) {
    echo "Successfully updated to $idioma\n";
} else {
    echo "Failed to update\n";
}

$alert_config = [];
$result_alert = mysqli_query($conn, "SELECT * FROM configuracoes_alertas LIMIT 1");
if ($result_alert && mysqli_num_rows($result_alert) > 0) {
    $alert_config = mysqli_fetch_assoc($result_alert);
}
echo "DB value is now: " . $alert_config['idioma'] . "\n";
?>
