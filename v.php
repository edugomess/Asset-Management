<?php
/**
 * DIGITAL TWIN VIEW: v.php
 * Visualização pública simplificada e profissional de ativos via QR Code.
 */
include_once 'conexao.php';
include_once 'language.php'; // Para a função __()

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Ativo não encontrado.");
}

// Buscar detalhes do ativo com joins para Usuário e Local de Instalação
$sql_ativo = "SELECT a.*, u.nome AS user_nome, u.sobrenome AS user_sobrenome, l.nome_local 
             FROM ativos a 
             LEFT JOIN usuarios u ON a.assigned_to = u.id_usuarios
             LEFT JOIN locais l ON a.id_local = l.id_local
             WHERE a.id_asset = '$id'";
$result_ativo = mysqli_query($conn, $sql_ativo);
$ativo = mysqli_fetch_assoc($result_ativo);

// Função para buscar o path do local de forma recursiva
function getLocalPath($conn, $id_local) {
    if (!$id_local) return '';
    $sql = "SELECT id_local, nome_local, id_parent_local FROM locais WHERE id_local = $id_local";
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        $parent = $row['id_parent_local'] ? getLocalPath($conn, $row['id_parent_local']) . ' > ' : '';
        return $parent . $row['nome_local'];
    }
    return '';
}

if (!$ativo) {
    echo '<style>body { background: #f0f2f5; font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; color: #666; }</style>';
    echo '<div><h2 style="margin-bottom: 5px;">Ops!</h2><p>Este ativo não foi encontrado em nossa base de dados.</p></div>';
    exit();
}

// UI Helpers
$status_class = ($ativo['status'] === 'Ativo') ? 'success' : (($ativo['status'] === 'Manutencao' || $ativo['status'] === 'Manutenção') ? 'warning' : 'danger');
$foto = !empty($ativo['imagem']) ? htmlspecialchars($ativo['imagem']) : '/assets/img/no-image.png';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($ativo['modelo']); ?> - Digital Twin</title>
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="/assets/css/Nunito.css">
    <style>
        body { background: #f0f2f5; font-family: 'Nunito', sans-serif; padding: env(safe-area-inset-top) 15px env(safe-area-inset-bottom); min-height: 100vh; display: flex; flex-direction: column; }
        .twin-card { background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border: none; max-width: 450px; margin: 20px auto; width: 100%; animation: slideUp 0.6s ease-out; }
        .twin-header { background: linear-gradient(135deg, #2c404a 0%, #3e5b69 100%); color: white; padding: 40px 20px; text-align: center; position: relative; }
        .twin-img-container { width: 130px; height: 130px; margin: 0 auto 20px; border: 5px solid rgba(255,255,255,0.25); border-radius: 20px; overflow: hidden; background: white; box-shadow: 0 8px 20px rgba(0,0,0,0.2); }
        .twin-img { width: 100%; height: 100%; object-fit: cover; }
        .status-badge { display: inline-block; padding: 6px 18px; border-radius: 50px; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; margin-top: 15px; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(0,0,0,0.15); }
        .detail-list { padding: 10px 0; }
        .detail-row { display: flex; align-items: center; padding: 16px 25px; border-bottom: 1px solid #f8f9fc; transition: background 0.2s; }
        .detail-row:last-child { border-bottom: none; }
        .detail-icon { width: 44px; height: 44px; background: #f4f6f9; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #2c404a; margin-right: 18px; font-size: 1.1rem; }
        .detail-info { flex: 1; }
        .detail-label { font-size: 0.65rem; color: #adb5bd; text-transform: uppercase; font-weight: 900; margin-bottom: 2px; }
        .detail-value { font-size: 1rem; color: #2c404a; font-weight: 700; line-height: 1.2; }
        .footer { text-align: center; margin-top: auto; padding: 30px 0; opacity: 0.6; font-size: 0.75rem; color: #6c757d; font-weight: 600; }
        @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body>
    <div class="twin-card">
        <div class="twin-header">
            <div class="twin-img-container">
                <img src="<?php echo $foto; ?>" class="twin-img" alt="Asset Image">
            </div>
            <h4 class="font-weight-bold mb-1"><?php echo htmlspecialchars($ativo['modelo']); ?></h4>
            <div class="opacity-75 small font-weight-bold"><?php echo htmlspecialchars($ativo['tag']); ?></div>
            <div class="status-badge bg-<?php echo $status_class; ?>">
                <?php echo __(ucfirst($ativo['status'])); ?>
            </div>
        </div>
        
        <?php if ($ativo['status'] == 'Manutencao' || $ativo['status'] == 'Manutenção' || $ativo['status'] == 'Em manutenção'): ?>
        <div style="background: #ffc107; color: #000; padding: 15px 25px; border-radius: 0 0 12px 12px; margin-bottom: 0; text-align: center; font-weight: bold; border-top: 2px solid #000; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            <i class="fas fa-tools mr-2"></i> <?php echo __('ESTE ATIVO ESTÁ EM MANUTENÇÃO'); ?>
        </div>
        <?php endif; ?>

        <div class="detail-list">
            <div class="detail-row">
                <div class="detail-icon"><i class="fas fa-fingerprint"></i></div>
                <div class="detail-info">
                    <div class="detail-label"><?php echo __('Identificação'); ?></div>
                    <div class="detail-value"><?php echo htmlspecialchars($ativo['tag']); ?></div>
                </div>
            </div>

            <?php if(!empty($ativo['fabricante'])): ?>
            <div class="detail-row">
                <div class="detail-icon"><i class="fas fa-industry"></i></div>
                <div class="detail-info">
                    <div class="detail-label"><?php echo __('Fabricante'); ?></div>
                    <div class="detail-value"><?php echo htmlspecialchars($ativo['fabricante']); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(!empty($ativo['id_local'])): ?>
            <div class="detail-row">
                <div class="detail-icon" style="color: #e74a3b;"><i class="fas fa-map-marker-alt"></i></div>
                <div class="detail-info">
                    <div class="detail-label"><?php echo __('Endereço Completo'); ?></div>
                    <div class="detail-value text-danger" style="font-size: 0.95rem;">
                        <?php echo htmlspecialchars(getLocalPath($conn, $ativo['id_local'])); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(!empty($ativo['hostName'])): ?>
            <div class="detail-row">
                <div class="detail-icon"><i class="fas fa-network-wired"></i></div>
                <div class="detail-info">
                    <div class="detail-label">Hostname / Rede</div>
                    <div class="detail-value"><?php echo htmlspecialchars($ativo['hostName']); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <?php 
            $computational_cats = ['Notebook', 'Desktop', 'Servidores', 'Workstation'];
            if (in_array($ativo['categoria'], $computational_cats)): 
            ?>
            <div class="detail-row">
                <div class="detail-icon"><i class="fas fa-microchip"></i></div>
                <div class="detail-info">
                    <div class="detail-label"><?php echo __('Hardware / Specs'); ?></div>
                    <div class="detail-value">
                        <?php 
                        $specs = [];
                        if(!empty($ativo['processador'])) $specs[] = $ativo['processador'];
                        if(!empty($ativo['memoria'])) $specs[] = $ativo['memoria'];
                        if(!empty($ativo['armazenamento'])) $specs[] = $ativo['armazenamento'] . " " . ($ativo['tipo_armazenamento'] ?? '');
                        echo !empty($specs) ? implode(" • ", $specs) : "-";
                        ?>
                    </div>
                </div>
            </div>

            <?php if(!empty($ativo['gpu'])): ?>
            <div class="detail-row">
                <div class="detail-icon" style="color: #4e73df;"><i class="fas fa-video"></i></div>
                <div class="detail-info">
                    <div class="detail-label"><?php echo __('Placa Gráfica (GPU)'); ?></div>
                    <div class="detail-value text-primary font-weight-bold"><?php echo htmlspecialchars($ativo['gpu']); ?></div>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <?php if($ativo['categoria'] == 'Monitor' && !empty($ativo['polegadas'])): ?>
            <div class="detail-row">
                <div class="detail-icon" style="color: #e74a3b;"><i class="fas fa-desktop"></i></div>
                <div class="detail-info">
                    <div class="detail-label"><?php echo __('Polegadas'); ?></div>
                    <div class="detail-value text-danger font-weight-bold"><?php echo htmlspecialchars($ativo['polegadas']); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if($ativo['categoria'] == 'Impressora' && !empty($ativo['is_scanner'])): ?>
            <div class="detail-row">
                <div class="detail-icon" style="color: #1cc88a;"><i class="fas fa-print"></i></div>
                <div class="detail-info">
                    <div class="detail-label"><?php echo __('Funcionalidade'); ?></div>
                    <div class="detail-value text-success font-weight-bold"><?php echo ($ativo['is_scanner'] == 'Sim') ? __('Multifuncional (C/ Scanner)') : __('Impressora'); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(empty($ativo['id_local'])): ?>
            <div class="detail-row">
                <div class="detail-icon"><i class="fas fa-user-check"></i></div>
                <div class="detail-info">
                    <div class="detail-label"><?php echo __('Responsável'); ?></div>
                    <div class="detail-value text-primary font-weight-bold">
                        <?php echo $ativo['assigned_to'] ? htmlspecialchars($ativo['user_nome'] . ' ' . $ativo['user_sobrenome']) : '<span class="text-muted">' . __('Livre / Disponível') . '</span>'; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="detail-row">
                <div class="detail-icon"><i class="fas fa-layer-group"></i></div>
                <div class="detail-info">
                    <div class="detail-label"><?php echo __('Nível de Atribuição'); ?></div>
                    <div class="detail-value text-primary">
                        <?php 
                        if (!empty($ativo['tier'])) {
                            echo '<span class="badge badge-dark" style="background: #2c404a; font-size: 0.8rem; margin-bottom: 5px;">' . htmlspecialchars($ativo['tier']) . '</span><br>';
                        }
                        echo htmlspecialchars($ativo['setor'] ?: '-');
                        ?>
                    </div>
                </div>
            </div>

            <?php if(!empty($ativo['centroDeCusto']) && $ativo['centroDeCusto'] !== 'Nenhum'): ?>
            <div class="detail-row">
                <div class="detail-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div class="detail-info">
                    <div class="detail-label"><?php echo __('Centro de Custo'); ?></div>
                    <div class="detail-value"><?php echo htmlspecialchars($ativo['centroDeCusto']); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <div class="detail-row">
                <div class="detail-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="detail-info">
                    <div class="detail-label"><?php echo __('Ativação'); ?></div>
                    <div class="detail-value">
                        <?php 
                        $dt = $ativo['dataAtivacao'];
                        echo ($dt && $dt !== '0000-00-00' && $dt !== '1970-01-01') ? date('d/m/Y', strtotime($dt)) : '<span class="text-muted">' . __('Não informado') . '</span>'; 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <i class="fas fa-copyright mr-1"></i> Asset Management System • Digital Twin Interface
    </div>

    <!-- Micro-interações -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Opções futuras como haptic feedback ou animações extras
        });
    </script>
</body>
</html>
