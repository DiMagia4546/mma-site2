<?php
session_start();
include "db.php";
include "security.php";
include "upload_helper.php";

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = (int) $_SESSION['user_id'];
$stmtRole = $conn->prepare("SELECT role FROM users WHERE id=? LIMIT 1");
$stmtRole->bind_param("i", $user_id);
$stmtRole->execute();
$role = $stmtRole->get_result()->fetch_assoc()['role'] ?? 'user';
if ($role !== 'admin') die("Acesso negado.");

if (!isset($_GET['id'])) die("Lutador não encontrado.");
$id = (int) $_GET['id'];
$stmtGet = $conn->prepare("SELECT * FROM fighters WHERE id=? LIMIT 1");
$stmtGet->bind_param("i", $id);
$stmtGet->execute();
$f = $stmtGet->get_result()->fetch_assoc();
if (!$f) die("Lutador não encontrado.");

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf_or_die();

    $name = trim($_POST['name'] ?? '');
    $weight = trim($_POST['weight_class'] ?? '');
    $wins = (int) ($_POST['wins'] ?? 0);
    $losses = (int) ($_POST['losses'] ?? 0);
    $age = (int) ($_POST['age'] ?? 0);
    $height = trim($_POST['height'] ?? '');
    $reach = trim($_POST['reach'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');

    $errors = [];
    $image = $f['image'];
    if (!empty($_FILES['image']['name'])) {
        $image = uploadImage($_FILES['image'], 'fighter', $f['image'], $errors);
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE fighters SET name=?, weight_class=?, wins=?, losses=?, age=?, height=?, reach=?, nationality=?, image=? WHERE id=?");
        $stmt->bind_param("ssiiissssi", $name, $weight, $wins, $losses, $age, $height, $reach, $nationality, $image, $id);

        if ($stmt->execute()) {
            $success = "Lutador atualizado!";
            $f = array_merge($f, compact('name', 'weight', 'wins', 'losses', 'age', 'height', 'reach', 'nationality', 'image'));
            $f['weight_class'] = $weight;
        } else {
            $error = "Erro ao atualizar.";
        }
    } else {
        $error = implode(' ', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Editar Lutador</title>
<script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css"></head>

<body class="bg-neutral-900 text-neutral-100">
<div class="pt-20 max-w-3xl mx-auto px-6">
<h1 class="text-5xl font-bold text-red-500 mb-6">Editar Lutador</h1>
<?php if ($success): ?><p class="bg-green-600 p-3 rounded mb-4"><?= e($success) ?></p><?php endif; ?>
<?php if ($error): ?><p class="bg-red-600 p-3 rounded mb-4"><?= e($error) ?></p><?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700">
<?= csrf_field(); ?>
<label>Nome</label><input type="text" name="name" value="<?= e($f['name']) ?>" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Peso</label><input type="text" name="weight_class" value="<?= e($f['weight_class']) ?>" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Vitórias</label><input type="number" name="wins" value="<?= (int) $f['wins'] ?>" class="w-full bg-neutral-900 p-2 rounded mb-4" min="0" required>
<label>Derrotas</label><input type="number" name="losses" value="<?= (int) $f['losses'] ?>" class="w-full bg-neutral-900 p-2 rounded mb-4" min="0" required>
<label>Idade</label><input type="number" name="age" value="<?= (int) $f['age'] ?>" class="w-full bg-neutral-900 p-2 rounded mb-4" min="0" required>
<label>Altura (m)</label><input type="text" name="height" value="<?= e($f['height']) ?>" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Alcance (cm)</label><input type="text" name="reach" value="<?= e($f['reach']) ?>" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Nacionalidade</label><input type="text" name="nationality" value="<?= e($f['nationality']) ?>" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Imagem</label><input type="file" name="image" accept="image/*" class="mb-4">
<button class="bg-red-600 px-6 py-3 rounded hover:bg-red-700">Guardar</button>
</form>

<a href="admin_fighters.php" class="text-red-500 mt-6 inline-block">Voltar</a>
</div>
</body>
</html>

