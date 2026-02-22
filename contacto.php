<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacte-nos | MMA 360</title>

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
            <li><a href="fighters.php" class="hover:text-red-500 transition">Lutadores</a></li>
            <li><a href="eventos.php" class="hover:text-red-500 transition">Eventos</a></li>
            <li><a href="contacto.php" class="text-red-500">Contacto</a></li>
            <li><a href="login.php" class="hover:text-red-500 transition">Login</a></li>
        </ul>

    </div>
</nav>

<!-- CONTACT SECTION -->
<section class="relative py-24 px-6 max-w-3xl mx-auto">
    <h1 class="text-4xl font-bold text-red-600 mb-10 text-center tracking-widest">Contacte-nos</h1>

    <div class="bg-neutral-800 p-10 rounded-2xl shadow-lg relative z-10">
        <?php if (isset($_SESSION['success'])) { ?>
            <p class="text-green-500 mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php } ?>
        <?php if (isset($_SESSION['error'])) { ?>
            <p class="text-red-500 mb-4"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php } ?>

        <form method="POST" action="submit_contact.php" class="space-y-6">
            <div>
                <label class="block text-lg mb-1 text-neutral-300">Nome</label>
                <input type="text" name="name" required
                       class="w-full p-3 rounded bg-neutral-900 border border-neutral-700 focus:outline-none focus:border-red-600">
            </div>

            <div>
                <label class="block text-lg mb-1 text-neutral-300">Email</label>
                <input type="email" name="email" required
                       class="w-full p-3 rounded bg-neutral-900 border border-neutral-700 focus:outline-none focus:border-red-600">
            </div>

            <div>
                <label class="block text-lg mb-1 text-neutral-300">Mensagem</label>
                <textarea name="message" rows="5" required
                          class="w-full p-3 rounded bg-neutral-900 border border-neutral-700 focus:outline-none focus:border-red-600"></textarea>
            </div>

            <button type="submit" class="w-full bg-red-600 py-3 text-xl rounded hover:bg-red-700 transition tracking-widest">
                Enviar Mensagem
            </button>
        </form>
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
