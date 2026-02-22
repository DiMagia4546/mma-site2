<?php
session_start();
include "db.php";

if (!isset($_GET['id'])) {
    die("Lutador não encontrado.");
}

$fighter_id = intval($_GET['id']);

$sql = "SELECT * FROM fighters WHERE id = $fighter_id";
$res = $conn->query($sql);

if (!$res || $res->num_rows === 0) {
    die("Lutador não existe.");
}

$f = $res->fetch_assoc();

$sql_history = "SELECT * FROM fighter_history WHERE fighter_id = $fighter_id ORDER BY fight_date DESC";
$history = $conn->query($sql_history);

// Lógica temporária para decidir se é campeão
$isChampion = ($f['wins'] > 20 && ($f['wins'] - $f['losses']) > 10);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $f['name'] ?> | MMA 360</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Teko', sans-serif; }
    </style>
</head>

<body class="bg-neutral-900 text-neutral-100">

<!-- NAVBAR ESCURA -->
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

<div class="pt-28"></div>

<!-- HEADER -->
<div class="max-w-6xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">

    <!-- FOTO GRANDE -->
    <div class="relative">
        <img src="<?= $f['image'] ?>" class="w-full h-[450px] object-cover rounded-xl grayscale">

        <?php if ($isChampion): ?>
            <span class="absolute top-4 left-4 bg-red-600 text-white px-4 py-1 rounded-full text-xl font-bold">
                CAMPEÃO
            </span>
        <?php endif; ?>
    </div>

    <!-- INFO PRINCIPAL -->
    <div>
        <p class="text-red-500 text-2xl uppercase tracking-wide"><?= $f['weight_class'] ?></p>

        <h1 class="text-6xl font-bold mt-2 text-white"><?= $f['name'] ?></h1>

        <?php if (!empty($f['nickname'])): ?>
            <p class="text-2xl text-neutral-400 mb-4">"<?= $f['nickname'] ?>"</p>
        <?php endif; ?>

        <p class="text-lg text-neutral-300 mb-6"><?= $f['nationality'] ?></p>

        <!-- RECORD -->
        <div class="grid grid-cols-3 gap-4 mb-10">
            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-4 text-center shadow">
                <p class="text-4xl font-bold text-white"><?= $f['wins'] ?></p>
                <p class="text-neutral-400">Vitórias</p>
            </div>

            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-4 text-center shadow">
                <p class="text-4xl font-bold text-white"><?= $f['losses'] ?></p>
                <p class="text-neutral-400">Derrotas</p>
            </div>

            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-4 text-center shadow">
                <p class="text-4xl font-bold text-white">0</p>
                <p class="text-neutral-400">Empates</p>
            </div>
        </div>

        <!-- ESTATÍSTICAS FÍSICAS -->
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-4 shadow">
                <p class="text-xl font-bold text-white"><?= $f['age'] ?> anos</p>
                <p class="text-neutral-400">Idade</p>
            </div>

            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-4 shadow">
                <p class="text-xl font-bold text-white"><?= $f['height'] ?> m</p>
                <p class="text-neutral-400">Altura</p>
            </div>

            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-4 shadow">
                <p class="text-xl font-bold text-white"><?= $f['reach'] ?> cm</p>
                <p class="text-neutral-400">Alcance</p>
            </div>

            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-4 shadow">
                <p class="text-xl font-bold text-white">96%</p>
                <p class="text-neutral-400">Eficácia</p>
            </div>
        </div>
    </div>

</div>

<!-- HISTÓRICO -->
<section class="max-w-6xl mx-auto px-6 py-16">
    <h2 class="text-4xl font-bold mb-6 text-white">Histórico de Lutas</h2>

    <?php if ($history && $history->num_rows > 0): ?>
        <div class="space-y-6">
            <?php while ($h = $history->fetch_assoc()): ?>
                <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-6 flex items-center justify-between shadow">

                    <div class="flex items-center gap-6">

                        <?php if (!empty($h['opponent_image'])): ?>
                            <img src="<?= $h['opponent_image'] ?>" class="w-20 h-20 rounded-full border-2 border-red-600 object-cover">
                        <?php endif; ?>

                        <div>
                            <p class="text-xl font-bold text-white"><?= $f['name'] ?> vs <?= $h['opponent_name'] ?></p>
                            <p class="text-neutral-400"><?= $h['event_name'] ?> — <?= date("d/m/Y", strtotime($h['fight_date'])) ?></p>
                            <p class="text-neutral-500 text-sm"><?= $h['method'] ?> • Round <?= $h['round_number'] ?> • <?= $h['time'] ?></p>
                        </div>
                    </div>

                    <span class="text-2xl font-bold 
                        <?= $h['result'] == 'Win' ? 'text-green-400' : ($h['result'] == 'Loss' ? 'text-red-400' : 'text-yellow-400') ?>">
                        <?= strtoupper($h['result']) ?>
                    </span>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-neutral-400">Ainda não há histórico registado.</p>
    <?php endif; ?>

    <div class="mt-10">
        <a href="fighters.php" class="text-red-500 hover:text-red-400 text-sm uppercase tracking-[0.25em]">
            ← Voltar aos Lutadores
        </a>
    </div>

</section>

</body>
</html>
