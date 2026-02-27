<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) exit("Acesso negado.");

$user_id = $_SESSION['user_id'];
$role = $conn->query("SELECT role FROM users WHERE id=$user_id")->fetch_assoc()['role'];
if ($role !== 'admin') exit("Acesso negado.");

if (!isset($_GET['event_id'])) exit("Evento inválido.");

$event_id = intval($_GET['event_id']);

$event = $conn->query("SELECT * FROM events WHERE id=$event_id")->fetch_assoc();
$fighters = $conn->query("SELECT * FROM fighters ORDER BY name ASC");
$fights = $conn->query("SELECT * FROM event_fights WHERE event_id=$event_id ORDER BY fight_order ASC");

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fighter1 = $_POST['fighter1'];
    $fighter2 = $_POST['fighter2'];
    $order = $_POST['fight_order'];

    if ($fighter1 == $fighter2) {
        $error = "Os lutadores não podem ser iguais.";
    } else {
        $stmt = $conn->prepare("INSERT INTO event_fights (event_id, fighter1_name, fighter2_name, fight_order) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $event_id, $fighter1, $fighter2, $order);

        if ($stmt->execute()) $success = "Luta adicionada!";
        else $error = "Erro ao adicionar luta.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Gerir Lutas</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 text-neutral-100">

<div class="pt-20 max-w-4xl mx-auto px-6">

<h1 class="text-5xl font-bold text-red-500 mb-6">Gerir Lutas — <?= $event['name'] ?></h1>

<?php if ($success): ?><p class="bg-green-600 p-3 rounded mb-4"><?= $success ?></p><?php endif; ?>
<?php if ($error): ?><p class="bg-red-600 p-3 rounded mb-4"><?= $error ?></p><?php endif; ?>

<h2 class="text-3xl font-bold mb-4">Adicionar Luta</h2>

<form method="POST" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700 mb-10">

<label>Lutador 1</label>
<select name="fighter1" class="w-full bg-neutral-900 p-2 rounded mb-4">
<?php while ($f = $fighters->fetch_assoc()): ?>
<option value="<?= $f['name'] ?>"><?= $f['name'] ?></option>
<?php endwhile; ?>
</select>

<label>Lutador 2</label>
<select name="fighter2" class="w-full bg-neutral-900 p-2 rounded mb-4">
<?php
$fighters2 = $conn->query("SELECT * FROM fighters ORDER BY name ASC");
while ($f2 = $fighters2->fetch_assoc()):
?>
<option value="<?= $f2['name'] ?>"><?= $f2['name'] ?></option>
<?php endwhile; ?>
</select>

<label>Ordem da Luta</label>
<input type="number" name="fight_order" class="w-full bg-neutral-900 p-2 rounded mb-4">

<button class="bg-red-600 px-6 py-3 rounded hover:bg-red-700">Adicionar</button>

</form>

<h2 class="text-3xl font-bold mb-4">Lutas Existentes</h2>

<div class="bg-neutral-800 p-6 rounded-xl border border-neutral-700">

<?php while ($fight = $fights->fetch_assoc()): ?>
<div class="border-b border-neutral-700 py-4 flex justify-between">
    <p><?= $fight['fighter1_name'] ?> vs <?= $fight['fighter2_name'] ?> (Ordem: <?= $fight['fight_order'] ?>)</p>
    <a href="delete_fight.php?id=<?= $fight['id'] ?>&event_id=<?= $event_id ?>" class="text-red-500">Eliminar</a>
</div>
<?php endwhile; ?>

</div>

<a href="admin_events.php" class="text-red-500 mt-6 inline-block">← Voltar</a>

</div>
</body>
</html>
