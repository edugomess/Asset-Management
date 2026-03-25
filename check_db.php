<?php
$c = new mysqli('localhost', 'root', '', 'db_asset_mgt');
$res = $c->query('DESCRIBE ativos');
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
}
