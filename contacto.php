<?php
session_start();
include "security.php";
include "navbar.php";
$loggedName = trim((string) ($_SESSION['user_name'] ?? ''));
$loggedEmail = trim((string) ($_SESSION['user_email'] ?? ''));
$isLoggedIn = is_logged_in();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto | MMA 360</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css">
    <script src="assets/account-menu.js" defer></script>
</head>
<body class="text-neutral-100">

<?php render_main_nav('contacto'); ?>

<main class="pt-28 pb-16 max-w-5xl mx-auto px-6">
    <header class="max-w-2xl mb-8">
        <h1 class="text-6xl text-red-400 mb-4">Fala connosco</h1>
        <p class="text-neutral-300">Partilha dúvidas, propostas de parceria ou sugestões para melhorar a plataforma.</p>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <section class="lg:col-span-3 bg-neutral-800 border border-neutral-700 rounded-2xl p-8">
            <?php if (isset($_SESSION['success'])): ?>
                <p class="bg-green-600 text-white px-4 py-3 rounded mb-5"><?= e($_SESSION['success']); unset($_SESSION['success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="bg-red-600 text-white px-4 py-3 rounded mb-5"><?= e($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>

            <form method="POST" action="submit_contact.php" class="space-y-5">
                <?= csrf_field(); ?>
                <div>
                    <label class="block text-sm uppercase tracking-wide text-neutral-400 mb-2">Nome</label>
                    <input type="text" name="name" required value="<?= e($loggedName) ?>" class="w-full px-4 py-3">
                </div>
                <div>
                    <label class="block text-sm uppercase tracking-wide text-neutral-400 mb-2">Email</label>
                    <input
                        type="email"
                        name="email"
                        required
                        value="<?= e($loggedEmail) ?>"
                        class="w-full px-4 py-3 <?= $isLoggedIn ? 'bg-neutral-700 text-neutral-300 cursor-not-allowed' : '' ?>"
                        <?= $isLoggedIn ? 'readonly' : '' ?>
                    >
                </div>
                <div>
                    <label class="block text-sm uppercase tracking-wide text-neutral-400 mb-2">Mensagem</label>
                    <textarea name="message" rows="5" required class="w-full px-4 py-3"></textarea>
                </div>
                <button type="submit" class="w-full bg-red-600 py-3 rounded-lg text-lg">Enviar Mensagem</button>
                <?php if (!$isLoggedIn): ?>
                    <p class="text-xs text-amber-300 text-center">Para enviar mensagem, tens de iniciar sessão.</p>
                <?php else: ?>
                    <p class="text-xs text-neutral-400 text-center">Mensagem será enviada com o email da tua conta registada.</p>
                <?php endif; ?>
            </form>
        </section>

        <aside class="lg:col-span-2 bg-neutral-800 border border-neutral-700 rounded-2xl p-8">
            <h2 class="text-3xl mb-4">Informação</h2>
            <p class="text-neutral-300 mb-4">Respondemos normalmente em 24h em dias úteis.</p>
            <p class="text-neutral-300 mb-2"><strong>Email:</strong> mma360.project@gmail.com</p>
            <p class="text-neutral-300 mb-6"><strong>Foco:</strong> eventos, media e comunidade MMA.</p>
            <a href="about.php" class="text-red-400 hover:text-red-300">Conhecer a equipa</a>
        </aside>
    </div>
</main>

<footer class="border-t border-neutral-700 py-10 text-center">
    <p class="text-neutral-300 text-lg">mma360.project@gmail.com</p>
    <p class="mt-4 text-neutral-500 text-sm">© 2026 MMA 360 - Todos os direitos reservados</p>
</footer>

</body>
</html>

