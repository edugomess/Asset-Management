<?php
include 'auth.php';
include 'conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cancelar') {
    $id_chamado = intval($_POST['id']);
    $user_id = $_SESSION['id_usuarios'];

    // Validar se o chamado pertence ao usuário e está em um status cancelável
    // Admin também pode cancelar por este endpoint simplificado
    $sql_check = "SELECT status, usuario_id, nota_resolucao FROM chamados WHERE id = $id_chamado";
    $res_check = $conn->query($sql_check);
    
    if ($res_check->num_rows > 0) {
        $row = $res_check->fetch_assoc();
        
        // Verificação de permissão: Autor ou Admin
        if ($row['usuario_id'] != $user_id && $_SESSION['nivelUsuario'] !== 'Admin') {
            echo json_encode(['success' => false, 'message' => __('Permissão negada.')]);
            exit;
        }
        
        // Verificação de status: Apenas 'Aberto' ou 'Pendente' podem ser cancelados pelo solicitante
        if (!in_array($row['status'], ['Aberto', 'Pendente']) && $_SESSION['nivelUsuario'] !== 'Admin') {
            echo json_encode(['success' => false, 'message' => __('Este chamado já está em atendimento e não pode ser cancelado.')]);
            exit;
        }
        
        // Preparar nota de cancelamento para o histórico
        $notas = [];
        if (!empty($row['nota_resolucao'])) {
            $dec = json_decode($row['nota_resolucao'], true);
            if (is_array($dec)) {
                $notas = $dec;
            } else {
                // Se for nota legada em texto simples
                $notas[] = ['texto' => $row['nota_resolucao'], 'data' => date('d/m/Y H:i'), 'usuario' => 'Sistema'];
            }
        }
        
        $notas[] = [
            'texto' => __('Chamado cancelado pelo solicitante.'),
            'data' => date('d/m/Y H:i'),
            'usuario' => $_SESSION['nome_usuario'] ?? 'Sistema'
        ];
        
        $nota_resolucao = mysqli_real_escape_string($conn, json_encode($notas, JSON_UNESCAPED_UNICODE));
        
        // Executar atualização
        $sql_update = "UPDATE chamados SET status = 'Cancelado', data_fechamento = NOW(), nota_resolucao = '$nota_resolucao' WHERE id = $id_chamado";
        
        if ($conn->query($sql_update)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => __('Erro ao atualizar banco: ') . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => __('Chamado não encontrado.')]);
    }
} else {
    echo json_encode(['success' => false, 'message' => __('Requisição inválida.')]);
}
