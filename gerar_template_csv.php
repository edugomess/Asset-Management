<?php
/**
 * GERADOR DE TEMPLATE CSV: gerar_template_csv.php
 * Fornece um arquivo CSV vazio com cabeçalhos para guiar a importação de dados.
 */
include_once 'auth.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'ativos';
$filename = "template_importacao_" . $type . ".csv";

// Cabeçalhos HTTP para download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// UTF-8 BOM para Excel ler corretamente em Português
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

// Definir delimitador como ponto-e-vírgula (padrão Excel Brasil)
$delimiter = ";";

if ($type === 'usuarios') {
    $headers = [
        'Nome', 'Sobrenome', 'UsuarioAD', 'Matricula', 'Email', 
        'Funcao', 'Setor', 'CentroDeCusto', 'Unidade', 'Perfil', 
        'TipoContrato', 'CPF', 'Telefone', 'Data_Nascimento'
    ];
    fputcsv($output, $headers, $delimiter);
    // Linha de Exemplo
    fputcsv($output, [
        'João', 'Silva', 'joao.silva', '123456', 'joao@empresa.com', 
        'Analista', 'TI', '001 - ADMINISTRATIVO', 'Matriz', 
        'Usuário', 'CLT', '123.456.789-00', '(11) 99999-9999', '1990-01-01'
    ], $delimiter);
} else {
    // Cabeçalhos expandidos para Ativos (31 campos totais suportados pela lógica mas limitados ao mapeamento de 27 do handler)
    $headers = [
        'Tag', 'Fabricante', 'Modelo', 'Categoria', 'Status', 'HostName', 
        'CentroDeCusto', 'Setor', 'Numero_Serie', 'Valor', 'MacAdress', 
        'IMEI', 'SIM_Card', 'Processador', 'Memoria', 'Armazenamento', 
        'Tipo_Armazenamento', 'Polegadas', 'GPU', 'Possui_Scanner', 
        'Nota_Fiscal', 'Fornecedor', 'Descricao', 'Atribuido_Para_Email', 
        'Local_Nome', 'Tag_Ativo_Pai', 'Tier'
    ];
    fputcsv($output, $headers, $delimiter);
    // Linha de Exemplo (com preenchimento total)
    fputcsv($output, [
        'TAG123', 'Dell', 'Latitude 3420', 'Notebook', 'Disponível', 
        'BR-LT-123', '001 - ADMINISTRATIVO', 'TI', 'S3R14L', 
        '5000.00', 'AA:BB:CC:DD:EE:FF', '', '', 'i5 11th', 
        '16GB', '512GB', 'SSD', '14', 'Integrada', 'Não', 'NF-1001', 
        'Dell Brasil', 'Equipamento em excelente estado', '', '', '', 'Tier 1'
    ], $delimiter);
}

fclose($output);
exit();
?>
