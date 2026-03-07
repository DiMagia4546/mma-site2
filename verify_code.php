<?php
session_start();
include "db.php";
include "security.php";
include "mailer.php";
include "auth_verification.php";

ensure_auth_verification_schema($conn);

$flow = $_GET['flow'] ?? '';
if (!in_array($flow, ['register', 'login'], true)) {
    $flow = '';
}

$error = '';
$success = '';
$devVisibleCode = '';
$expiresAtUnix = 0;
$resendWaitSeconds = 0;

function load_user_for_verification(mysqli $conn, int $userId): ?array
{
    $stmt = $conn->prepare("SELECT id, name, email, role, profile_pic, email_verified FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $user ?: null;
}

function load_pending_registration_for_verification(mysqli $conn, int $pendingId): ?array
{
    $pending = get_pending_registration_by_id($conn, $pendingId);
    if (!$pending) {
        return null;
    }
    return [
        'id' => (int) $pending['id'],
        'name' => $pending['name'] ?? '',
        'email' => $pending['email'] ?? '',
    ];
}

if ($flow === 'register') {
    $pendingId = (int) ($_SESSION['pending_register_id'] ?? 0);
    if ($pendingId <= 0) {
        header("Location: register.php");
        exit;
    }
    $pendingUser = load_pending_registration_for_verification($conn, $pendingId);
    if (!$pendingUser) {
        unset($_SESSION['pending_register_id'], $_SESSION['pending_verify_email'], $_SESSION['pending_verify_name']);
        header("Location: register.php");
        exit;
    }
} elseif ($flow === 'login') {
    $pending = $_SESSION['pending_login_user'] ?? null;
    if (!is_array($pending) || empty($pending['id'])) {
        header("Location: login.php");
        exit;
    }
    $pendingId = (int) $pending['id'];
    $pendingUser = load_user_for_verification($conn, $pendingId);
    if (!$pendingUser) {
        unset($_SESSION['pending_login_user']);
        header("Location: login.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf_or_die();
    $action = $_POST['action'] ?? 'verify';

    if ($action === 'resend') {
        $purpose = $flow === 'register' ? 'register_pending' : 'login';
        $canResend = can_resend_auth_code($conn, (int) $pendingUser['id'], $purpose, 30);
        if (!($canResend['allowed'] ?? false)) {
            $resendWaitSeconds = (int) ($canResend['wait'] ?? 0);
            $error = "Aguarda {$resendWaitSeconds}s para reenviar novo codigo.";
        } else {
            $context = $flow === 'register' ? 'register' : 'login';
            $code = create_auth_code($conn, (int) $pendingUser['id'], $purpose, 600);
            if (send_auth_code_email($pendingUser['name'] ?? '', $pendingUser['email'] ?? '', $code, $context)) {
                $success = "Codigo reenviado para o email.";
            } else {
                $error = "Nao foi possivel reenviar o codigo agora. Tenta novamente.";
            }
        }
    } else {
        $code = preg_replace('/\D+/', '', (string) ($_POST['code'] ?? ''));
        if (strlen($code) !== 6) {
            $error = "Codigo invalido.";
        } else {
            $purpose = $flow === 'register' ? 'register_pending' : 'login';
            $verify = verify_auth_code_detailed($conn, (int) $pendingUser['id'], $purpose, $code);
            if (!($verify['ok'] ?? false)) {
                $reason = (string) ($verify['reason'] ?? 'invalid');
                if ($reason === 'locked') {
                    $error = "Demasiadas tentativas. Reenvia um novo codigo.";
                } elseif ($reason === 'missing') {
                    $error = "Codigo expirado. Reenvia novo codigo.";
                } else {
                    $left = (int) ($verify['attempts_left'] ?? 0);
                    $error = $left > 0
                        ? "Codigo incorreto. Tentativas restantes: {$left}."
                        : "Codigo incorreto. Reenvia novo codigo.";
                }
            } else {
                if ($flow === 'register') {
                    $newUserId = create_user_from_pending_registration($conn, (int) $pendingUser['id']);
                    if (!$newUserId) {
                        $error = "Nao foi possivel finalizar a criacao da conta. Tenta novamente.";
                    } else {
                        send_welcome_email($pendingUser['name'] ?? '', $pendingUser['email'] ?? '');
                        unset($_SESSION['pending_register_id'], $_SESSION['pending_verify_email'], $_SESSION['pending_verify_name']);
                        unset($_SESSION['dev_auth_code_register']);
                        $success = "Email confirmado com sucesso. Conta criada. Ja podes fazer login.";
                    }
                } else {
                    mark_email_verified($conn, (int) $pendingUser['id']);
                    $p = $_SESSION['pending_login_user'] ?? [];
                    $_SESSION['user_id'] = (int) ($p['id'] ?? 0);
                    $_SESSION['role'] = $p['role'] ?? 'user';
                    $_SESSION['user_name'] = $p['name'] ?? '';
                    $_SESSION['user_email'] = $p['email'] ?? '';
                    $_SESSION['user_profile_pic'] = $p['profile_pic'] ?? '';
                    unset($_SESSION['pending_login_user']);
                    unset($_SESSION['dev_auth_code_login']);
                    session_regenerate_id(true);
                    header("Location: index.php");
                    exit;
                }
            }
        }
    }
}

if ($flow === 'register' && !empty($_SESSION['dev_auth_code_register'])) {
    $devVisibleCode = (string) $_SESSION['dev_auth_code_register'];
}
if ($flow === 'login' && !empty($_SESSION['dev_auth_code_login'])) {
    $devVisibleCode = (string) $_SESSION['dev_auth_code_login'];
}

$purpose = $flow === 'register' ? 'register_pending' : 'login';
$latestMeta = get_latest_auth_code_meta($conn, (int) $pendingUser['id'], $purpose);
if ($latestMeta && !empty($latestMeta['expires_at'])) {
    $expiresAtUnix = (int) strtotime((string) $latestMeta['expires_at']);
}
if ($resendWaitSeconds <= 0) {
    $cooldownInfo = can_resend_auth_code($conn, (int) $pendingUser['id'], $purpose, 30);
    if (!($cooldownInfo['allowed'] ?? false)) {
        $resendWaitSeconds = (int) ($cooldownInfo['wait'] ?? 0);
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Codigo | MMA 360</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css">
</head>
<body class="min-h-screen bg-neutral-900 text-neutral-100 flex items-center justify-center px-6">
    <div class="w-full max-w-lg bg-neutral-800 border border-neutral-700 rounded-2xl p-8 shadow-2xl">
        <div class="text-center mb-6">
            <img src="assets/logo-mma360.png.png" class="h-16 mx-auto mb-4" alt="Logo">
            <h1 class="text-4xl text-white">Confirmacao de Codigo</h1>
            <p class="text-neutral-400 mt-2">
                Enviamos um codigo de 6 digitos para
                <span class="text-neutral-200"><?= e($pendingUser['email'] ?? '') ?></span>.
            </p>
        </div>

        <?php if ($error): ?>
            <p class="bg-red-600 text-white p-3 rounded mb-4 text-center"><?= e($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="bg-emerald-600 text-white p-3 rounded mb-4 text-center"><?= e($success) ?></p>
        <?php endif; ?>
        <?php if ($devVisibleCode !== ''): ?>
            <p class="bg-amber-600/90 text-white p-3 rounded mb-4 text-center">
                Modo desenvolvimento ativo: codigo atual <strong><?= e($devVisibleCode) ?></strong>
            </p>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <?= csrf_field(); ?>
            <input type="hidden" name="action" value="verify">
            <div>
                <label class="block text-sm text-neutral-300 mb-1">Codigo</label>
                <input
                    type="text"
                    name="code"
                    inputmode="numeric"
                    pattern="\d{6}"
                    maxlength="6"
                    required
                    class="w-full px-4 py-3 rounded bg-neutral-900 border border-neutral-700 tracking-[0.35em] text-center text-xl"
                    placeholder="000000"
                >
            </div>
            <button class="w-full bg-red-600 py-3 rounded text-white font-semibold hover:bg-red-700 transition">
                Confirmar Codigo
            </button>
        </form>

        <form method="POST" class="mt-3">
            <?= csrf_field(); ?>
            <input type="hidden" name="action" value="resend">
            <button id="resend-btn" class="w-full bg-neutral-700 py-3 rounded text-neutral-100 hover:bg-neutral-600 transition">
                Reenviar Codigo
            </button>
        </form>
        <p id="resend-note" class="text-xs text-neutral-400 mt-2 text-center"></p>
        <p id="expiry-note" class="text-xs text-neutral-400 mt-1 text-center"></p>

        <?php if ($flow === 'register' && $success): ?>
            <a href="login.php" class="block text-center mt-5 text-red-400 hover:text-red-300">Ir para Login</a>
        <?php endif; ?>
    </div>
<script>
(function () {
    const resendBtn = document.getElementById('resend-btn');
    const resendNote = document.getElementById('resend-note');
    const expiryNote = document.getElementById('expiry-note');
    let wait = <?= (int) $resendWaitSeconds ?>;
    const expiryTs = <?= (int) $expiresAtUnix ?>;

    function formatTimer(seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    function tickResend() {
        if (!resendBtn || !resendNote) return;
        if (wait > 0) {
            resendBtn.disabled = true;
            resendBtn.classList.add('opacity-60', 'cursor-not-allowed');
            resendNote.textContent = 'Podes reenviar em ' + wait + 's';
            wait -= 1;
        } else {
            resendBtn.disabled = false;
            resendBtn.classList.remove('opacity-60', 'cursor-not-allowed');
            resendNote.textContent = '';
        }
    }

    function tickExpiry() {
        if (!expiryNote || !expiryTs) return;
        const now = Math.floor(Date.now() / 1000);
        const left = expiryTs - now;
        if (left > 0) {
            expiryNote.textContent = 'Codigo expira em ' + formatTimer(left);
        } else {
            expiryNote.textContent = 'Codigo expirado. Reenvia um novo codigo.';
        }
    }

    tickResend();
    tickExpiry();
    setInterval(function () {
        tickResend();
        tickExpiry();
    }, 1000);
})();
</script>
</body>
</html>
