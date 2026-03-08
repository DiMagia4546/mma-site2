<?php
session_start();
include "security.php";
include "navbar.php";
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MMA 360 | Plataforma de MMA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css">
    <script src="assets/account-menu.js" defer></script>
</head>
<body class="text-neutral-100">

<?php if (isset($_SESSION['welcome'])): ?>
    <div id="welcomeBox" class="fixed top-6 left-1/2 -translate-x-1/2 bg-red-600 text-white px-6 py-3 rounded-lg shadow-xl flex items-center gap-4 z-50">
        <span class="text-lg"><?= e($_SESSION['welcome']) ?></span>
        <button onclick="closeWelcome()" class="text-xl hover:text-black transition">x</button>
    </div>
    <?php unset($_SESSION['welcome']); ?>
<?php endif; ?>

<?php render_main_nav('index'); ?>

<section class="relative min-h-screen flex items-center pt-28 pb-16">
    <div class="absolute inset-0">
        <img src="https://i2-prod.mirror.co.uk/incoming/article1516071.ece/ALTERNATES/s1227b/_MG_0139.jpg" class="w-full h-full object-cover opacity-45" alt="MMA arena">
        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/55 to-black/80"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-6 w-full grid grid-cols-1 lg:grid-cols-5 gap-8 items-center">
        <div class="lg:col-span-3">
            <span class="inline-block bg-red-600 text-white px-4 py-1 rounded-full text-sm uppercase tracking-[0.2em] mb-6">Cobertura de Elite</span>
            <h1 class="text-6xl md:text-7xl leading-[0.95] mb-6">MMA com Qualidade Profissional</h1>
            <p class="text-lg md:text-xl text-neutral-300 max-w-2xl mb-8">
                Eventos, atletas, análise técnica e gestão completa numa única plataforma desenhada para fãs e equipas.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="eventos.php" class="bg-red-600 text-white px-8 py-3 rounded-lg text-lg">Ver Eventos</a>
                <a href="fighters.php" class="bg-neutral-800 border border-neutral-700 px-8 py-3 rounded-lg text-lg">Explorar Lutadores</a>
            </div>
        </div>

        <div class="lg:col-span-2 bg-neutral-900/80 border border-neutral-700 rounded-2xl p-6">
            <h2 class="text-3xl mb-4">Visão Rápida</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-neutral-800 rounded-xl p-4 border border-neutral-700">
                    <p class="text-3xl text-red-400">+100</p>
                    <p class="text-sm text-neutral-400">Perfis de Lutadores</p>
                </div>
                <div class="bg-neutral-800 rounded-xl p-4 border border-neutral-700">
                    <p class="text-3xl text-red-400">24/7</p>
                    <p class="text-sm text-neutral-400">Atualização de Conteúdo</p>
                </div>
                <div class="bg-neutral-800 rounded-xl p-4 border border-neutral-700">
                    <p class="text-3xl text-red-400">HD</p>
                    <p class="text-sm text-neutral-400">Media e Banners</p>
                </div>
                <div class="bg-neutral-800 rounded-xl p-4 border border-neutral-700">
                    <p class="text-3xl text-red-400">Admin</p>
                    <p class="text-sm text-neutral-400">Gestão Completa</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 pb-20">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <article class="bg-neutral-800 border border-neutral-700 rounded-2xl p-6">
            <h3 class="text-3xl mb-3">Gestão de Eventos</h3>
            <p class="text-neutral-300">Cria eventos, organiza fight cards e mantém o histórico sempre atualizado.</p>
        </article>
        <article class="bg-neutral-800 border border-neutral-700 rounded-2xl p-6">
            <h3 class="text-3xl mb-3">Perfis Completos</h3>
            <p class="text-neutral-300">Dados de carreira, recordes e histórico de combates com apresentação profissional.</p>
        </article>
        <article class="bg-neutral-800 border border-neutral-700 rounded-2xl p-6">
            <h3 class="text-3xl mb-3">Conta de Utilizador</h3>
            <p class="text-neutral-300">Favoritos, atividade recente e painel pessoal para acompanhamento rápido.</p>
        </article>
    </div>
</section>

<footer class="border-t border-neutral-700 py-10 text-center">
    <p class="text-neutral-300 text-lg">mma360@gmail.com</p>
    <p class="mt-4 text-neutral-500 text-sm">© 2026 MMA 360 - Todos os direitos reservados</p>
</footer>

<script>
function closeWelcome() {
    const box = document.getElementById('welcomeBox');
    if (!box) return;
    box.style.opacity = '0';
    setTimeout(() => box.remove(), 300);
}
</script>

</body>
</html>

