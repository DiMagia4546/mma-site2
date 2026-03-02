<?php
session_start();
include "security.php";
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recursos de Treino | MMA 360</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css">
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
            <li><a href="noticias.php" class="text-red-400">Notícias</a></li>
            <li><a href="about.php" class="hover:text-red-400 transition">Quem Somos</a></li>
            <li><a href="fighters.php" class="hover:text-red-400 transition">Lutadores</a></li>
            <li><a href="eventos.php" class="hover:text-red-400 transition">Eventos</a></li>
            <li><a href="contacto.php" class="hover:text-red-400 transition">Contacto</a></li>
            <li><a href="login.php" class="hover:text-red-400 transition">Login</a></li>
        </ul>
    </div>
</nav>

<section class="min-h-[56vh] flex items-center justify-center pt-24 bg-cover bg-center relative" style="background-image:url('https://cdn.onefc.com/wp-content/uploads/2020/05/ONE-Training.jpg')">
    <div class="absolute inset-0 bg-black/70"></div>
    <div class="relative text-center max-w-4xl px-6">
        <h1 class="text-6xl md:text-7xl mb-4">Recursos de Treino</h1>
        <p class="text-xl text-neutral-300">Conteúdo pensado para evolução técnica, física e estratégica.</p>
    </div>
</section>

<section class="py-20">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-8">
        <article class="bg-neutral-800 border border-neutral-700 rounded-2xl p-8">
            <h3 class="text-3xl mb-3">Vídeo Tutoriais</h3>
            <p class="text-neutral-300 mb-6">Aulas de striking, grappling e transições de combate para todos os níveis.</p>
            <a href="#" class="text-red-400 hover:text-red-300">Ver tutoriais</a>
        </article>

        <article class="bg-neutral-800 border border-neutral-700 rounded-2xl p-8">
            <h3 class="text-3xl mb-3">Blog Técnico</h3>
            <p class="text-neutral-300 mb-6">Análise de estratégias, game plans e tendências no cenário atual do MMA.</p>
            <a href="blog.php" class="text-red-400 hover:text-red-300">Ler artigos</a>
        </article>

        <article class="bg-neutral-800 border border-neutral-700 rounded-2xl p-8">
            <h3 class="text-3xl mb-3">Guias Práticos</h3>
            <p class="text-neutral-300 mb-6">Modelos semanais de treino, condicionamento e recomendações de recuperação.</p>
            <a href="#" class="text-red-400 hover:text-red-300">Explorar guias</a>
        </article>
    </div>
</section>

<footer class="border-t border-neutral-700 py-10 text-center">
    <p class="text-neutral-300 text-lg">mma360@gmail.com</p>
    <p class="mt-4 text-neutral-500 text-sm">© 2026 MMA 360 - Todos os direitos reservados</p>
</footer>

</body>
</html>
