
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
        $this->Cell(0, 10, 'Relatorio de Centro de Custo', 0, 1, 'C');
        // Linha abaixo do cabeçalho
        $this->Ln(10);

        // Cabeçalho da tabela
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(60, 10, 'Setor', 1);
        $this->Cell(40, 10, 'Unidade', 1);
        $this->Cell(30, 10, 'Gestor', 1);
        $this->Cell(30, 10, 'Codigo', 1);
        $this->Ln();
    }

    // Rodapé do PDF
    function Footer() {
        // Posição a 1,5 cm do fim
        $this->SetY(-15);
        // Fonte Arial itálico 8
        $this->SetFont('Arial', 'I', 8);
        // Número da página
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Evita qualquer saída antes de gerar o PDF
ob_start();

// Cria um novo PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Consulta para buscar dados da tabela centro_de_custo
$query = "SELECT nomeSetor, unidade, gestor, codigo FROM centro_de_custo";
$result = $conn->query($query);

// Verifica se há dados e adiciona ao PDF
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(60, 10, $row['nomeSetor'], 1);
        $pdf->Cell(40, 10, $row['unidade'], 1);
        $pdf->Cell(30, 10, $row['gestor'], 1);
        $pdf->Cell(30, 10, $row['codigo'], 1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0, 10, 'Nenhum ativo encontrado.', 1, 1, 'C');
}

// Fecha a conexão com o banco de dados
$conn->close();

// Limpa qualquer saída no buffer antes de enviar o PDF
ob_end_clean();

// Gera o PDF no navegador
$pdf->Output();
?>
