<?php
$pt = include 'c:/xampp/htdocs/lang/pt-BR.php';
$en = include 'c:/xampp/htdocs/lang/en-US.php';

$missing_in_en = array_diff_key($pt, $en);
$missing_in_pt = array_diff_key($en, $pt);

echo "Missing in EN:\n";
foreach ($missing_in_en as $key => $val) {
    echo "  - $key\n";
}

echo "\nMissing in PT:\n";
foreach ($missing_in_pt as $key => $val) {
    echo "  - $key\n";
}
