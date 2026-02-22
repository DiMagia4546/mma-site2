<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nós | MMA 360</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Fontes -->
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1,h2,h3 { font-family: 'Teko', sans-serif; }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-neutral-900 via-slate-800 to-neutral-900 text-neutral-100 relative">

<!-- Fundo -->
<div class="absolute inset-0">
    <img src="https://cdn.vox-cdn.com/uploads/chorus_image/image/72857030/1254763496.0.jpg" 
         class="w-full h-full object-cover opacity-15">
    <div class="absolute inset-0 bg-gradient-to-t from-neutral-900 via-neutral-900/80 to-neutral-900/40"></div>
</div>

<!-- NAVBAR (IGUAL À PÁGINA PRINCIPAL) -->
<nav class="fixed top-0 w-full z-40 bg-neutral-900/70 backdrop-blur border-b border-neutral-700">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

        <a href="index.php" class="flex items-center gap-3">
            <img src="pf-removebg-preview.png" class="h-10">
            <span class="text-xl font-semibold tracking-widest text-red-500">MMA 360</span>
        </a>

        <ul class="hidden md:flex gap-8 text-sm uppercase tracking-wide">
            <li><a href="index.php" class="hover:text-red-500 transition">Início</a></li>
            <li><a href="noticias.php" class="hover:text-red-500 transition">Notícias</a></li>
            <li><a href="about.php" class="hover:text-red-500 transition">Quem Somos</a></li>
            <li><a href="fighters.php" class="text-red-500">Lutadores</a></li>
            <li><a href="eventos.php" class="hover:text-red-500 transition">Eventos</a></li>
            <li><a href="contacto.php" class="hover:text-red-500 transition">Contacto</a></li>
            <li><a href="login.php" class="hover:text-red-500 transition">Login</a></li>
        </ul>

    </div>
</nav>

<!-- ABOUT SECTION -->
<section class="relative py-24 px-6 max-w-7xl mx-auto">
    <div class="text-center mb-16">
        <h1 class="text-5xl font-bold text-red-600 mb-6 tracking-widest">Sobre Nós</h1>
        <p class="text-lg text-neutral-300 max-w-3xl mx-auto">
            A nossa missão é dar visibilidade e força às organizações de artes marciais mistas de menor dimensão. 
            Trabalhamos para promover academias locais e projetos independentes, oferecendo espaço para que atletas 
            e treinadores possam crescer e mostrar o seu talento.
        </p>
        <p class="text-lg text-neutral-300 max-w-3xl mx-auto mt-4">
            Desde iniciantes a lutadores profissionais, fornecemos treino personalizado, instalações de última geração 
            e uma comunidade de apoio para dominar no ringue.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
        <div class="bg-neutral-800 p-8 rounded-2xl shadow-lg hover:scale-105 transition">
            <h3 class="text-2xl font-bold mb-3 text-red-600">Oportunidades para Organizações</h3>
            <p class="text-neutral-300">
                Criamos iniciativas que ajudam na divulgação de eventos locais, promovemos competições regionais 
                e oferecemos ferramentas para que as organizações possam alcançar mais público e reconhecimento.
            </p>
        </div>

        <div class="bg-neutral-800 p-8 rounded-2xl shadow-lg hover:scale-105 transition">
            <h3 class="text-2xl font-bold mb-3 text-red-600">Nossa Filosofia</h3>
            <p class="text-neutral-300">
                Acreditamos na disciplina, no trabalho árduo e na melhoria contínua. Nosso objetivo é apoiar 
                organizações menores, criar oportunidades e ajudar os atletas a alcançar novos patamares.
            </p>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="relative bg-neutral-900 border-t border-red-600 py-12 text-center mt-16">
    <p class="text-neutral-300 mb-4 text-lg">Email: mma360@gmail.com</p>

    <div class="flex justify-center gap-6 mb-6 text-lg">
        <a href="#" class="text-red-600 hover:text-red-700 transition">Twitter</a>
        <a href="#" class="text-red-600 hover:text-red-700 transition">Instagram</a>
        <a href="#" class="text-red-600 hover:text-red-700 transition">Facebook</a>
    </div>

    <p class="text-neutral-500">© 2025 MMA 360 — Todos os direitos reservados</p>
</footer>

</body>
</html>
