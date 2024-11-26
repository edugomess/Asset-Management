<?php
require('fpdf/fpdf.php');

// Inclui o arquivo de conexão com o banco de dados
include 'conexao.php';

// Verifica se a conexão foi estabelecida
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Função para converter texto UTF-8 para ISO-8859-1
function utf8_to_iso88591($text) {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
}

// Cria uma classe PDF personalizada que estende a FPDF
class PDF extends FPDF {
    // Cabeçalho do PDF
    function Header() {
        // Define a fonte
        $this->SetFont('Arial', 'B', 12);
        // Título
        $this->Cell(0, 10, utf8_to_iso88591('Relatório de Ativos'), 0, 1, 'C');
        // Linha abaixo do cabeçalho
        $this->Ln(10);

        // Cabeçalho da tabela
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(30, 10, utf8_to_iso88591('Categoria'), 1);
        $this->Cell(30, 10, utf8_to_iso88591('Fabricante'), 1);
        $this->Cell(30, 10, utf8_to_iso88591('Modelo'), 1);
        $this->Cell(20, 10, utf8_to_iso88591('Tag'), 1);
        $this->Cell(30, 10, utf8_to_iso88591('Host Name'), 1);
        $this->Cell(30, 10, utf8_to_iso88591('Valor'), 1);
        $this->Ln();
    }

    // Rodapé do PDF
    function Footer() {
        // Posição a 1,5 cm do fim
        $this->SetY(-15);
        // Fonte Arial itálico 8
        $this->SetFont('Arial', 'I', 8);
        // Número da página
        $this->Cell(0, 10, utf8_to_iso88591('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

// Inicia o buffer de saída para evitar que qualquer saída anterior atrapalhe o PDF
ob_start();

// Cria um novo PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Consulta para buscar dados da tabela ativo
$query = "SELECT categoria, fabricante, modelo, tag, hostName, valor, macAdress, status, dataAtivacao, centroDeCusto FROM ativos";
$result = $conn->query($query);

// Verifica se há dados e adiciona ao PDF
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(30, 10, utf8_to_iso88591($row['categoria']), 1);
        $pdf->Cell(30, 10, utf8_to_iso88591($row['fabricante']), 1);
        $pdf->Cell(30, 10, utf8_to_iso88591($row['modelo']), 1);
        $pdf->Cell(20, 10, utf8_to_iso88591($row['tag']), 1);
        $pdf->Cell(30, 10, utf8_to_iso88591($row['hostName']), 1);
        $pdf->Cell(30, 10, utf8_to_iso88591($row['valor']), 1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0, 10, utf8_to_iso88591('Nenhum ativo encontrado.'), 1, 1, 'C');
}

// Fecha a conexão com o banco de dados
$conn->close();

// Limpa qualquer saída no buffer antes de enviar o PDF
ob_end_clean();

// Gera o PDF no navegador
$pdf->Output();
?>

