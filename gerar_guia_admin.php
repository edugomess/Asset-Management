<?php
require('fpdf/fpdf.php');

class AdminGuidePDF extends FPDF
{
    function Header()
    {
        /*
        if (file_exists('assets/img/1.gif')) {
            $this->Image('assets/img/1.gif', 10, 6, 15);
        }
        */
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(44, 64, 74);
        $this->Cell(0, 10, $this->utf8_to_iso('Asset MGT - Guia do Administrador'), 0, 1, 'C');
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, $this->utf8_to_iso('Documentação Técnica e Gestão de Sistema'), 0, 1, 'C');
        $this->Ln(10);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, $this->utf8_to_iso('Página ') . $this->PageNo() . ' | Asset MGT Admin Docs', 0, 0, 'C');
    }

    function SectionTitle($title)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(230, 230, 230);
        $this->SetTextColor(26, 121, 158);
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

$pdf = new AdminGuidePDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle('Guia do Administrador - Asset MGT');

// 1. Visão Geral Administrativa
$pdf->SectionTitle('1. Visão Geral Administrativa');
$pdf->SectionBody('Este guia é destinado exclusivamente aos administradores do sistema Asset MGT. Nele, detalhamos os procedimentos de manutenção do banco de dados, gestão de permissões de usuários e configuração de parâmetros globais de SLA e notificações.');

// 2. Gestão de Usuários e Permissões
$pdf->SectionTitle('2. Gestão de Usuários e Permissões');
$pdf->SectionBody('O controle de acesso é feito através do menu "Controle de Usuários". Administradores podem criar, editar, desativar ou resetar senhas. É fundamental manter os dados de contato (E-mail e WhatsApp) atualizados para o correto funcionamento dos alertas automáticos do sistema.');

// 3. Estrutura de Banco de Dados
$pdf->SectionTitle('3. Estrutura de Banco de Dados');
$pdf->SectionBody('O sistema utiliza MariaDB/MySQL. As tabelas principais são "ativos", "chamados", "usuarios" e "centro_de_custo". Recomenda-se a realização de backups semanais via phpMyAdmin ou scripts cron. Evite exclusões diretas via SQL para manter a integridade referencial dos históricos de manutenção.');

// 4. Configurações de SLA
$pdf->SectionTitle('4. Configurações de SLA');
$pdf->SectionBody('As métricas de atendimento (SLA) podem ser configuradas no menu "Configurações". O administrador define o tempo máximo de resposta para cada nível de criticidade. O sistema calcula automaticamente o tempo de resolução e gera o ranking de eficiência dos técnicos visível na Dashboard.');

// 5. Gestão de Licenças e Softwares
$pdf->SectionTitle('5. Gestão de Licenças e Softwares');
$pdf->SectionBody('O módulo de licenças permite o controle rigoroso de chaves de ativação e subscrições (Office 365, Adobe, etc). O administrador deve registrar a quantidade total de licenças adquiridas; o sistema bloqueará novas atribuições caso o limite seja atingido, garantindo conformidade legal.');

// 6. Manutenção de Sistema
$pdf->SectionTitle('6. Manutenção de Sistema');
$pdf->SectionBody('Para garantir a performance, utilize a ferramenta de "Otimização de Banco de Dados" periodicamente. Verifique também os logs de erro do PHP em caso de comportamentos inesperados. Atualizações de CSS e JS devem ser feitas preferencialmente nos arquivos globais da pasta /assets.');

$pdf->Output('I', 'Guia_Admin_AssetMGT.pdf');
?>