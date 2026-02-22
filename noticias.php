<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recursos de Treino | MMA 360</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Fontes -->
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Teko', sans-serif; }
    </style>
</head>

<body class="bg-black text-gray-100">

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

<!-- HERO -->
<section class="min-h-[60vh] flex items-center justify-center pt-24 bg-cover bg-center relative"
         style="background-image:url('https://cdn.onefc.com/wp-content/uploads/2020/05/ONE-Training.jpg')">

    <div class="absolute inset-0 bg-black/75"></div>

    <div class="relative text-center max-w-4xl px-6">
        <h1 class="text-6xl md:text-7xl font-bold tracking-widest mb-6">
            RECURSOS DE TREINO
        </h1>
        <p class="text-2xl text-gray-300">
            Aprende, evolui e prepara-te como um verdadeiro lutador profissional.
        </p>
    </div>
</section>

<!-- RESOURCES -->
<section class="py-24 bg-gradient-to-b from-black to-gray-900">
    <div class="max-w-7xl mx-auto px-6">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">

            <!-- CARD -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-8 shadow-xl hover:border-red-600 hover:scale-105 transition">
                <div class="text-5xl mb-4">🎥</div>
                <h3 class="text-3xl font-bold mb-3">Vídeo Tutoriais</h3>
                <p class="text-gray-400 text-xl mb-6">
                    Técnicas de striking, grappling, wrestling e condicionamento físico.
                </p>
                <a href="#" class="inline-block text-red-600 text-xl hover:underline">
                    Ver Tutoriais →
                </a>
            </div>

            <!-- CARD -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-8 shadow-xl hover:border-red-600 hover:scale-105 transition">
                <div class="text-5xl mb-4">📖</div>
                <h3 class="text-3xl font-bold mb-3">Blog & Artigos</h3>
                <p class="text-gray-400 text-xl mb-6">
                    Dicas de lutadores profissionais, treinadores e especialistas.
                </p>
                <a href="blog.php" class="inline-block text-red-600 text-xl hover:underline">
                    Ler Blogs →
                </a>
            </div>

            <!-- CARD -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-8 shadow-xl hover:border-red-600 hover:scale-105 transition">
                <div class="text-5xl mb-4">📋</div>
                <h3 class="text-3xl font-bold mb-3">Guias de Treino</h3>
                <p class="text-gray-400 text-xl mb-6">
                    Planos de treino, rotinas semanais e nutrição para atletas.
                </p>
                <a href="#" class="inline-block text-red-600 text-xl hover:underline">
                    Download →
                </a>
            </div>

        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="bg-black border-t border-red-600 py-12 text-center">
    <p class="text-xl mb-4">📧 mma360@gmail.com</p>

    <div class="flex justify-center gap-8 text-xl mb-6">
        <a href="#" class="text-red-600 hover:text-red-700 transition">Twitter</a>
        <a href="#" class="text-red-600 hover:text-red-700 transition">Instagram</a>
        <a href="#" class="text-red-600 hover:text-red-700 transition">Facebook</a>
    </div>

    <p class="text-gray-500">
        © 2025 MMA 360 — Todos os direitos reservados
    </p>
</footer>

</body>
</html>
