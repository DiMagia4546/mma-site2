<<<<<<< HEAD
<?php
require_once "auth.php";

$user_id = requireAdmin($conn);
=======
﻿<?php
session_start();
include "db.php";
include "security.php";

if (!isset($_SESSION['user_id'])) {
    exit("Acesso negado.");
}

$user_id = (int) $_SESSION['user_id'];
$stmtRole = $conn->prepare("SELECT role FROM users WHERE id=? LIMIT 1");
$stmtRole->bind_param("i", $user_id);
$stmtRole->execute();
$role = $stmtRole->get_result()->fetch_assoc()['role'] ?? 'user';
if ($role !== 'admin') {
    exit("Acesso negado.");
}
>>>>>>> bb0e1c37f01ca30bb9c897503cc0cf8c0a0a5224

if (!isset($_GET['event_id'])) {
    exit("Evento inválido.");
}

<<<<<<< HEAD
$event_id = (int)$_GET['event_id'];

$event_stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event = $event_stmt->get_result()->fetch_assoc();
$event_stmt->close();

$fighters = $conn->query("SELECT * FROM fighters ORDER BY name ASC");
$fights_stmt = $conn->prepare("SELECT * FROM event_fights WHERE event_id = ? ORDER BY fight_order ASC");
$fights_stmt->bind_param("i", $event_id);
$fights_stmt->execute();
$fights = $fights_stmt->get_result();
=======
$event_id = (int) $_GET['event_id'];
$stmtEvent = $conn->prepare("SELECT * FROM events WHERE id=? LIMIT 1");
$stmtEvent->bind_param("i", $event_id);
$stmtEvent->execute();
$event = $stmtEvent->get_result()->fetch_assoc();
if (!$event) {
    exit("Evento não encontrado.");
}
>>>>>>> bb0e1c37f01ca30bb9c897503cc0cf8c0a0a5224

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf_or_die();

<<<<<<< HEAD
    $fighter1_name = $_POST['fighter1'];
    $fighter2_name = $_POST['fighter2'];
    $order = (int)$_POST['fight_order'];

    if ($fighter1_name === $fighter2_name) {
        $error = "Os lutadores não podem ser iguais.";
    } else {

        // Buscar imagens dos lutadores
        $stmt_f1 = $conn->prepare("SELECT image FROM fighters WHERE name = ? LIMIT 1");
        $stmt_f1->bind_param("s", $fighter1_name);
        $stmt_f1->execute();
        $res_f1 = $stmt_f1->get_result();
        $fighter1_image = $res_f1->num_rows ? $res_f1->fetch_assoc()['image'] : 'uploads/default_fighter.png';
        $stmt_f1->close();

        $stmt_f2 = $conn->prepare("SELECT image FROM fighters WHERE name = ? LIMIT 1");
        $stmt_f2->bind_param("s", $fighter2_name);
        $stmt_f2->execute();
        $res_f2 = $stmt_f2->get_result();
        $fighter2_image = $res_f2->num_rows ? $res_f2->fetch_assoc()['image'] : 'uploads/default_fighter.png';
        $stmt_f2->close();

        $stmt = $conn->prepare("
            INSERT INTO event_fights (event_id, fighter1_name, fighter2_name, fighter1_image, fighter2_image, fight_order) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issssi", $event_id, $fighter1_name, $fighter2_name, $fighter1_image, $fighter2_image, $order);

        if ($stmt->execute()) $success = "Luta adicionada!";
        else $error = "Erro ao adicionar luta.";

        $stmt->close();
=======
    $fighter1 = (int) ($_POST['fighter1'] ?? 0);
    $fighter2 = (int) ($_POST['fighter2'] ?? 0);
    $order = (int) ($_POST['fight_order'] ?? 0);

    if ($fighter1 <= 0 || $fighter2 <= 0 || $order <= 0) {
        $error = "Preenche os campos corretamente.";
    } elseif ($fighter1 === $fighter2) {
        $error = "Os lutadores não podem ser iguais.";
    } else {
        $stmtF1 = $conn->prepare("SELECT name, image FROM fighters WHERE id=? LIMIT 1");
        $stmtF1->bind_param("i", $fighter1);
        $stmtF1->execute();
        $f1 = $stmtF1->get_result()->fetch_assoc();

        $stmtF2 = $conn->prepare("SELECT name, image FROM fighters WHERE id=? LIMIT 1");
        $stmtF2->bind_param("i", $fighter2);
        $stmtF2->execute();
        $f2 = $stmtF2->get_result()->fetch_assoc();

        if (!$f1 || !$f2) {
            $error = "Lutador inválido.";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO event_fights (event_id, fighter1_name, fighter2_name, fighter1_image, fighter2_image, fight_order)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("issssi", $event_id, $f1['name'], $f2['name'], $f1['image'], $f2['image'], $order);

            if ($stmt->execute()) {
                $success = "Luta adicionada com sucesso.";
            } else {
                $error = "Erro ao adicionar luta.";
            }
        }
>>>>>>> bb0e1c37f01ca30bb9c897503cc0cf8c0a0a5224
    }
}

$fightersResult = $conn->query("SELECT id, name FROM fighters ORDER BY name ASC");
$fighters = [];
while ($row = $fightersResult->fetch_assoc()) {
    $fighters[] = $row;
}

$stmtFights = $conn->prepare("SELECT * FROM event_fights WHERE event_id=? ORDER BY fight_order ASC, id ASC");
$stmtFights->bind_param("i", $event_id);
$stmtFights->execute();
$fights = $stmtFights->get_result();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gerir Lutas</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
body { font-family: 'Inter', sans-serif; }
h1, h2, h3 { font-family: 'Teko', sans-serif; }
</style>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css"></head>

<body class="bg-neutral-950 text-neutral-100 min-h-screen">
<nav class="sticky top-0 z-40 bg-neutral-900/80 backdrop-blur border-b border-neutral-800">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="admin_events.php" class="text-sm uppercase tracking-[0.25em] text-neutral-400 hover:text-red-400 transition">Voltar aos Eventos</a>
        <span class="text-xs uppercase tracking-[0.25em] text-neutral-500">Admin</span>
    </div>
</nav>

<main class="max-w-6xl mx-auto px-6 py-10">
    <div class="mb-8">
        <h1 class="text-5xl md:text-6xl text-red-500 leading-none">Gerir Lutas</h1>
        <p class="text-neutral-300 text-lg mt-2"><?= e($event['name']) ?> - <?= e(date("d/m/Y", strtotime($event['date']))) ?></p>
    </div>

<<<<<<< HEAD
<h1 class="text-5xl font-bold text-red-500 mb-6">Gerir Lutas — <?= htmlspecialchars($event['name']) ?></h1>
=======
    <?php if ($success): ?>
        <p class="bg-emerald-600/90 text-white px-4 py-3 rounded-lg mb-6 border border-emerald-500"><?= e($success) ?></p>
    <?php endif; ?>
>>>>>>> bb0e1c37f01ca30bb9c897503cc0cf8c0a0a5224

    <?php if ($error): ?>
        <p class="bg-red-600/90 text-white px-4 py-3 rounded-lg mb-6 border border-red-500"><?= e($error) ?></p>
    <?php endif; ?>

    <section class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-1 bg-neutral-900 border border-neutral-800 rounded-2xl p-6 shadow-xl">
            <h2 class="text-3xl mb-4">Adicionar Luta</h2>

            <form method="POST" class="space-y-4">
                <?= csrf_field(); ?>

<<<<<<< HEAD
<label>Lutador 1</label>
<select name="fighter1" class="w-full bg-neutral-900 p-2 rounded mb-4">
<?php while ($f = $fighters->fetch_assoc()): ?>
<option value="<?= htmlspecialchars($f['name']) ?>"><?= htmlspecialchars($f['name']) ?></option>
<?php endwhile; ?>
</select>

<?php
// recarregar lista para o segundo select
$fighters2 = $conn->query("SELECT * FROM fighters ORDER BY name ASC");
?>

<label>Lutador 2</label>
<select name="fighter2" class="w-full bg-neutral-900 p-2 rounded mb-4">
<?php while ($f2 = $fighters2->fetch_assoc()): ?>
<option value="<?= htmlspecialchars($f2['name']) ?>"><?= htmlspecialchars($f2['name']) ?></option>
<?php endwhile; ?>
</select>

<label>Ordem da Luta</label>
<input type="number" name="fight_order" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
=======
                <div>
                    <label class="block text-sm uppercase tracking-wide text-neutral-400 mb-1">Lutador 1</label>
                    <select name="fighter1" class="w-full bg-neutral-800 border border-neutral-700 rounded-lg px-3 py-2" required>
                        <?php foreach ($fighters as $f): ?>
                            <option value="<?= (int) $f['id'] ?>"><?= e($f['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm uppercase tracking-wide text-neutral-400 mb-1">Lutador 2</label>
                    <select name="fighter2" class="w-full bg-neutral-800 border border-neutral-700 rounded-lg px-3 py-2" required>
                        <?php foreach ($fighters as $f): ?>
                            <option value="<?= (int) $f['id'] ?>"><?= e($f['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm uppercase tracking-wide text-neutral-400 mb-1">Ordem</label>
                    <input type="number" name="fight_order" min="1" class="w-full bg-neutral-800 border border-neutral-700 rounded-lg px-3 py-2" required>
                </div>
>>>>>>> bb0e1c37f01ca30bb9c897503cc0cf8c0a0a5224

                <button class="w-full bg-red-600 px-6 py-3 rounded-lg hover:bg-red-700 transition font-semibold">Adicionar Luta</button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-neutral-900 border border-neutral-800 rounded-2xl p-6 shadow-xl">
            <h2 class="text-3xl mb-5">Fight Card Atual</h2>

            <?php if ($fights->num_rows === 0): ?>
                <div class="rounded-xl border border-dashed border-neutral-700 p-8 text-center text-neutral-400">
                    Ainda não há lutas registadas para este evento.
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php while ($fight = $fights->fetch_assoc()): ?>
                        <div class="bg-neutral-800 border border-neutral-700 rounded-xl px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <p class="text-lg font-semibold"><?= e($fight['fighter1_name']) ?> <span class="text-neutral-400">vs</span> <?= e($fight['fighter2_name']) ?></p>
                                <p class="text-sm text-neutral-400">Ordem da luta: <?= (int) $fight['fight_order'] ?></p>
                            </div>

<<<<<<< HEAD
<div class="bg-neutral-800 p-6 rounded-xl border border-neutral-700">

<?php while ($fight = $fights->fetch_assoc()): ?>
<div class="border-b border-neutral-700 py-4 flex justify-between">
    <p><?= htmlspecialchars($fight['fighter1_name']) ?> vs <?= htmlspecialchars($fight['fighter2_name']) ?> (Ordem: <?= (int)$fight['fight_order'] ?>)</p>
    <a href="delete_fight.php?id=<?= (int)$fight['id'] ?>&event_id=<?= $event_id ?>" class="text-red-500">Eliminar</a>
</div>
<?php endwhile; ?>

</div>

<a href="admin_events.php" class="text-red-500 mt-6 inline-block">← Voltar</a>

</div>
=======
                            <form method="POST" action="delete_fight.php" onsubmit="return confirm('Eliminar luta?');">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="id" value="<?= (int) $fight['id'] ?>">
                                <input type="hidden" name="event_id" value="<?= $event_id ?>">
                                <button class="bg-red-600/20 border border-red-500 text-red-400 px-4 py-2 rounded-lg hover:bg-red-600/30 transition">Eliminar</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
>>>>>>> bb0e1c37f01ca30bb9c897503cc0cf8c0a0a5224
</body>
</html>

