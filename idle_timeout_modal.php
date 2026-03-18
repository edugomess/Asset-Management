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
            <div class="modal-header border-0 pt-4 pb-0 justify-content-center">
                <div style="width: 70px; height: 70px; background: rgba(246, 194, 62, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-hourglass-half text-warning fa-2x animate__animated animate__pulse animate__infinite"></i>
                </div>
            </div>
            <div class="modal-body text-center py-4 px-5">
                <h4 class="font-weight-bold text-dark mb-3">Sessão Expirando</h4>
                <p class="text-muted mb-4">Você está inativo. Por segurança, sua sessão será encerrada em breve.</p>
                
                <div class="position-relative d-inline-block mb-4">
                    <svg width="100" height="100" viewbox="0 0 100 100">
                        <circle cx="50" cy="50" r="45" fill="none" stroke="#f1f1f1" stroke-width="8" />
                        <circle id="idleProgressCircle" cx="50" cy="50" r="45" fill="none" stroke="#e74a3b" stroke-width="8" 
                            stroke-dasharray="283" stroke-dashoffset="0" stroke-linecap="round" transform="rotate(-90 50 50)" style="transition: stroke-dashoffset 1s linear;" />
                    </svg>
                    <div class="position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        <span class="h2 font-weight-bold text-danger mb-0" id="idleCountdown"><?php echo $warning_time; ?></span>
                    </div>
                </div>

                <p class="text-secondary small mb-0">Deseja continuar seu trabalho?</p>
            </div>
            <div class="modal-footer border-0 pb-5 justify-content-center px-5">
                <button type="button" class="btn btn-primary btn-block py-3 shadow-sm" id="stayConnectedBtn"
                    style="background: linear-gradient(135deg, #2c404a 0%, #1a2930 100%); border: none; border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-check-circle mr-2"></i>SIM, CONTINUAR CONECTADO
                </button>
                <a href="logout.php?timeout=true" class="btn btn-link text-muted mt-2 small text-decoration-none">Sair agora</a>
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