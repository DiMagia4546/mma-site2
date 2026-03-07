<?php
session_start();
include "db.php";
include "security.php";
include "mailer.php";
include "auth_verification.php";

$error = "";
$success = "";

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

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email inválido.";
    } elseif ($password !== $confirm) {
        $error = "As passwords não coincidem.";
    } elseif (strlen($password) < 4) {
        $error = "A password deve ter pelo menos 4 caracteres e incluir pelo menos 1 simbolo.";
    } elseif (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $error = "A password deve incluir pelo menos 1 simbolo (ex: ! @ # $ %).";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $error = "Este email já está registado.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $pendingId = upsert_pending_registration($conn, $name, $email, $hashed);

            if ($pendingId > 0) {
                $code = create_auth_code($conn, $pendingId, 'register_pending', 600);
                if (!send_auth_code_email($name, $email, $code, 'register')) {
                    if (dev_show_code_on_email_fail()) {
                        $_SESSION['pending_register_id'] = $pendingId;
                        $_SESSION['pending_verify_email'] = $email;
                        $_SESSION['pending_verify_name'] = $name;
                        $_SESSION['dev_auth_code_register'] = $code;
                        header("Location: verify_code.php?flow=register");
                        exit;
                    }
                    $cleanupCodes = $conn->prepare("DELETE FROM pending_auth_codes WHERE pending_id = ? AND purpose = 'register_pending'");
                    $cleanupCodes->bind_param("i", $pendingId);
                    $cleanupCodes->execute();
                    $cleanupCodes->close();
                    $error = "Nao foi possivel enviar o codigo para o email. A conta nao foi criada. Confirma o SMTP e tenta novamente.";
                } else {
                    $_SESSION['pending_register_id'] = $pendingId;
                    $_SESSION['pending_verify_email'] = $email;
                    $_SESSION['pending_verify_name'] = $name;
                    header("Location: verify_code.php?flow=register");
                    exit;
                }
            } else {
                $error = "Erro ao criar conta.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registar | MMA 360</title>

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
            <p class="text-2xl text-neutral-200 mb-8">Junta-te à comunidade e acompanha tudo sobre MMA.</p>

            <ul class="text-xl space-y-4 text-neutral-200">
                <li>Conta gratuita</li>
                <li>Favoritos e histórico</li>
                <li>Painel de utilizador</li>
                <li>Conteúdo exclusivo</li>
            </ul>
        </div>

        <div class="p-10 md:p-12 bg-neutral-900">
            <div class="mb-10 text-center">
                <img src="assets/logo-mma360.png.png" class="h-20 mx-auto mb-4" alt="Logo">
                <h2 class="text-4xl font-bold tracking-widest text-slate-300">CRIAR CONTA</h2>
                <p class="text-neutral-400 text-lg">Junta-te à plataforma</p>
            </div>

            <?php if (!empty($error)): ?>
                <p class="bg-red-600 text-white p-3 rounded mb-6 text-center text-xl"><?= e($error) ?></p>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <p class="bg-green-600 text-white p-3 rounded mb-6 text-center text-xl"><?= e($success) ?></p>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <?= csrf_field(); ?>

                <div>
                    <label class="block text-lg mb-1 text-neutral-300">Nome</label>
                    <input type="text" name="name" required class="w-full px-4 py-3 rounded bg-neutral-800 border border-neutral-700 focus:outline-none focus:border-slate-500">
                </div>

                <div>
                    <label class="block text-lg mb-1 text-neutral-300">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-3 rounded bg-neutral-800 border border-neutral-700 focus:outline-none focus:border-slate-500">
                </div>

                <div>
                    <label class="block text-lg mb-1 text-neutral-300">Password</label>
                    <div class="relative">
                        <input id="register-password" type="password" name="password" required class="w-full px-4 py-3 pr-14 rounded bg-neutral-800 border border-neutral-700 focus:outline-none focus:border-slate-500">
                        <button type="button" data-toggle-target="register-password" class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-200 text-sm">Mostrar</button>
                    </div>
                </div>

                <div>
                    <label class="block text-lg mb-1 text-neutral-300">Confirmar Password</label>
                    <div class="relative">
                        <input id="register-confirm" type="password" name="confirm" required class="w-full px-4 py-3 pr-14 rounded bg-neutral-800 border border-neutral-700 focus:outline-none focus:border-slate-500">
                        <button type="button" data-toggle-target="register-confirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-200 text-sm">Mostrar</button>
                    </div>
                </div>

                <button class="w-full bg-slate-600 py-3 text-2xl rounded hover:bg-slate-700 transition tracking-widest">REGISTAR</button>
            </form>

            <a href="login.php" class="block text-center mt-8 text-slate-400 hover:text-slate-300 transition text-lg">Já tens conta? Entrar</a>
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


