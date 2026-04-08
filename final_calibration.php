<?php
require 'conexao.php';

echo "--- REPARO DE DADOS NA TABELA VENDA ---\n";
// Atualizar itens antigos de 'Ativo' para 'Doado' (pois antes no diferenciava)
$conn->query("UPDATE venda SET status = 'Doado' WHERE status = 'Ativo' OR status = '' OR status IS NULL");
echo "Registros atualizados para Doado: " . $conn->affected_rows . "\n";

// Garantir que no haja itens sem status
$conn->query("UPDATE venda SET status = 'Leiloado' WHERE id_venda IN (SELECT id_venda FROM (SELECT id_venda FROM venda WHERE status = '') as t)");
echo "----\n";

echo "--- CORREO DO SCRIPT DE DOAO ---\n";
// O script original estava tentando inserir na coluna inexistente 'imagem'
// Vamos ler e consertar o doar_ativo.php
$content = file_get_contents('doar_ativo.php');
// Remover 'imagem' da query de INSERT e dos bind_params
$new_content = str_replace(
    'macAdress, imagem, status',
    'macAdress, status',
    $content
);
$new_content = str_replace(
    'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    $new_content
);
// Ajustar bind_param (remover $ativo['imagem'] e uma string 's')
$new_content = str_replace(
    "'sssssssssssi i'",
    "'sssssssssssi'",
    $new_content
);
$new_content = str_replace(
    "\$ativo['imagem'],",
    "",
    $new_content
);
// Remover o espao extra se houver
$new_content = preg_replace('/bind_param\s*\(\s*\'sssssssssssi\s*i\'/i', "bind_param('sssssssssssi'", $new_content);

file_put_contents('doar_ativo.php', $new_content);
echo "doar_ativo.php calibrado.\n";
?>
