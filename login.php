<?php
session_start();
include "db.php";
include "security.php";
include "mailer.php";
include "auth_verification.php";

$error = "";

ensure_auth_verification_schema($conn);

function dev_show_code_on_email_fail(): bool
{
    if (defined('MMA_DEV_SHOW_CODE_ON_EMAIL_FAIL')) {
        return trim((string) constant('MMA_DEV_SHOW_CODE_ON_EMAIL_FAIL')) === '1';
    }
    return false;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf_or_die();

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $userId = (int) $user['id'];
            $role = $user['role'] ?? 'user';
            $isEmailVerified = (int) ($user['email_verified'] ?? 0) === 1;

            // Admin bypasses email code to avoid lockout when SMTP is unavailable.
            if ($role === 'admin') {
                $_SESSION['user_id'] = $userId;
                $_SESSION['role'] = $role;
                $_SESSION['user_name'] = $user['name'] ?? '';
                $_SESSION['user_email'] = $user['email'] ?? '';
                $_SESSION['user_profile_pic'] = $user['profile_pic'] ?? '';
                session_regenerate_id(true);
                header("Location: index.php");
                exit;
            }

            if (!$isEmailVerified) {
                $verifyCode = create_auth_code($conn, $userId, 'verify_email', 600);
                if (!send_auth_code_email($user['name'] ?? '', $user['email'] ?? '', $verifyCode, 'register')) {
                    $error = "Email ainda nao confirmado. Nao foi possivel reenviar o codigo agora. Confirma o SMTP e tenta novamente.";
                } else {
                    $_SESSION['pending_verify_user_id'] = $userId;
                    $_SESSION['pending_verify_email'] = $user['email'] ?? '';
                    $_SESSION['pending_verify_name'] = $user['name'] ?? '';
                    header("Location: verify_code.php?flow=register");
                    exit;
                }
            }

            $code = create_auth_code($conn, $userId, 'login', 600);
            if (!send_auth_code_email($user['name'] ?? '', $user['email'] ?? '', $code, 'login')) {
                if (dev_show_code_on_email_fail()) {
                    $_SESSION['pending_login_user'] = [
                        'id' => $userId,
                        'role' => $user['role'] ?? 'user',
                        'name' => $user['name'] ?? '',
                        'email' => $user['email'] ?? '',
                        'profile_pic' => $user['profile_pic'] ?? '',
                    ];
                    $_SESSION['dev_auth_code_login'] = $code;
                    header("Location: verify_code.php?flow=login");
                    exit;
                } else {
                    $error = "Nao foi possivel enviar o codigo de seguranca para o teu email. Tenta novamente em instantes.";
                    $clearCodes = $conn->prepare("DELETE FROM auth_codes WHERE user_id = ? AND purpose = 'login' AND used_at IS NULL");
                    $clearCodes->bind_param("i", $userId);
                    $clearCodes->execute();
                    $clearCodes->close();
                }
            } else {
                $_SESSION['pending_login_user'] = [
                    'id' => $userId,
                    'role' => $user['role'] ?? 'user',
                    'name' => $user['name'] ?? '',
                    'email' => $user['email'] ?? '',
                    'profile_pic' => $user['profile_pic'] ?? '',
                ];

                header("Location: verify_code.php?flow=login");
                exit;
            }
        }

        $error = "Password incorreta.";
    } else {
        $error = "Email não encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MMA 360</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Teko', sans-serif;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css"></head>

<body class="min-h-screen bg-gradient-to-br from-neutral-900 via-slate-800 to-neutral-900 text-neutral-100 flex items-center justify-center relative">

<div class="absolute inset-0">
    <img src="https://cdn.vox-cdn.com/uploads/chorus_image/image/72857030/1254763496.0.jpg"
         class="w-full h-full object-cover opacity-15" alt="Background">
    <div class="absolute inset-0 bg-gradient-to-t from-neutral-900 via-neutral-900/80 to-neutral-900/40"></div>
</div>

<div class="relative z-10 w-full max-w-5xl mx-auto px-6">
    <div class="grid grid-cols-1 md:grid-cols-2 bg-neutral-800/80 backdrop-blur rounded-2xl shadow-2xl overflow-hidden">

        <div class="hidden md:flex flex-col justify-center p-12 bg-gradient-to-b from-slate-700 to-slate-800">
            <h1 class="text-5xl font-bold tracking-widest mb-6 text-neutral-100">MMA 360</h1>
            <p class="text-2xl text-neutral-200 mb-8">A tua plataforma para eventos, atletas e conteúdo exclusivo de MMA.</p>

            <ul class="text-xl space-y-4 text-neutral-200">
                <li>Transmissões ao vivo</li>
                <li>Arquivo completo de eventos</li>
                <li>Conteúdo exclusivo</li>
                <li>Qualidade profissional</li>
            </ul>
        </div>

        <div class="p-10 md:p-12 bg-neutral-900">
            <div class="mb-10 text-center">
                <img src="assets/logo-mma360.png.png" class="h-20 mx-auto mb-4" alt="Logo">
                <h2 class="text-4xl font-bold tracking-widest text-slate-300">LOGIN</h2>
                <p class="text-neutral-400 text-lg">Acede à tua conta</p>
            </div>

            <?php if (!empty($error)): ?>
                <p class="bg-red-600 text-white p-3 rounded mb-6 text-center text-xl"><?= e($error) ?></p>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <?= csrf_field(); ?>

                <div>
                    <label class="block text-lg mb-1 text-neutral-300">Email</label>
                    <input type="email" name="email" required
                           class="w-full px-4 py-3 rounded bg-neutral-800 border border-neutral-700 focus:outline-none focus:border-slate-500">
                </div>

                <div>
                    <label class="block text-lg mb-1 text-neutral-300">Password</label>
                    <div class="relative">
                        <input id="login-password" type="password" name="password" required
                               class="w-full px-4 py-3 pr-14 rounded bg-neutral-800 border border-neutral-700 focus:outline-none focus:border-slate-500">
                        <button type="button" data-toggle-target="login-password" class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-200 text-sm">Mostrar</button>
                    </div>
                </div>

                <button class="w-full bg-slate-600 py-3 text-2xl rounded hover:bg-slate-700 transition tracking-widest">
                    ENTRAR
                </button>
            </form>

            <a href="index.php" class="block text-center mt-8 text-slate-400 hover:text-slate-300 transition text-lg">Voltar ao site</a>

            <p class="text-center text-neutral-400 mt-4 text-lg">
                Não tens conta?
                <a href="register.php" class="text-red-500 hover:text-red-400">Criar conta</a>
            </p>

        </div>
    </div>
</div>

<script>
document.querySelectorAll('button[data-toggle-target]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const input = document.getElementById(btn.getAttribute('data-toggle-target'));
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.textContent = isPassword ? 'Ocultar' : 'Mostrar';
    });
});
</script>

</body>
</html>


