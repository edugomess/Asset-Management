Ajustar relatório
<?php
require('fpdf/fpdf.php');

// Inclui o arquivo de conexão com o banco de dados
include 'conexao.php';

// Verifica se a conexão foi estabelecida
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Cria uma classe PDF personalizada que estende a FPDF
class PDF extends FPDF {
    // Cabeçalho do PDF
    function Header() {
        // Define a fonte
        $this->SetFont('Arial', 'B', 12);
        // Título
        $this->Cell(0, 10, 'Relatorio de Ativos', 0, 1, 'C');
        // Linha abaixo do cabeçalho
        $this->Ln(10);

        // Cabeçalho da tabela
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(30, 10, 'Categoria', 1);
        $this->Cell(30, 10, 'Fabricante', 1);
        $this->Cell(30, 10, 'Modelo', 1);
        $this->Cell(20, 10, 'Tag', 1);
        $this->Cell(30, 10, 'Host Name', 1);
        $this->Ln();
    }

    // Rodapé do PDF
    function Footer() {
        // Posição a 1,5 cm do fim
        $this->SetY(-15);
        // Fonte Arial itálico 8
        $this->SetFont('Arial', 'I', 8);
        // Número da página
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Cria um novo PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Consulta para buscar dados da tabela ativo
$query = "SELECT categoria, fabricante, modelo, tag, hostName, ip, macAdress, status, dataAtivacao, centroDeCusto FROM ativos";
$result = $conn->query($query);

// Verifica se há dados e adiciona ao PDF
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(30, 10, htmlspecialchars($row['categoria']), 1);
        $pdf->Cell(30, 10, htmlspecialchars($row['fabricante']), 1);
        $pdf->Cell(30, 10, htmlspecialchars($row['modelo']), 1);
        $pdf->Cell(20, 10, htmlspecialchars($row['tag']), 1);
        $pdf->Cell(30, 10, htmlspecialchars($row['hostName']), 1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0, 10, 'Nenhum ativo encontrado.', 1, 1, 'C');
}

// Fecha a conexão com o banco de dados
$conn->close();

// Gera o PDF no navegador
$pdf->Output();
?>
