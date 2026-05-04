<?php
require_once 'funcoes_ai.php';
require_once 'credentials.php';
session_start();
$res = callGitHubModels('teste', '', GITHUB_TOKEN, []);
var_dump($res);
echo "\nERROR: " . $_SESSION['last_ai_error'];
?>
