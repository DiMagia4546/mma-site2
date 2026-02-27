<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$res = $conn->query("SELECT role FROM users WHERE id=$user_id");
$role = $res->fetch_assoc()['role'];

if ($role !== 'admin') {
    die("Acesso negado.");
}

$events = $conn->query("SELECT * FROM events ORDER BY date DESC");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gerir Eventos | Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 text-neutral-100">

<div class="pt-20 max-w-6xl mx-auto px-6">

    <h1 class="text-6xl font-bold text-red-500 mb-10">GERIR EVENTOS</h1>

    <a href="admin_panel.php" class="text-red-500 hover:text-red-400 text-sm uppercase tracking-[0.25em]">
        ← Voltar ao Painel Admin
    </a>

    <div class="mt-10 bg-neutral-800 border border-neutral-700 rounded-xl p-8">

        <table class="w-full text-left">
            <tr class="border-b border-neutral-700 text-red-500 text-xl">
                <th>Nome</th>
                <th>Data</th>
                <th>Local</th>
                <th>Ações</th>
            </tr>

            <?php while ($e = $events->fetch_assoc()): ?>
            <tr class="border-b border-neutral-700">
                <td class="py-3"><?= $e['name'] ?></td>
                <td><?= date("d/m/Y", strtotime($e['date'])) ?></td>
                <td><?= $e['location'] ?></td>
                <td>
                    <a href="edit_event.php?id=<?= $e['id'] ?>" class="text-yellow-400 hover:text-yellow-300">Editar</a> |
                    <a href="delete_event.php?id=<?= $e['id'] ?>" class="text-red-500 hover:text-red-400">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>

        </table>

    </div>

</div>

</body>
</html>
