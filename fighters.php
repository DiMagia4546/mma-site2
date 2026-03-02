<?php
session_start();
include "db.php";
include "security.php";

$stmt = $conn->prepare("SELECT * FROM fighters ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
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
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css">
    <script src="assets/account-menu.js" defer></script>
</head>

<body class="bg-neutral-900 text-neutral-100">

<nav class="fixed top-0 w-full z-40 bg-neutral-900/70 backdrop-blur border-b border-neutral-700">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

        <a href="index.php" class="flex items-center gap-3">
            <img src="pf-removebg-preview.png" class="h-10" alt="Logo">
            <span class="text-xl font-semibold tracking-widest text-red-500">MMA 360</span>
        </a>

        <ul class="hidden md:flex gap-8 text-sm uppercase tracking-wide">
            <li><a href="index.php" class="hover:text-red-500 transition">Início</a></li>
            <li><a href="noticias.php" class="hover:text-red-500 transition">Notícias</a></li>
            <li><a href="about.php" class="hover:text-red-500 transition">Quem Somos</a></li>
            <li><a href="fighters.php" class="text-red-500">Lutadores</a></li>
            <li><a href="eventos.php" class="hover:text-red-500 transition">Eventos</a></li>
            <li><a href="contacto.php" class="hover:text-red-500 transition">Contacto</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                $displayName = $_SESSION['user_name'] ?? 'Conta';
                $displayEmail = $_SESSION['user_email'] ?? '';
                $displayPic = $_SESSION['user_profile_pic'] ?? '';
                $initial = strtoupper(substr(trim($displayName) ?: 'U', 0, 1));
                ?>
                <li class="relative account-menu">
                    <button type="button" class="account-menu-toggle flex items-center gap-2 text-neutral-100 hover:text-red-500 transition">
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
                        <a href="logout.php" class="block px-4 py-3 text-sm text-red-500 hover:bg-neutral-800">Terminar Sessão</a>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="login.php" class="hover:text-red-500 transition">Login</a></li>
            <?php endif; ?>
        </ul>

    </div>
</nav>

<section class="pt-32 pb-10 text-center">
    <h1 class="text-6xl font-bold tracking-widest text-red-500">LUTADORES</h1>
    <p class="text-neutral-400 text-lg mt-2">Explora os atletas do MMA 360</p>
</section>

<div class="max-w-7xl mx-auto px-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-10 pb-20">

<?php while ($row = $result->fetch_assoc()): ?>

    <div class="bg-neutral-800 rounded-xl overflow-hidden shadow-lg hover:scale-105 transition cursor-pointer">
        <img src="<?= e($row['image']) ?>" class="w-full h-80 object-cover" alt="<?= e($row['name']) ?>">

        <div class="p-5">
            <h3 class="text-3xl font-bold tracking-wide text-white"><?= e($row['name']) ?></h3>
            <p class="text-red-500 text-lg -mt-1"><?= e($row['weight_class']) ?></p>
            <p class="text-neutral-300 mt-2"><?= e($row['nationality']) ?></p>

            <p class="text-neutral-400 mt-3 text-sm">
                Record: <span class="text-white font-semibold"><?= (int) $row['wins'] ?>-<?= (int) $row['losses'] ?>-0</span>
            </p>

            <p class="text-neutral-400 text-sm">
                KO: <?= (int) $row['kos'] ?> | Sub: <?= (int) $row['submissions'] ?>
            </p>

            <a href="fighter.php?id=<?= (int) $row['id'] ?>" class="block mt-4 text-center bg-red-600 py-2 rounded hover:bg-red-700 transition">Ver Perfil</a>
        </div>
    </div>

<?php endwhile; ?>

</div>

<footer class="bg-neutral-900 border-t border-neutral-700 py-10 text-center">
    <p class="text-neutral-300 text-lg">mma360@gmail.com</p>
    <p class="mt-4 text-neutral-500 text-sm">© 2026 MMA 360 - Todos os direitos reservados</p>
</footer>

</body>
</html>

