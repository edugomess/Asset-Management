<?php
// Busca o tempo de logout configurado (Geral, Admin ou Suporte)
$dynamic_idle_timeout = 600; // Padrão 10 min
if (file_exists('conexao.php')) {
    include_once 'conexao.php';
    $res_config = $conn->query("SELECT idle_timeout_minutos, idle_timeout_admin, idle_timeout_suporte FROM configuracoes_alertas LIMIT 1");
    if ($idle_config = $res_config->fetch_assoc()) {
        $nivel = $_SESSION['nivelUsuario'] ?? 'Usuário';
        $timeout_min = $idle_config['idle_timeout_minutos'] ?? 10;

        if ($nivel === 'Admin' && isset($idle_config['idle_timeout_admin'])) {
            $timeout_min = $idle_config['idle_timeout_admin'];
        } elseif ($nivel === 'Suporte' && isset($idle_config['idle_timeout_suporte'])) {
            $timeout_min = $idle_config['idle_timeout_suporte'];
        }

        $dynamic_idle_timeout = (int) $timeout_min * 60;
    }
}

// TEST MODE: Se você quiser testar rápido, mude para true
$test_mode = false; 
if ($test_mode) {
    $dynamic_idle_timeout = 30; // 30 segundos total para teste
}

$warning_time = 60; // 60 segundos de aviso (Padrão para Produção)
$idle_limit = $dynamic_idle_timeout - $warning_time;
if ($idle_limit < 0)
    $idle_limit = 0;
?>

<!-- Modal de Alerta de Inatividade -->
<div class="modal fade" id="idleTimeoutModal" tabindex="-1" role="dialog" aria-labelledby="idleTimeoutModalLabel"
    aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 p-4" style="background: #2c404a; color: white;">
                <div class="d-flex align-items-center">
                    <div class="bg-white rounded-circle p-2 mr-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-hourglass-half text-warning animate__animated animate__pulse animate__infinite"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0" id="idleTimeoutModalLabel"><?php echo __('Sessão Expirando'); ?></h5>
                        <small class="text-white-50"><?php echo __('Você está inativo há algum tempo.'); ?></small>
                    </div>
                </div>
            </div>
            <div class="modal-body text-center p-4 bg-light">
                <p class="text-muted mb-4"><?php echo __('Por segurança, sua sessão será encerrada automaticamente.'); ?></p>
                
                <div class="position-relative d-inline-block mb-4">
                    <svg width="100" height="100" viewbox="0 0 100 100">
                        <circle cx="50" cy="50" r="45" fill="none" stroke="#e0e0e0" stroke-width="8" />
                        <circle id="idleProgressCircle" cx="50" cy="50" r="45" fill="none" stroke="#e74a3b" stroke-width="8" 
                            stroke-dasharray="283" stroke-dashoffset="0" stroke-linecap="round" transform="rotate(-90 50 50)" style="transition: stroke-dashoffset 1s linear;" />
                    </svg>
                    <div class="position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        <span class="h2 font-weight-bold text-danger mb-0" id="idleCountdown"><?php echo $warning_time; ?></span>
                    </div>
                </div>

                <p class="text-secondary small mb-0"><?php echo __('Deseja continuar seu trabalho?'); ?></p>
            </div>
            <div class="modal-footer border-0 p-4 bg-white d-flex flex-column">
                <button type="button" class="btn btn-primary btn-block p-3 font-weight-bold shadow-sm mb-2" id="stayConnectedBtn"
                    style="background: #2c404a; border: none; border-radius: 12px;">
                    <i class="fas fa-check-circle mr-2"></i><?php echo __('SIM, CONTINUAR CONECTADO'); ?>
                </button>
                <a href="logout.php?timeout=true" class="btn btn-link text-muted font-weight-bold btn-block m-0"><?php echo __('Sair agora'); ?></a>
            </div>
        </div>
    </div>
</div>

<style>
    #idleTimeoutModal .modal-content {
        background: #ffffff;
    }
    @keyframes pulse-red {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }
    .text-danger.animate-pulse {
        animation: pulse-red 1s infinite;
    }
</style>

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
            if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
                isWarningActive = true;
                warningCounter = warningTime;
                
                const countdownEl = document.getElementById('idleCountdown');
                const circleEl = document.getElementById('idleProgressCircle');
                
                if (countdownEl) countdownEl.innerText = warningCounter;
                
                $('#idleTimeoutModal').modal('show');

                const totalLength = 283; // 2 * PI * r (r=45)

                countdownInterval = setInterval(function () {
                    warningCounter--;
                    if (countdownEl) {
                        countdownEl.innerText = warningCounter;
                        if (warningCounter <= 5) {
                            countdownEl.classList.add('animate-pulse');
                        }
                    }

                    if (circleEl) {
                        const offset = totalLength - (warningCounter / warningTime) * totalLength;
                        circleEl.style.strokeDashoffset = offset;
                    }

                    if (warningCounter <= 0) {
                        clearInterval(countdownInterval);
                        window.location.href = 'logout.php?timeout=true';
                    }
                }, 1000);
            } else {
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