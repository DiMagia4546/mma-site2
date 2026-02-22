<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MMA 360</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Fontes -->
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Teko', sans-serif; }
    </style>
</head>

<body class="bg-neutral-900 text-neutral-100">

<!-- WELCOME BOX -->
<?php if (isset($_SESSION['welcome'])): ?>
    <div id="welcomeBox"
         class="fixed top-6 left-1/2 -translate-x-1/2 bg-red-600 text-white px-6 py-3 rounded-lg shadow-xl flex items-center gap-4 z-50">
        <span class="text-xl"><?= htmlspecialchars($_SESSION['welcome']) ?></span>
        <button onclick="closeWelcome()" class="text-2xl hover:text-black transition">✕</button>
    </div>
    <?php unset($_SESSION['welcome']); ?>
<?php endif; ?>

<!-- NAVBAR ESCURA -->
<nav class="fixed top-0 w-full z-40 bg-neutral-900/70 backdrop-blur border-b border-neutral-700">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

        <a href="index.php" class="flex items-center gap-3">
            <img src="pf-removebg-preview.png" class="h-10">
            <span class="text-xl font-semibold tracking-widest text-red-500">MMA 360</span>
        </a>

        <ul class="hidden md:flex gap-8 text-sm uppercase tracking-wide">
            <li><a href="index.php" class="text-red-500">Início</a></li>
            <li><a href="noticias.php" class="hover:text-red-500 transition">Notícias</a></li>
            <li><a href="about.php" class="hover:text-red-500 transition">Quem Somos</a></li>
            <li><a href="fighters.php" class="hover:text-red-500 transition">Lutadores</a></li>
            <li><a href="eventos.php" class="hover:text-red-500 transition">Eventos</a></li>
            <li><a href="contacto.php" class="hover:text-red-500 transition">Contacto</a></li>
            <li><a href="login.php" class="hover:text-red-500 transition">Login</a></li>
        </ul>

    </div>
</nav>

<!-- HERO ESCURO COM IMAGEM MAIS VISÍVEL -->
<section class="relative min-h-screen flex items-center justify-center pt-24">

    <!-- Imagem de fundo -->
    <div class="absolute inset-0">
        <img src="https://i2-prod.mirror.co.uk/incoming/article1516071.ece/ALTERNATES/s1227b/_MG_0139.jpg"
             class="w-full h-full object-cover opacity-80"> <!-- MAIS VISÍVEL -->
        <div class="absolute inset-0 bg-gradient-to-b from-neutral-900/40 via-neutral-900/50 to-neutral-900/80"></div>
        <!-- OVERLAY SUAVE -->
    </div>

    <!-- Conteúdo -->
    <div class="relative text-center max-w-3xl px-6">

        <span class="inline-block bg-red-600 text-white px-4 py-1 rounded-full text-lg tracking-wide mb-6 shadow">
            A sua fonte de MMA
        </span>

        <h1 class="text-6xl md:text-7xl font-bold tracking-widest mb-6 text-white drop-shadow-xl">
            Tudo sobre Artes Marciais Mistas
        </h1>

        <p class="text-xl md:text-2xl text-neutral-200 mb-10 drop-shadow-lg">
            Cobertura completa de eventos, perfis detalhados de lutadores, notícias em tempo real e análises aprofundadas.
        </p>

        <div class="flex justify-center gap-6">
            <a href="eventos.php"
               class="bg-red-600 text-white px-10 py-4 text-xl rounded-lg hover:bg-red-700 transition shadow-lg">
                Ver Eventos
            </a>

            <a href="fighters.php"
               class="bg-neutral-800 text-neutral-100 px-10 py-4 text-xl rounded-lg border border-neutral-700 hover:bg-neutral-700 transition shadow-lg">
                Explorar Lutadores
            </a>
        </div>
    </div>
</section>

<!-- FOOTER ESCURO -->
<footer class="bg-neutral-900 border-t border-neutral-700 py-10 text-center">
    <p class="text-neutral-300 text-lg">📧 mma360@gmail.com</p>
    <p class="mt-4 text-neutral-500 text-sm">
        © 2025 MMA 360 — Todos os direitos reservados
    </p>
</footer>

<script>
    function closeWelcome() {
        const box = document.getElementById('welcomeBox');
        box.style.opacity = "0";
        setTimeout(() => box.remove(), 400);
    }
</script>

</body>
</html>
