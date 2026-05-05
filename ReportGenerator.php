<?php
/**
 * GERADOR DE RELATÓRIOS PDF: ReportGenerator.php
 * Classe extendida do FPDF para padronizar a criação de relatórios do sistema.
 * Gerencia automaticamente cabeçalho, rodapé, conversão de caracteres e formatação.
 */
require_once 'fpdf/fpdf.php';
include_once 'conexao.php';

class ReportGenerator extends FPDF
{
    protected $reportTitle;
    protected $columns;
    protected $conn;
    protected $startDate;
    protected $endDate;

    public static function getLogoPath($conn)
    {
        $logo = 'dashboard/images/favicon.png';
        $res = $conn->query("SELECT logo_path FROM configuracoes_alertas WHERE id = 1");
        if ($res && $row = $res->fetch_assoc()) {
            if (!empty($row['logo_path']) && file_exists(__DIR__ . '/' . $row['logo_path'])) {
                $logo = $row['logo_path'];
            }
        }
        return $logo;
    }

    public function __construct($title, $columns, $conn, $orientation = 'P')
    {
        // ... (auth check remains the same) ...
        if (!isset($_SESSION['nivelUsuario']) || ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte')) {
            die($this->utf8_to_iso88591('⚠️ Acesso Negado.'));
        }

        parent::__construct($orientation, 'mm', 'A4');
        $this->SetMargins(6, 6, 6);
        $this->SetAutoPageBreak(true, 10);
        $this->reportTitle = $title;
        $this->columns = $columns;
        $this->conn = $conn;

        // Captura datas do período se disponíveis
        $this->startDate = isset($_GET['start']) ? $this->conn->real_escape_string($_GET['start']) : null;
        $this->endDate = isset($_GET['end']) ? $this->conn->real_escape_string($_GET['end']) : null;
    }

    public function Header()
    {
        // Logo dinâmico
        $logo = self::getLogoPath($this->conn);

        // Layout de Cabeçalho Profissional
        $this->Image($logo, 6, 6, 12);
        
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(44, 64, 74); // Cor primária do dashboard
        $this->Cell(15); // Espaço para o logo
        $this->Cell(100, 10, 'Asset MGT', 0, 0, 'L');
        
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, $this->utf8_to_iso88591('Gerado em: ' . date('d/m/Y H:i:s')), 0, 1, 'R');
        
        $this->Ln(-2);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, $this->utf8_to_iso88591(mb_strtoupper($this->reportTitle)), 0, 1, 'C');

        // Exibir Período se filtrado
        if ($this->startDate || $this->endDate) {
            $this->SetFont('Arial', 'I', 9);
            $this->SetTextColor(100, 100, 100);
            $periodo = 'Período: ';
            $periodo .= $this->startDate ? date('d/m/Y', strtotime($this->startDate)) : 'Início';
            $periodo .= ' até ';
            $periodo .= $this->endDate ? date('d/m/Y', strtotime($this->endDate)) : 'Hoje';
            $this->Cell(0, 5, $this->utf8_to_iso88591($periodo), 0, 1, 'C');
            $this->Ln(2);
        }
        
        $this->Line(6, 23, 204, 23); // Linha horizontal moderna (210 - 6 - 6 = 198 span)
        $this->Ln(5);

        // Table Header
        $this->SetFillColor(44, 64, 74);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 9);
        foreach ($this->columns as $col) {
            $this->Cell($col['width'], 10, $this->utf8_to_iso88591($col['header']), 1, 0, $col['align'], true);
        }
        $this->Ln();
        // Reset text color
        $this->SetTextColor(0, 0, 0);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, $this->utf8_to_iso88591('Página ') . $this->PageNo(), 0, 0, 'C');
    }

    public function utf8_to_iso88591($text)
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
    }

    public function generate($sql, $dateColumn = null)
    {
        // Tenta detectar a coluna de data se não for informada
        if (!$dateColumn && ($this->startDate || $this->endDate)) {
            if (stripos($sql, 'historico_ativos h') !== false || stripos($sql, ' historico_ativos ') !== false) {
                $dateColumn = (stripos($sql, 'historico_ativos h') !== false) ? 'h.data_evento' : 'data_evento';
            } elseif (stripos($sql, 'chamados c') !== false || stripos($sql, ' chamados ') !== false) {
                $dateColumn = (stripos($sql, 'chamados c') !== false) ? 'c.data_abertura' : 'data_abertura';
            } elseif (stripos($sql, 'manutencao m') !== false || stripos($sql, ' manutencao ') !== false) {
                $dateColumn = (stripos($sql, 'manutencao m') !== false) ? 'm.data_inicio' : 'data_inicio';
            } elseif (stripos($sql, 'licencas_atribuicoes la') !== false || stripos($sql, ' licencas_atribuicoes ') !== false) {
                $dateColumn = (stripos($sql, 'licencas_atribuicoes la') !== false) ? 'la.data_atribuicao' : 'data_atribuicao';
            } elseif (stripos($sql, 'licencas l') !== false || stripos($sql, ' licencas ') !== false) {
                $dateColumn = (stripos($sql, 'licencas l') !== false) ? 'l.data_aquisicao' : 'data_aquisicao';
            } elseif (stripos($sql, 'venda v') !== false || stripos($sql, ' venda ') !== false) {
                $dateColumn = (stripos($sql, 'venda v') !== false) ? 'v.data_venda' : 'data_venda';
            } elseif (stripos($sql, 'ativos a') !== false || stripos($sql, ' ativos ') !== false) {
                $dateColumn = (stripos($sql, 'ativos a') !== false) ? 'a.dataAtivacao' : 'dataAtivacao';
            } elseif (stripos($sql, 'usuarios u') !== false || stripos($sql, ' usuarios ') !== false) {
                $dateColumn = (stripos($sql, 'usuarios u') !== false) ? 'u.data_criacao' : 'data_criacao';
            }
        }

        // Aplica filtro de data se uma coluna foi definida ou detectada
        if ($dateColumn && ($this->startDate || $this->endDate)) {
            $where = "";
            if ($this->startDate && $this->endDate) {
                $where = "$dateColumn BETWEEN '{$this->startDate}' AND '{$this->endDate}'";
            } elseif ($this->startDate) {
                $where = "$dateColumn >= '{$this->startDate}'";
            } elseif ($this->endDate) {
                $where = "$dateColumn <= '{$this->endDate}'";
            }

            if (!empty($where)) {
                // Insere o filtro antes de ORDER BY, GROUP BY ou LIMIT para evitar erro de sintaxe
                $parts = preg_split('/(ORDER\s+BY|GROUP\s+BY|LIMIT)/i', $sql, 2, PREG_SPLIT_DELIM_CAPTURE);
                $baseSql = $parts[0];
                $suffix = isset($parts[1]) ? $parts[1] . $parts[2] : '';

                $connector = (stripos($baseSql, 'WHERE') !== false) ? ' AND ' : ' WHERE ';
                $sql = $baseSql . $connector . $where . ' ' . $suffix;
            }
        }

        if (isset($_GET['format']) && $_GET['format'] == 'xlsx') {
            $this->generateExcel($sql);
            return;
        }

        $this->AddPage();
        $this->SetFont('Arial', '', 8);

        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $fill = false; // Alternância de cores
            while ($row = $result->fetch_assoc()) {
                // Background color for alternating rows
                if ($fill) {
                    $this->SetFillColor(245, 245, 245);
                } else {
                    $this->SetFillColor(255, 255, 255);
                }

                foreach ($this->columns as $col) {
                    $val = isset($row[$col['field']]) ? $row[$col['field']] : '';

                    // Format output if needed
                    if (isset($col['format'])) {
                        if ($col['format'] == 'money' || $col['format'] == 'currency') {
                            $val = 'R$ ' . number_format($val, 2, ',', '.');
                        } elseif ($col['format'] == 'date') {
                            $val = date('d/m/Y', strtotime($val));
                        }
                    }
                    // Truncate precisely to prevent leakage if it's too long for the column
                    $cellWidth = $col['width'] - 1; // 0.5mm margin on each side
                    if ($this->GetStringWidth($this->utf8_to_iso88591($val)) > $cellWidth) {
                        while ($this->GetStringWidth($this->utf8_to_iso88591($val . '..')) > $cellWidth && mb_strlen($val) > 0) {
                            $val = mb_substr($val, 0, -1);
                        }
                        $val .= '..';
                    }

                    $this->Cell($col['width'], 7, $this->utf8_to_iso88591($val), 1, 0, $col['align'], true);
                }
                $this->Ln();
                $fill = !$fill;
            }
        } else {
            $this->Cell(0, 10, $this->utf8_to_iso88591('Nenhum registro encontrado.'), 1, 1, 'C');
        }

        $this->Output();
    }

    /**
     * Gera um arquivo Excel (.xls) baseado em uma tabela HTML.
     * Utiliza cabeçalhos HTTP para forçar o download.
     */
    public function generateExcel($sql)
    {
        $result = $this->conn->query($sql);
        $filename = str_replace(' ', '_', $this->reportTitle) . "_" . date('Y-m-d_H-i-s') . ".xls";

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename");
        header("Pragma: no-cache");
        header("Expires: 0");

        // UTF-8 BOM para Excel
        echo "\xEF\xBB\xBF";

        echo "<table border='1'>";
        echo "<thead><tr>";
        foreach ($this->columns as $col) {
            echo "<th style='background-color: #2c404a; color: white;'>" . htmlspecialchars($col['header']) . "</th>";
        }
        echo "</tr></thead>";
        echo "<tbody>";

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                foreach ($this->columns as $col) {
                    $val = isset($row[$col['field']]) ? $row[$col['field']] : '';

                    if (isset($col['format'])) {
                        if ($col['format'] == 'money') {
                            $val = 'R$ ' . number_format($val, 2, ',', '.');
                        } elseif ($col['format'] == 'date') {
                            $val = date('d/m/Y', strtotime($val));
                        }
                    }
                    echo "<td>" . htmlspecialchars($val) . "</td>";
                }
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='" . count($this->columns) . "'>Nenhum registro encontrado.</td></tr>";
        }

        echo "</tbody></table>";
        exit;
    }
}
