<?php
// Busca o tempo de logout configurado (Geral, Admin ou Suporte)
$dynamic_idle_timeout = 600; // Padrão 10 min
if (file_exists('conexao.php')) {
    include_once 'conexao.php';
    $res_config = $conn->query("SELECT idle_timeout_minutos, idle_timeout_admin, idle_timeout_suporte FROM configuracoes_alertas LIMIT 1");
    if ($alert_config = $res_config->fetch_assoc()) {
        $nivel = $_SESSION['nivelUsuario'] ?? 'Usuário';
        $timeout_min = $alert_config['idle_timeout_minutos'] ?? 10;

        if ($nivel === 'Admin' && isset($alert_config['idle_timeout_admin'])) {
            $timeout_min = $alert_config['idle_timeout_admin'];
        } elseif ($nivel === 'Suporte' && isset($alert_config['idle_timeout_suporte'])) {
            $timeout_min = $alert_config['idle_timeout_suporte'];
        }

        $dynamic_idle_timeout = (int) $timeout_min * 60;
    }
}
$warning_time = 30; // 30 segundos de aviso
$idle_limit = $dynamic_idle_timeout - $warning_time;
if ($idle_limit < 0)
    $idle_limit = 0;
?>

<!-- Modal de Alerta de Inatividade -->
<div class="modal fade" id="idleTimeoutModal" tabindex="-1" role="dialog" aria-labelledby="idleTimeoutModalLabel"
    aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="idleTimeoutModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Sessão Expirando
                </h5>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-2">Você está inativo há algum tempo.</p>
                <p class="h4 font-weight-bold ml-1">Sua sessão será encerrada em:</p>
                <div class="display-4 font-weight-bold text-danger my-3" id="idleCountdown">
                    <?php echo $warning_time; ?>
                </div>
                <p class="text-muted">Deseja continuar conectado?</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pb-4">
                <button type="button" class="btn btn-primary px-4 py-2" id="stayConnectedBtn"
                    style="background: rgb(44,64,74); border-radius: 10px;">
                    <i class="fas fa-check mr-2"></i>Permanecer Conectado
                </button>
                <a href="logout.php?timeout=true" class="btn btn-link text-muted">Sair agora</a>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        let idleTime = <?php echo $idle_limit; ?>; // Tempo de inatividade antes do aviso
        let warningTime = <?php echo $warning_time; ?>; // 30 segundos de aviso
        let idleCounter = 0;
        let warningCounter = warningTime;
        let isWarningActive = false;
        let countdownInterval;
        let idleInterval;

        function resetIdleTimer() {
            if (!isWarningActive) {
                idleCounter = 0;
            }
        }

        // Eventos que resetam o timer de inatividade
        window.addEventListener('load', resetIdleTimer);
        window.addEventListener('mousemove', resetIdleTimer);
        window.addEventListener('mousedown', resetIdleTimer);
        window.addEventListener('touchstart', resetIdleTimer);
        window.addEventListener('click', resetIdleTimer);
        window.addEventListener('keypress', resetIdleTimer);

        function startIdleDetection() {
            idleInterval = setInterval(function () {
                idleCounter++;
                if (idleCounter >= idleTime && !isWarningActive) {
                    showWarning();
                }
            }, 1000);
        }

        function showWarning() {
            // Verifica se jQuery e Bootstrap Modal estão carregados
            if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
                isWarningActive = true;
                warningCounter = warningTime;
                const countdownEl = document.getElementById('idleCountdown');
                if (countdownEl) countdownEl.innerText = warningCounter;

                $('#idleTimeoutModal').modal('show');

                countdownInterval = setInterval(function () {
                    warningCounter--;
                    if (countdownEl) countdownEl.innerText = warningCounter;

                    if (warningCounter <= 0) {
                        clearInterval(countdownInterval);
                        window.location.href = 'logout.php?timeout=true';
                    }
                }, 1000);
            } else {
                // Fallback caso o modal não possa ser exibido (ex: erro de carregamento de scripts)
                if (idleCounter >= idleTime + warningTime) {
                    window.location.href = 'logout.php?timeout=true';
                }
            }
        }

        function stayConnected() {
            // Ping do servidor para renovar o $_SESSION['last_activity']
            fetch('auth_ping.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        isWarningActive = false;
                        idleCounter = 0;
                        clearInterval(countdownInterval);
                        if (typeof $ !== 'undefined') {
                            $('#idleTimeoutModal').modal('hide');
                        }
                    }
                })
                .catch(err => {
                    console.error('Erro ao renovar sessão:', err);
                    // Mesmo com erro de rede, resetamos localmente para dar outra chance
                    isWarningActive = false;
                    idleCounter = 0;
                    clearInterval(countdownInterval);
                    if (typeof $ !== 'undefined') {
                        $('#idleTimeoutModal').modal('hide');
                    }
                });
        }

        // Inicialização segura após o carregamento do DOM
        function init() {
            const stayBtn = document.getElementById('stayConnectedBtn');
            if (stayBtn) {
                stayBtn.addEventListener('click', stayConnected);
            }
            startIdleDetection();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>