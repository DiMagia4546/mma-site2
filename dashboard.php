<?php
session_start();
include "db.php";
include "activity.php"; // para registar ações (se ainda não criaste, digo-te já como fazer)

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Buscar dados do utilizador
$sql = "SELECT * FROM users WHERE id = $user_id";
$res = $conn->query($sql);
$user = $res->fetch_assoc();

// Buscar favoritos (lutadores)
$sql_fav_fighters = "
    SELECT f.* FROM user_favorites uf
    JOIN fighters f ON uf.fighter_id = f.id
    WHERE uf.user_id = $user_id AND uf.fighter_id IS NOT NULL
";
$fav_fighters = $conn->query($sql_fav_fighters);

// Buscar favoritos (eventos)
$sql_fav_events = "
    SELECT e.* FROM user_favorites uf
    JOIN events e ON uf.event_id = e.id
    WHERE uf.user_id = $user_id AND uf.event_id IS NOT NULL
";
$fav_events = $conn->query($sql_fav_events);

// Buscar atividade recente
$sql_activity = "
    SELECT * FROM user_activity 
    WHERE user_id = $user_id 
    ORDER BY created_at DESC 
    LIMIT 10
";
$activity = $conn->query($sql_activity);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Utilizador | MMA 360</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Teko', sans-serif; }
    </style>
</head>

<body class="bg-neutral-900 text-neutral-100">

<!-- NAVBAR -->
<nav class="fixed top-0 w-full z-40 bg-neutral-900/70 backdrop-blur border-b border-neutral-700">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-3">
            <img src="pf-removebg-preview.png" class="h-10">
            <span class="text-xl font-semibold tracking-widest text-red-500">MMA 360</span>
        </a>

        <ul class="hidden md:flex gap-8 text-sm uppercase tracking-wide">
            <li><a href="fighters.php" class="hover:text-red-500 transition">Lutadores</a></li>
            <li><a href="eventos.php" class="hover:text-red-500 transition">Eventos</a></li>
            <li><a href="logout.php" class="text-red-500">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="pt-28"></div>

<section class="max-w-6xl mx-auto px-6">

    <!-- TÍTULO -->
    <h1 class="text-6xl font-bold text-red-500 tracking-widest mb-10">PAINEL DO UTILIZADOR</h1>

    <!-- PERFIL -->
    <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-8 shadow-xl mb-12">

        <div class="flex items-center gap-6 mb-10">
            <img src="<?= $user['profile_pic'] ?: 'uploads/default_user.png' ?>"
                 class="w-28 h-28 rounded-full object-cover border-2 border-red-600">

            <div>
                <h2 class="text-4xl font-bold"><?= $user['name'] ?></h2>
                <p class="text-neutral-400"><?= $user['email'] ?></p>

                <?php if ($user['role'] === 'admin'): ?>
                    <span class="inline-block mt-2 bg-red-600 text-white px-3 py-1 rounded-full text-sm font-bold">
                        ADMINISTRADOR
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- INFO -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="bg-neutral-900 border border-neutral-700 rounded-xl p-6">
                <p class="text-neutral-400 text-sm">Nome</p>
                <p class="text-2xl font-bold"><?= $user['name'] ?></p>
            </div>

            <div class="bg-neutral-900 border border-neutral-700 rounded-xl p-6">
                <p class="text-neutral-400 text-sm">Email</p>
                <p class="text-2xl font-bold"><?= $user['email'] ?></p>
            </div>

            <div class="bg-neutral-900 border border-neutral-700 rounded-xl p-6">
                <p class="text-neutral-400 text-sm">Conta criada em</p>
                <p class="text-xl font-bold"><?= date("d/m/Y", strtotime($user['created_at'])) ?></p>
            </div>

        </div>

        <!-- BOTÕES -->
        <div class="mt-10 flex gap-6">
            <a href="editar_perfil.php" class="bg-red-600 px-6 py-3 rounded-lg hover:bg-red-700 transition">
                Editar Perfil
            </a>

            <a href="alterar_password.php" class="bg-neutral-700 px-6 py-3 rounded-lg hover:bg-neutral-600 transition">
                Alterar Password
            </a>

            <?php if ($user['role'] === 'admin'): ?>
                <a href="admin_panel.php" class="bg-yellow-600 px-6 py-3 rounded-lg hover:bg-yellow-700 transition">
                    Painel Admin
                </a>
            <?php endif; ?>
        </div>

    </div>

    <!-- FAVORITOS -->
    <h2 class="text-4xl font-bold mb-4">Favoritos</h2>

    <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-8 shadow-xl mb-12">

        <h3 class="text-3xl font-bold mb-4">Lutadores</h3>

        <?php if ($fav_fighters->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php while ($f = $fav_fighters->fetch_assoc()): ?>
                    <a href="fighter.php?id=<?= $f['id'] ?>" class="bg-neutral-900 border border-neutral-700 rounded-xl p-4 hover:bg-neutral-700 transition">
                        <img src="<?= $f['image'] ?>" class="w-full h-40 object-cover rounded-xl mb-3">
                        <p class="text-xl font-bold"><?= $f['name'] ?></p>
                        <p class="text-neutral-400"><?= $f['weight_class'] ?></p>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-neutral-500">Ainda não tens lutadores favoritos.</p>
        <?php endif; ?>

        <h3 class="text-3xl font-bold mt-10 mb-4">Eventos</h3>

        <?php if ($fav_events->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php while ($e = $fav_events->fetch_assoc()): ?>
                    <a href="evento.php?id=<?= $e['id'] ?>" class="bg-neutral-900 border border-neutral-700 rounded-xl p-4 hover:bg-neutral-700 transition">
                        <p class="text-2xl font-bold"><?= $e['name'] ?></p>
                        <p class="text-neutral-400"><?= date("d/m/Y", strtotime($e['date'])) ?></p>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-neutral-500">Ainda não tens eventos favoritos.</p>
        <?php endif; ?>

    </div>

    <!-- ATIVIDADE RECENTE -->
    <h2 class="text-4xl font-bold mb-4">Atividade Recente</h2>

    <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-8 shadow-xl mb-12">

        <?php if ($activity->num_rows > 0): ?>
            <ul class="space-y-4">
                <?php while ($a = $activity->fetch_assoc()): ?>
                    <li class="bg-neutral-900 border border-neutral-700 rounded-xl p-4">
                        <p class="text-neutral-300"><?= $a['action'] ?></p>
                        <p class="text-neutral-500 text-sm"><?= date("d/m/Y H:i", strtotime($a['created_at'])) ?></p>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="text-neutral-500">Sem atividade registada.</p>
        <?php endif; ?>

    </div>

</section>

</body>
</html>
