<?php
require('fpdf/fpdf.php');

class UserManualPDF extends FPDF
{
    function Header()
    {
        // Logo - Comentado para evitar erro FATAL (extensão GD/GIF ausente)
        /*
        if (file_exists('assets/img/1.gif')) {
            $this->Image('assets/img/1.gif', 10, 6, 15);
        }
        */
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(44, 64, 74);
        $this->Cell(0, 10, $this->utf8_to_iso('Asset MGT - Manual do Usuário'), 0, 1, 'C');
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, $this->utf8_to_iso('Guia Oficial de Operação do Sistema'), 0, 1, 'C');
        $this->Ln(10);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, $this->utf8_to_iso('Página ') . $this->PageNo() . ' | Asset MGT Internal Docs', 0, 0, 'C');
    }

    function SectionTitle($title)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(240, 240, 240);
        $this->SetTextColor(44, 64, 74);
        $this->Cell(0, 10, $this->utf8_to_iso($title), 0, 1, 'L', true);
        $this->Ln(4);
    }

    function SectionBody($body)
    {
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(50, 50, 50);
        $this->MultiCell(0, 6, $this->utf8_to_iso($body));
        $this->Ln(8);
    }

    function utf8_to_iso($text)
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
    }
}

$pdf = new UserManualPDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle('Manual do Usuário - Asset MGT');

// 1. Introdução
$pdf->SectionTitle('1. Introdução');
$pdf->SectionBody('Bem-vindo ao Asset MGT. Este manual tem como objetivo orientar você na utilização de todas as funcionalidades da nossa plataforma de gestão de ativos e chamados técnicos. O sistema foi projetado para ser intuitivo, centralizando o controle de inventário e otimizando o fluxo de manutenção.');

// 2. Gestão de Equipamentos
$pdf->SectionTitle('2. Gestão de Equipamentos');
$pdf->SectionBody('No menu "Inventário de Ativos", você pode visualizar todos os equipamentos cadastrados na empresa. Cada item possui uma "Tag" única para identificação física. Você pode filtrar a busca por setor, responsável ou status atual. Para cadastrar um novo item, utilize o botão "Novo Equipamento" e preencha o formulário com o máximo de detalhes possível.');

// 3. Processo de Manutenção
$pdf->SectionTitle('3. Processo de Manutenção');
$pdf->SectionBody('Sempre que um equipamento apresentar falhas, ele deve ser enviado para manutenção através do sistema. Clique no ícone de ferramentas na lista de ativos e descreva o problema. Enquanto estiver em manutenção, o ativo não estará disponível para atribuição. Ao retornar da manutenção, utilize o botão "Liberar" para que o item volte ao inventário disponível.');

// 4. Central de Chamados
$pdf->SectionTitle('4. Central de Chamados');
$pdf->SectionBody('A Central de Chamados é o seu canal direto com o suporte técnico. Você deve abrir um chamado para qualquer necessidade de suporte, como instalação de software, problemas de rede ou troca de periféricos. Acompanhe o status (Aberto, Em Andamento, Resolvido) e interaja com os técnicos através das notas internas de cada chamado.');

// 5. Inteligência Artificial (Assistente IA)');
$pdf->SectionTitle('5. Assistente IA');
$pdf->SectionBody('Nosso sistema conta com um Assistente IA integrado. Ele pode ajudar você a encontrar informações rápidas sobre ativos, gerar resumos de manutenção ou até mesmo sugerir soluções para chamados técnicos comuns. Utilize o menu "Assistente IA" para interagir com o bot e extrair o máximo do banco de dados.');

// 6. Relatórios
$pdf->SectionTitle('6. Relatórios');
$pdf->SectionBody('O módulo de "Relatórios Internos" permite a geração de documentos em PDF contendo estatísticas de uso, custos por setor, histórico de intervenções e muito mais. Esses relatórios são essenciais para auditorias e planejamento de upgrades de hardware.');

$pdf->Output('I', 'Manual_Usuario_AssetMGT.pdf');
?>