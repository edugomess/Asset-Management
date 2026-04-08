<?php
$conn = new mysqli('localhost', 'root', '', 'db_asset_mgt'); 
$res = $conn->query('DESCRIBE configuracoes_depreciacao'); 
while($row = $res->fetch_assoc()) { 
    print_r($row); 
}
?>
