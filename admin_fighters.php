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

$fighters = $conn->query("SELECT * FROM fighters ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gerir Lutadores | Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 text-neutral-100">

<div class="pt-20 max-w-6xl mx-auto px-6">

    <h1 class="text-6xl font-bold text-red-500 mb-10">GERIR LUTADORES</h1>

    <a href="admin_panel.php" class="text-red-500 hover:text-red-400 text-sm uppercase tracking-[0.25em]">
        ← Voltar ao Painel Admin
    </a>

    <div class="mt-10 bg-neutral-800 border border-neutral-700 rounded-xl p-8">

        <table class="w-full text-left">
            <tr class="border-b border-neutral-700 text-red-500 text-xl">
                <th>Nome</th>
                <th>Peso</th>
                <th>Vitórias</th>
                <th>Derrotas</th>
                <th>Ações</th>
            </tr>

            <?php while ($f = $fighters->fetch_assoc()): ?>
            <tr class="border-b border-neutral-700">
                <td class="py-3"><?= $f['name'] ?></td>
                <td><?= $f['weight_class'] ?></td>
                <td><?= $f['wins'] ?></td>
                <td><?= $f['losses'] ?></td>
                <td>
                    <a href="edit_fighter.php?id=<?= $f['id'] ?>" class="text-yellow-400 hover:text-yellow-300">Editar</a> |
                    <a href="delete_fighter.php?id=<?= $f['id'] ?>" class="text-red-500 hover:text-red-400">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>

        </table>

    </div>

</div>

</body>
</html>
