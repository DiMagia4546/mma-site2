<?php
session_start();
include "db.php";
include "security.php";
include "navbar.php";
include "mailer.php";
include "auth_verification.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

ensure_auth_verification_schema($conn);

$success = "";
$error = "";
$pendingPasswordChange = $_SESSION['pending_password_change'] ?? null;
$hasPendingPasswordChange = is_array($pendingPasswordChange)
    && (int) ($pendingPasswordChange['user_id'] ?? 0) === $user_id
    && !empty($pendingPasswordChange['password_hash']);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf_or_die();
    $action = $_POST['action'] ?? 'request_code';

    if ($action === 'request_code') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password'])) {
            $error = "A password atual está incorreta.";
        } elseif ($new !== $confirm) {
            $error = "As passwords novas não coincidem.";
        } elseif (strlen($new) < 8) {
            $error = "A nova password deve ter pelo menos 8 caracteres.";
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $code = create_auth_code($conn, $user_id, 'change_password', 600);
            if (!send_auth_code_email($user['name'] ?? '', $user['email'] ?? '', $code, 'login')) {
                $error = "Nao foi possivel enviar o codigo para o teu email. Tenta novamente.";
            } else {
                $_SESSION['pending_password_change'] = [
                    'user_id' => $user_id,
                    'password_hash' => $hashed,
                    'requested_at' => time(),
                ];
                $hasPendingPasswordChange = true;
                $success = "Enviamos um codigo para o teu email. Introduz o codigo para confirmar a alteracao.";
            }
        }
    } elseif ($action === 'confirm_code') {
        $pending = $_SESSION['pending_password_change'] ?? null;
        if (!is_array($pending) || (int) ($pending['user_id'] ?? 0) !== $user_id || empty($pending['password_hash'])) {
            $error = "Nao existe pedido de alteracao pendente. Inicia novamente.";
            $hasPendingPasswordChange = false;
        } else {
            $code = preg_replace('/\D+/', '', (string) ($_POST['code'] ?? ''));
            if (strlen($code) !== 6) {
                $error = "Codigo invalido.";
            } else {
                $verify = verify_auth_code_detailed($conn, $user_id, 'change_password', $code);
                if (!($verify['ok'] ?? false)) {
                    $reason = (string) ($verify['reason'] ?? 'invalid');
                    if ($reason === 'locked') {
                        $error = "Demasiadas tentativas. Pede novo codigo.";
                    } elseif ($reason === 'missing') {
                        $error = "Codigo expirado. Pede novo codigo.";
                    } else {
                        $left = (int) ($verify['attempts_left'] ?? 0);
                        $error = $left > 0 ? "Codigo incorreto. Tentativas restantes: {$left}." : "Codigo incorreto.";
                    }
                } else {
                    $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                    $update->bind_param("si", $pending['password_hash'], $user_id);
                    if ($update->execute()) {
                        unset($_SESSION['pending_password_change']);
                        $hasPendingPasswordChange = false;
                        $success = "Password alterada com sucesso!";
                    } else {
                        $error = "Erro ao atualizar password.";
                    }
                    $update->close();
                }
            }
        }
    } elseif ($action === 'resend_code') {
        $pending = $_SESSION['pending_password_change'] ?? null;
        if (!is_array($pending) || (int) ($pending['user_id'] ?? 0) !== $user_id || empty($pending['password_hash'])) {
            $error = "Nao existe pedido pendente. Inicia novamente.";
            $hasPendingPasswordChange = false;
        } else {
            $canResend = can_resend_auth_code($conn, $user_id, 'change_password', 30);
            if (!($canResend['allowed'] ?? false)) {
                $wait = (int) ($canResend['wait'] ?? 0);
                $error = "Aguarda {$wait}s para reenviar novo codigo.";
            } else {
                $code = create_auth_code($conn, $user_id, 'change_password', 600);
                if (send_auth_code_email($user['name'] ?? '', $user['email'] ?? '', $code, 'login')) {
                    $success = "Codigo reenviado para o teu email.";
                } else {
                    $error = "Nao foi possivel reenviar o codigo.";
                }
            }
        }
    } elseif ($action === 'cancel') {
        unset($_SESSION['pending_password_change']);
        $hasPendingPasswordChange = false;
        $success = "Pedido de alteracao cancelado.";
    } else {
        $error = "Acao invalida.";
    }

    $pendingPasswordChange = $_SESSION['pending_password_change'] ?? null;
    if (!$hasPendingPasswordChange) {
        $hasPendingPasswordChange = is_array($pendingPasswordChange)
            && (int) ($pendingPasswordChange['user_id'] ?? 0) === $user_id
            && !empty($pendingPasswordChange['password_hash']);
    }
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Password | MMA 360</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Teko', sans-serif; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css"></head>

<body class="bg-neutral-900 text-neutral-100">
<?php render_main_nav(); ?>

<div class="pt-28"></div>

<section class="max-w-3xl mx-auto px-6">
    <h1 class="text-6xl font-bold text-red-500 tracking-widest mb-10">ALTERAR PASSWORD</h1>

    <?php if (!empty($success)): ?><p class="bg-green-600 text-white px-4 py-2 rounded mb-6"><?= e($success) ?></p><?php endif; ?>
    <?php if (!empty($error)): ?><p class="bg-red-600 text-white px-4 py-2 rounded mb-6"><?= e($error) ?></p><?php endif; ?>

    <?php if (!$hasPendingPasswordChange): ?>
    <form method="POST" class="bg-neutral-800 border border-neutral-700 p-8 rounded-xl shadow-xl">
        <?= csrf_field(); ?>
        <input type="hidden" name="action" value="request_code">

        <label class="block text-neutral-300 mb-1">Password Atual</label>
        <input type="password" name="current_password" required class="w-full bg-neutral-900 border border-neutral-700 rounded px-4 py-2 mb-6 text-neutral-100">

        <label class="block text-neutral-300 mb-1">Nova Password</label>
        <input type="password" name="new_password" required class="w-full bg-neutral-900 border border-neutral-700 rounded px-4 py-2 mb-6 text-neutral-100">

        <label class="block text-neutral-300 mb-1">Confirmar Nova Password</label>
        <input type="password" name="confirm_password" required class="w-full bg-neutral-900 border border-neutral-700 rounded px-4 py-2 mb-6 text-neutral-100">

        <button class="bg-red-600 px-6 py-3 rounded-lg hover:bg-red-700 transition text-white text-lg">Enviar Código de Confirmação</button>
    </form>
    <?php else: ?>
    <div class="bg-neutral-800 border border-neutral-700 p-8 rounded-xl shadow-xl">
        <p class="text-neutral-300 mb-6">Enviamos um codigo de 6 digitos para <span class="text-neutral-100"><?= e($user['email'] ?? '') ?></span>. Confirma para concluir a alteracao.</p>

        <form method="POST" class="space-y-4">
            <?= csrf_field(); ?>
            <input type="hidden" name="action" value="confirm_code">
            <label class="block text-neutral-300 mb-1">Codigo</label>
            <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6" required class="w-full bg-neutral-900 border border-neutral-700 rounded px-4 py-2 text-neutral-100 tracking-[0.35em] text-center text-xl">
            <button class="bg-red-600 px-6 py-3 rounded-lg hover:bg-red-700 transition text-white text-lg">Confirmar Alteração</button>
        </form>

        <div class="mt-4 flex gap-3">
            <form method="POST">
                <?= csrf_field(); ?>
                <input type="hidden" name="action" value="resend_code">
                <button class="bg-neutral-700 px-4 py-2 rounded hover:bg-neutral-600 transition">Reenviar Código</button>
            </form>
            <form method="POST">
                <?= csrf_field(); ?>
                <input type="hidden" name="action" value="cancel">
                <button class="bg-neutral-800 border border-neutral-600 px-4 py-2 rounded hover:bg-neutral-700 transition">Cancelar</button>
            </form>
        }
    }
}
    </div>
    <?php endif; ?>

    <div class="mt-10">
        <a href="dashboard.php" class="text-red-500 hover:text-red-400 text-sm uppercase tracking-[0.25em]">Voltar ao Painel</a>
    </div>
</section>

</body>
</html>





