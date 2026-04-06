<?php
$en = include 'lang/en-US.php';
$pt = include 'lang/pt-BR.php';

$keys_en = array_keys($en);
$keys_pt = array_keys($pt);

$missing_in_pt = array_diff($keys_en, $keys_pt);
$missing_in_en = array_diff($keys_pt, $keys_en);

echo "Missing in PT:\n";
foreach ($missing_in_pt as $k) {
    echo "'$k' => '???'\n";
}

echo "\nMissing in EN:\n";
foreach ($missing_in_en as $k) {
    echo "'$k' => '???'\n";
}
?>
