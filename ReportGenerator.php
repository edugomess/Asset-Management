<?php
require('fpdf/fpdf.php');
include 'conexao.php';

class ReportGenerator extends FPDF
{
    protected $reportTitle;
    protected $columns;
    protected $conn;

    function __construct($title, $columns, $conn, $orientation = 'P')
    {
        parent::__construct($orientation, 'mm', 'A4');
        $this->reportTitle = $title;
        $this->columns = $columns;
        $this->conn = $conn;
    }

    function Header()
    {
        // Logo
        $this->Image('dashboard/images/favicon.png', 10, 6, 15);

        // System Name
        $this->SetFont('Arial', 'B', 15);

        $this->Cell(0, 10, 'Asset MGT', 0, 1, 'C');

        // Report Title
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, $this->utf8_to_iso88591($this->reportTitle), 0, 1, 'C');
        $this->Ln(2);

        // Timestamp
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, $this->utf8_to_iso88591('Gerado em: ' . date('d/m/Y H:i:s')), 0, 1, 'C');
        $this->Ln(10);

        // Table Header
        $this->SetFillColor(44, 64, 74); // Dark blue/grey from dashboard
        $this->SetTextColor(255, 255, 255); // White text
        $this->SetFont('Arial', 'B', 10);
        foreach ($this->columns as $col) {
            $this->Cell($col['width'], 10, $this->utf8_to_iso88591($col['header']), 1, 0, $col['align'], true);
        }
        $this->Ln();
        // Reset text color
        $this->SetTextColor(0, 0, 0);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, $this->utf8_to_iso88591('Página ') . $this->PageNo(), 0, 0, 'C');
    }

    function utf8_to_iso88591($text)
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
    }

    function generate($sql)
    {
        $this->AddPage();
        $this->SetFont('Arial', '', 8);

        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                foreach ($this->columns as $col) {
                    $val = isset($row[$col['field']]) ? $row[$col['field']] : '';

                    // Format output if needed
                    if (isset($col['format'])) {
                        if ($col['format'] == 'money') {
                            $val = 'R$ ' . number_format($val, 2, ',', '.');
                        }
                        elseif ($col['format'] == 'date') {
                            $val = date('d/m/Y', strtotime($val));
                        }
                    }
                    // Truncate if too long
                    $val = substr($val, 0, 40);

                    $this->Cell($col['width'], 7, $this->utf8_to_iso88591($val), 1, 0, $col['align']);
                }
                $this->Ln();
            }
        }
        else {
            $this->Cell(0, 10, $this->utf8_to_iso88591('Nenhum registro encontrado.'), 1, 1, 'C');
        }

        $this->Output();
    }
}
