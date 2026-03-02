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
$role = $stmtRole->get_result()->fetch_assoc()['role'] ?? 'user';

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
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css"></head>

<body class="bg-neutral-900 text-neutral-100">

<div class="pt-20 max-w-6xl mx-auto px-6">

    <h1 class="text-6xl font-bold text-red-500 mb-10">GERIR LUTADORES</h1>

    <a href="admin_panel.php" class="text-red-500 hover:text-red-400 text-sm uppercase tracking-[0.25em]">Voltar ao Painel Admin</a>

    <div class="mt-10 bg-neutral-800 border border-neutral-700 rounded-xl p-8 overflow-x-auto">

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
                <td class="py-3"><?= e($f['name']) ?></td>
                <td><?= e($f['weight_class']) ?></td>
                <td><?= (int) $f['wins'] ?></td>
                <td><?= (int) $f['losses'] ?></td>
                <td class="flex items-center gap-2 py-2">
                    <a href="edit_fighter.php?id=<?= (int) $f['id'] ?>" class="text-yellow-400 hover:text-yellow-300">Editar</a>
                    <form method="POST" action="delete_fighter.php" onsubmit="return confirm('Eliminar lutador?');">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                        <button class="text-red-500 hover:text-red-400">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>

        </table>

    </div>

</div>

</body>
</html>

