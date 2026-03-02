<?php
session_start();
include "security.php";
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

<nav class="fixed top-0 w-full z-40 backdrop-blur border-b border-neutral-700">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-3">
            <img src="pf-removebg-preview.png" class="h-10" alt="Logo MMA 360">
            <span class="text-xl font-semibold tracking-widest text-red-500">MMA 360</span>
        </a>

        <ul class="hidden md:flex gap-8 text-sm uppercase tracking-wide">
            <li><a href="index.php" class="hover:text-red-400 transition">Início</a></li>
            <li><a href="noticias.php" class="hover:text-red-400 transition">Notícias</a></li>
            <li><a href="about.php" class="hover:text-red-400 transition">Quem Somos</a></li>
            <li><a href="fighters.php" class="hover:text-red-400 transition">Lutadores</a></li>
            <li><a href="eventos.php" class="hover:text-red-400 transition">Eventos</a></li>
            <li><a href="contacto.php" class="text-red-400">Contacto</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                $displayName = $_SESSION['user_name'] ?? 'Conta';
                $displayEmail = $_SESSION['user_email'] ?? '';
                $displayPic = $_SESSION['user_profile_pic'] ?? '';
                $initial = strtoupper(substr(trim($displayName) ?: 'U', 0, 1));
                ?>
                <li class="relative account-menu">
                    <button type="button" class="account-menu-toggle flex items-center gap-2 text-neutral-100 hover:text-red-400 transition">
                        <?php if (!empty($displayPic)): ?>
                            <img src="<?= e($displayPic) ?>" class="w-9 h-9 rounded-full object-cover border border-red-500" alt="Perfil">
                        <?php else: ?>
                            <span class="w-9 h-9 rounded-full bg-red-600 text-white flex items-center justify-center text-sm font-bold"><?= e($initial) ?></span>
                        <?php endif; ?>
                        <span class="hidden lg:block normal-case text-sm"><?= e($displayName) ?></span>
                    </button>

                    <div class="account-menu-panel hidden absolute right-0 top-12 w-72 bg-neutral-900/95 border border-neutral-700 rounded-xl shadow-2xl overflow-hidden normal-case">
                        <div class="px-4 py-3 border-b border-neutral-700">
                            <p class="text-sm font-semibold text-white"><?= e($displayName) ?></p>
                            <p class="text-xs text-neutral-400"><?= e($displayEmail) ?></p>
                        </div>
                        <a href="dashboard.php" class="block px-4 py-3 text-sm hover:bg-neutral-800">Dashboard</a>
                        <a href="logout.php" class="block px-4 py-3 text-sm text-red-400 hover:bg-neutral-800">Terminar Sessão</a>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="login.php" class="hover:text-red-400 transition">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

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
                    <input type="text" name="name" required class="w-full px-4 py-3">
                </div>
                <div>
                    <label class="block text-sm uppercase tracking-wide text-neutral-400 mb-2">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-3">
                </div>
                <div>
                    <label class="block text-sm uppercase tracking-wide text-neutral-400 mb-2">Mensagem</label>
                    <textarea name="message" rows="5" required class="w-full px-4 py-3"></textarea>
                </div>
                <button type="submit" class="w-full bg-red-600 py-3 rounded-lg text-lg">Enviar Mensagem</button>
            </form>
        </section>

        <aside class="lg:col-span-2 bg-neutral-800 border border-neutral-700 rounded-2xl p-8">
            <h2 class="text-3xl mb-4">Informação</h2>
            <p class="text-neutral-300 mb-4">Respondemos normalmente em 24h em dias úteis.</p>
            <p class="text-neutral-300 mb-2"><strong>Email:</strong> mma360@gmail.com</p>
            <p class="text-neutral-300 mb-6"><strong>Foco:</strong> eventos, media e comunidade MMA.</p>
            <a href="about.php" class="text-red-400 hover:text-red-300">Conhecer a equipa</a>
        </aside>
    </div>
</main>

<footer class="border-t border-neutral-700 py-10 text-center">
    <p class="text-neutral-300 text-lg">mma360@gmail.com</p>
    <p class="mt-4 text-neutral-500 text-sm">© 2026 MMA 360 - Todos os direitos reservados</p>
</footer>

</body>
</html>
