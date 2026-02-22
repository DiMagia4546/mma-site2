<?php
session_start();
include "db.php";

$sql = "SELECT * FROM fighters ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lutadores | MMA 360</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Teko', sans-serif; }
    </style>
</head>

<body class="bg-neutral-900 text-neutral-100">

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

<!-- TÍTULO -->
<section class="pt-32 pb-10 text-center">
    <h1 class="text-6xl font-bold tracking-widest text-red-500">LUTADORES</h1>
    <p class="text-neutral-400 text-lg mt-2">Explora os atletas do MMA 360</p>
</section>

<!-- GRID DE LUTADORES -->
<div class="max-w-7xl mx-auto px-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-10 pb-20">

<?php while ($row = $result->fetch_assoc()): ?>

    <div class="bg-neutral-800 rounded-xl overflow-hidden shadow-lg hover:scale-105 transition cursor-pointer">

        <!-- FOTO DO LUTADOR -->
        <img src="<?= $row['image'] ?>" class="w-full h-80 object-cover">

        <!-- INFO -->
        <div class="p-5">

            <h3 class="text-3xl font-bold tracking-wide text-white">
                <?= $row['name'] ?>
            </h3>

            <p class="text-red-500 text-lg -mt-1">
                <?= $row['weight_class'] ?>
            </p>

            <p class="text-neutral-300 mt-2">
                <?= $row['nationality'] ?>
            </p>

            <p class="text-neutral-400 mt-3 text-sm">
                Record: 
                <span class="text-white font-semibold">
                    <?= $row['wins'] ?>-<?= $row['losses'] ?>-0
                </span>
            </p>

            <p class="text-neutral-400 text-sm">
                KO: <?= $row['kos'] ?> | Sub: <?= $row['submissions'] ?>
            </p>

            <a href="fighter.php?id=<?= $row['id'] ?>"
               class="block mt-4 text-center bg-red-600 py-2 rounded hover:bg-red-700 transition">
                Ver Perfil
            </a>

        </div>
    </div>

<?php endwhile; ?>

</div>

<!-- FOOTER -->
<footer class="bg-neutral-900 border-t border-neutral-700 py-10 text-center">
    <p class="text-neutral-300 text-lg">📧 mma360@gmail.com</p>
    <p class="mt-4 text-neutral-500 text-sm">
        © 2025 MMA 360 — Todos os direitos reservados
    </p>
</footer>

</body>
</html>
