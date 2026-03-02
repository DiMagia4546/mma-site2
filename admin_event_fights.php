<?php
require_once "auth.php";

$user_id = requireAdmin($conn);

if (!isset($_GET['event_id'])) exit("Evento inválido.");

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

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

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

<h1 class="text-5xl font-bold text-red-500 mb-6">Gerir Lutas — <?= htmlspecialchars($event['name']) ?></h1>

<?php if ($success): ?><p class="bg-green-600 p-3 rounded mb-4"><?= $success ?></p><?php endif; ?>
<?php if ($error): ?><p class="bg-red-600 p-3 rounded mb-4"><?= $error ?></p><?php endif; ?>

<h2 class="text-3xl font-bold mb-4">Adicionar Luta</h2>

<form method="POST" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700 mb-10">

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

<button class="bg-red-600 px-6 py-3 rounded hover:bg-red-700">Adicionar</button>

</form>

<h2 class="text-3xl font-bold mb-4">Lutas Existentes</h2>

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
</body>
</html>
