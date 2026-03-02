<<<<<<< HEAD
<?php
session_start();
require_once "db.php";

if (!isset($_GET['id'])) {
    die("Evento não encontrado.");
}

$event_id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$res_event = $stmt->get_result();

if ($res_event->num_rows === 0) {
    die("Evento não existe.");
}
$event = $res_event->fetch_assoc();
$stmt->close();

$stmt_f = $conn->prepare("SELECT * FROM event_fights WHERE event_id = ? ORDER BY fight_order ASC");
$stmt_f->bind_param("i", $event_id);
$stmt_f->execute();
$fights = $stmt_f->get_result();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['name']) ?> | MMA 360</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Teko', sans-serif; }
    </style>
</head>

<body class="bg-neutral-900 text-neutral-100">

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
            <li><a href="eventos.php" class="text-red-500">Eventos</a></li>
            <li><a href="contacto.php" class="hover:text-red-500 transition">Contacto</a></li>
            <li><a href="login.php" class="hover:text-red-500 transition">Login</a></li>
        </ul>
    </div>
</nav>

<div class="pt-24"></div>

<?php
$banner = !empty($event['banner']) ? $event['banner'] : 'uploads/default_banner.webp';
?>
<div class="w-full h-64 md:h-80 bg-cover bg-center border-b border-neutral-700"
     style="background-image: linear-gradient(to top, rgba(0,0,0,0.75), rgba(0,0,0,0.3)), url('<?= htmlspecialchars($banner) ?>');">
    <div class="max-w-5xl mx-auto h-full flex flex-col justify-end px-6 pb-6">
        <h1 class="text-6xl font-bold text-white tracking-wide"><?= htmlspecialchars($event['name']) ?></h1>
        <p class="text-neutral-300 text-lg mt-2">
            📅 <?= date("d/m/Y", strtotime($event['date'])) ?> &nbsp; • &nbsp; 📍 <?= htmlspecialchars($event['location']) ?>
        </p>

        <?php if (isset($_SESSION['user_id'])): ?>
        <form method="POST" action="toggle_favorite.php">
            <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
            <button class="mt-4 bg-red-600 px-6 py-3 rounded-lg hover:bg-red-700 transition">
                ⭐ Adicionar aos Favoritos
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<section class="max-w-5xl mx-auto px-6 py-12">

    <h2 class="text-5xl font-bold text-white tracking-wide mb-10">Fight Card</h2>

    <div class="space-y-10">

        <?php if ($fights && $fights->num_rows > 0): ?>
            <?php while ($fight = $fights->fetch_assoc()): ?>

                <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-8 shadow-lg">

                    <div class="flex flex-col md:flex-row items-center justify-between gap-10">

                        <div class="flex flex-col items-center">
                            <img src="<?= htmlspecialchars($fight['fighter1_image']) ?>" 
                                 class="w-40 h-40 object-cover rounded-full border-2 border-red-600">
                            <p class="text-3xl font-bold mt-4 text-white"><?= htmlspecialchars($fight['fighter1_name']) ?></p>
                        </div>

                        <span class="text-5xl font-bold text-white">VS</span>

                        <div class="flex flex-col items-center">
                            <img src="<?= htmlspecialchars($fight['fighter2_image']) ?>" 
                                 class="w-40 h-40 object-cover rounded-full border-2 border-red-600">
                            <p class="text-3xl font-bold mt-4 text-white"><?= htmlspecialchars($fight['fighter2_name']) ?></p>
                        </div>

                    </div>

                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-neutral-400">Ainda não há lutas registadas para este evento.</p>
        <?php endif; ?>

    </div>

    <div class="mt-10">
        <a href="eventos.php" class="text-red-500 hover:text-red-400 text-sm uppercase tracking-[0.25em]">
            ← Voltar aos eventos
        </a>
    </div>
</section>

</body>
=======
﻿<?php
session_start();
include "db.php";
include "security.php";

if (!isset($_GET['id'])) {
    die("Evento não encontrado.");
}

$event_id = (int) $_GET['id'];

$stmtEvent = $conn->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
$stmtEvent->bind_param("i", $event_id);
$stmtEvent->execute();
$res_event = $stmtEvent->get_result();

if (!$res_event || $res_event->num_rows === 0) {
    die("Evento não existe.");
}
$event = $res_event->fetch_assoc();

$stmtFights = $conn->prepare("SELECT * FROM event_fights WHERE event_id = ? ORDER BY fight_order ASC");
$stmtFights->bind_param("i", $event_id);
$stmtFights->execute();
$fights = $stmtFights->get_result();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($event['name']) ?> | MMA 360</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Teko', sans-serif; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css"></head>

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
            <li><a href="fighters.php" class="hover:text-red-500 transition">Lutadores</a></li>
            <li><a href="eventos.php" class="text-red-500">Eventos</a></li>
            <li><a href="contacto.php" class="hover:text-red-500 transition">Contacto</a></li>
            <li><a href="login.php" class="hover:text-red-500 transition">Login</a></li>
        </ul>
    </div>
</nav>

<div class="pt-24"></div>

<?php $banner = !empty($event['banner']) ? $event['banner'] : 'uploads/default_banner.webp'; ?>
<div class="w-full h-64 md:h-80 bg-cover bg-center border-b border-neutral-700"
     style="background-image: linear-gradient(to top, rgba(0,0,0,0.75), rgba(0,0,0,0.3)), url('<?= e($banner) ?>');">
    <div class="max-w-5xl mx-auto h-full flex flex-col justify-end px-6 pb-6">
        <h1 class="text-6xl font-bold text-white tracking-wide"><?= e($event['name']) ?></h1>
        <p class="text-neutral-300 text-lg mt-2">
            <?= e(date("d/m/Y", strtotime($event['date']))) ?>  •  <?= e($event['location']) ?>
        </p>

        <?php if (isset($_SESSION['user_id'])): ?>
        <form method="POST" action="toggle_favorite.php">
            <?= csrf_field(); ?>
            <input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>">
            <button class="mt-4 bg-red-600 px-6 py-3 rounded-lg hover:bg-red-700 transition">Adicionar aos Favoritos</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<section class="max-w-5xl mx-auto px-6 py-12">
    <h2 class="text-5xl font-bold text-white tracking-wide mb-10">Fight Card</h2>

    <div class="space-y-10">
        <?php if ($fights && $fights->num_rows > 0): ?>
            <?php while ($fight = $fights->fetch_assoc()): ?>
                <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-8 shadow-lg">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-10">
                        <div class="flex flex-col items-center">
                            <img src="<?= e($fight['fighter1_image']) ?>" class="w-40 h-40 object-cover rounded-full border-2 border-red-600" alt="<?= e($fight['fighter1_name']) ?>">
                            <p class="text-3xl font-bold mt-4 text-white"><?= e($fight['fighter1_name']) ?></p>
                        </div>

                        <span class="text-5xl font-bold text-white">VS</span>

                        <div class="flex flex-col items-center">
                            <img src="<?= e($fight['fighter2_image']) ?>" class="w-40 h-40 object-cover rounded-full border-2 border-red-600" alt="<?= e($fight['fighter2_name']) ?>">
                            <p class="text-3xl font-bold mt-4 text-white"><?= e($fight['fighter2_name']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-neutral-400">Ainda não há lutas registadas para este evento.</p>
        <?php endif; ?>
    </div>

    <div class="mt-10">
        <a href="eventos.php" class="text-red-500 hover:text-red-400 text-sm uppercase tracking-[0.25em]">Voltar aos eventos</a>
    </div>
</section>

</body>
>>>>>>> bb0e1c37f01ca30bb9c897503cc0cf8c0a0a5224
</html>

