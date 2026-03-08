<?php
session_start();
include "db.php";
include "security.php";
include "navbar.php";
include "favorites_helper.php";
require_login();

if (!isset($_GET["id"])) {
    die("Lutador nao encontrado.");
}

$fighter_id = (int) $_GET["id"];

$stmt = $conn->prepare("SELECT * FROM fighters WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $fighter_id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

if (!$res || $res->num_rows === 0) {
    die("Lutador nao existe.");
}

$f = $res->fetch_assoc();

$stmtHistory = $conn->prepare("SELECT * FROM fighter_history WHERE fighter_id = ? ORDER BY fight_date DESC");
$stmtHistory->bind_param("i", $fighter_id);
$stmtHistory->execute();
$history = $stmtHistory->get_result();
$stmtHistory->close();

$isChampion = ((int) $f["wins"] > 20 && ((int) $f["wins"] - (int) $f["losses"]) > 10);
$isFavorite = false;
if (isset($_SESSION["user_id"])) {
    $favTable = favoritesTable($conn);
    $user_id = (int) $_SESSION["user_id"];
    $stmtFav = $conn->prepare("SELECT id FROM {$favTable} WHERE user_id = ? AND fighter_id = ? LIMIT 1");
    $stmtFav->bind_param("ii", $user_id, $fighter_id);
    $stmtFav->execute();
    $isFavorite = (bool) $stmtFav->get_result()->fetch_assoc();
    $stmtFav->close();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($f["name"]) ?> | MMA 360</title>

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

<?php render_main_nav('fighters'); ?>

<div class="pt-28"></div>

<div class="max-w-6xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">

    <div class="relative">
        <img src="<?= e($f["image"]) ?>" class="w-full h-[450px] object-cover rounded-xl grayscale" alt="<?= e($f["name"]) ?>">

        <?php if ($isChampion): ?>
            <span class="absolute top-4 left-4 bg-red-600 text-white px-4 py-1 rounded-full text-xl font-bold">CAMPEAO</span>
        <?php endif; ?>
    </div>

    <div>
        <p class="text-red-500 text-2xl uppercase tracking-wide"><?= e($f["weight_class"]) ?></p>

        <h1 class="text-6xl font-bold mt-2"><?= e($f["name"]) ?></h1>

        <?php if (!empty($f["nickname"])): ?>
            <p class="text-2xl text-neutral-400 mb-4">"<?= e($f["nickname"]) ?>"</p>
        <?php endif; ?>

        <p class="text-lg text-neutral-300 mb-6"><?= e($f["nationality"]) ?></p>

        <?php if (isset($_SESSION["user_id"])): ?>
        <form method="POST" action="toggle_favorite.php">
            <?= csrf_field(); ?>
            <input type="hidden" name="fighter_id" value="<?= (int) $f["id"] ?>">
            <?php if ($isFavorite): ?>
                <button class="mt-6 bg-neutral-700 px-6 py-3 rounded-lg hover:bg-neutral-600 transition">Remover dos Favoritos</button>
            <?php else: ?>
                <button class="mt-6 bg-red-600 px-6 py-3 rounded-lg hover:bg-red-700 transition">Adicionar aos Favoritos</button>
            <?php endif; ?>
        </form>
        <?php endif; ?>

        <div class="grid grid-cols-3 gap-4 mb-10 mt-10">
            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-4 text-center shadow">
                <p class="text-4xl font-bold"><?= (int) $f["wins"] ?></p>
                <p class="text-neutral-400">Vitorias</p>
            </div>

            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-4 text-center shadow">
                <p class="text-4xl font-bold"><?= (int) $f["losses"] ?></p>
                <p class="text-neutral-400">Derrotas</p>
            </div>

            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-4 text-center shadow">
                <p class="text-4xl font-bold">0</p>
                <p class="text-neutral-400">Empates</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-4 shadow">
                <p class="text-xl font-bold"><?= (int) $f["age"] ?> anos</p>
                <p class="text-neutral-400">Idade</p>
            </div>

            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-4 shadow">
                <p class="text-xl font-bold"><?= e($f["height"]) ?> m</p>
                <p class="text-neutral-400">Altura</p>
            </div>

            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-4 shadow">
                <p class="text-xl font-bold"><?= e($f["reach"]) ?> cm</p>
                <p class="text-neutral-400">Alcance</p>
            </div>

            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-4 shadow">
                <p class="text-xl font-bold">96%</p>
                <p class="text-neutral-400">Eficacia</p>
            </div>
        </div>
    </div>

</div>

<section class="max-w-6xl mx-auto px-6 py-16">
    <h2 class="text-4xl font-bold mb-6">Historico de Lutas</h2>

    <?php if ($history && $history->num_rows > 0): ?>
        <div class="space-y-6">
            <?php while ($h = $history->fetch_assoc()): ?>
                <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-6 flex items-center justify-between shadow">
                    <div class="flex items-center gap-6">
                        <?php if (!empty($h["opponent_image"])): ?>
                            <img src="<?= e($h["opponent_image"]) ?>" class="w-20 h-20 rounded-full border-2 border-red-600 object-cover" alt="<?= e($h["opponent_name"]) ?>">
                        <?php endif; ?>

                        <div>
                            <p class="text-xl font-bold"><?= e($f["name"]) ?> vs <?= e($h["opponent_name"]) ?></p>
                            <p class="text-neutral-400"><?= e($h["event_name"]) ?> - <?= e(date("d/m/Y", strtotime($h["fight_date"]))) ?></p>
                            <p class="text-neutral-500 text-sm"><?= e($h["method"]) ?> - Round <?= (int) $h["round_number"] ?> - <?= e($h["time"]) ?></p>
                        </div>
                    </div>

                    <span class="text-2xl font-bold <?= $h["result"] === "Win" ? "text-green-400" : ($h["result"] === "Loss" ? "text-red-400" : "text-yellow-400") ?>">
                        <?= e(strtoupper($h["result"])) ?>
                    </span>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-neutral-400">Ainda nao ha historico registado.</p>
    <?php endif; ?>

    <div class="mt-10">
        <a href="fighters.php" class="text-red-500 hover:text-red-400 text-sm uppercase tracking-[0.25em]">Voltar aos Lutadores</a>
    </div>
</section>

</body>
</html>




