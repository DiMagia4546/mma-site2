<?php
session_start();
include "db.php";
include "security.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$stmtRole = $conn->prepare("SELECT role FROM users WHERE id=? LIMIT 1");
$stmtRole->bind_param("i", $user_id);
$stmtRole->execute();
$roleRow = $stmtRole->get_result()->fetch_assoc();
$role = $roleRow['role'] ?? 'user';

if ($role !== 'admin') {
    die("Acesso negado.");
}

$total_fighters = (int) ($conn->query("SELECT COUNT(*) AS total FROM fighters")->fetch_assoc()['total'] ?? 0);
$total_events = (int) ($conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc()['total'] ?? 0);
$total_users = (int) ($conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0);
$total_fights = (int) ($conn->query("SELECT COUNT(*) AS total FROM event_fights")->fetch_assoc()['total'] ?? 0);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin | MMA 360</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2 { font-family: 'Teko', sans-serif; }
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
            <li><a href="dashboard.php" class="hover:text-red-500 transition">Painel Utilizador</a></li>
            <li><a href="fighters.php" class="hover:text-red-500 transition">Lutadores</a></li>
            <li><a href="eventos.php" class="hover:text-red-500 transition">Eventos</a></li>
            <li><a href="logout.php" class="text-red-500">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="pt-24 max-w-6xl mx-auto px-6">

    <h1 class="text-6xl font-bold text-red-500 mb-8">PAINEL ADMIN</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <div class="bg-neutral-800 p-6 rounded-xl border border-neutral-700">
            <p class="text-5xl font-bold text-red-500"><?= $total_fighters ?></p>
            <p class="text-neutral-400">Lutadores</p>
        </div>

        <div class="bg-neutral-800 p-6 rounded-xl border border-neutral-700">
            <p class="text-5xl font-bold text-red-500"><?= $total_events ?></p>
            <p class="text-neutral-400">Eventos</p>
        </div>

        <div class="bg-neutral-800 p-6 rounded-xl border border-neutral-700">
            <p class="text-5xl font-bold text-red-500"><?= $total_users ?></p>
            <p class="text-neutral-400">Utilizadores</p>
        </div>

        <div class="bg-neutral-800 p-6 rounded-xl border border-neutral-700">
            <p class="text-5xl font-bold text-red-500"><?= $total_fights ?></p>
            <p class="text-neutral-400">Lutas</p>
        </div>
    </div>

    <h2 class="text-4xl font-bold mb-4">Gestão</h2>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <a href="admin_fighters.php" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700 hover:bg-neutral-700 transition">
            <p class="text-3xl font-bold">Lutadores</p>
            <p class="text-neutral-400 text-sm mt-2">Ver, editar, eliminar e criar novos lutadores.</p>
        </a>

        <a href="admin_events.php" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700 hover:bg-neutral-700 transition">
            <p class="text-3xl font-bold">Eventos</p>
            <p class="text-neutral-400 text-sm mt-2">Gerir eventos e respetivas lutas.</p>
        </a>

        <a href="admin_users.php" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700 hover:bg-neutral-700 transition">
            <p class="text-3xl font-bold">Utilizadores</p>
            <p class="text-neutral-400 text-sm mt-2">Gerir contas, roles e acessos.</p>
        </a>

        <a href="admin_news.php" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700 hover:bg-neutral-700 transition">
            <p class="text-3xl font-bold">Notícias</p>
            <p class="text-neutral-400 text-sm mt-2">Criar e acompanhar notícias da plataforma.</p>
        </a>
    </div>

    <h2 class="text-4xl font-bold mb-4">Criação Rápida</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <a href="add_fighter.php" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700 hover:bg-neutral-700 transition">
            <p class="text-2xl font-bold">Novo Lutador</p>
            <p class="text-neutral-400 text-sm mt-2">Adicionar um novo atleta ao roster.</p>
        </a>

        <a href="add_event.php" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700 hover:bg-neutral-700 transition">
            <p class="text-2xl font-bold">Novo Evento</p>
            <p class="text-neutral-400 text-sm mt-2">Criar um novo evento com fight card.</p>
        </a>

        <a href="admin_news.php" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700 hover:bg-neutral-700 transition">
            <p class="text-2xl font-bold">Nova Notícia</p>
            <p class="text-neutral-400 text-sm mt-2">Publicar artigo com imagem para a homepage editorial.</p>
        </a>
    </div>

    <div class="mt-6">
        <a href="dashboard.php" class="text-red-500 hover:text-red-400 text-sm uppercase tracking-[0.25em]">Voltar ao Painel do Utilizador</a>
    </div>

</div>

</body>
</html>

