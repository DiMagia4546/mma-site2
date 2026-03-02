<?php
session_start();
include "db.php";
include "security.php";
include "upload_helper.php";

if (!isset($_SESSION['user_id'])) exit("Acesso negado.");

$user_id = (int) $_SESSION['user_id'];
$stmtRole = $conn->prepare("SELECT role FROM users WHERE id=? LIMIT 1");
$stmtRole->bind_param("i", $user_id);
$stmtRole->execute();
$role = $stmtRole->get_result()->fetch_assoc()['role'] ?? 'user';
if ($role !== 'admin') exit("Acesso negado.");

if (!isset($_GET['id'])) exit("Evento não encontrado.");

$id = (int) $_GET['id'];
$stmtGet = $conn->prepare("SELECT * FROM events WHERE id=? LIMIT 1");
$stmtGet->bind_param("i", $id);
$stmtGet->execute();
$e = $stmtGet->get_result()->fetch_assoc();
if (!$e) exit("Evento não encontrado.");

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf_or_die();

    $name = trim($_POST['name'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $main_event = trim($_POST['main_event'] ?? '');

    $errors = [];
    $banner = $e['banner'];
    if (!empty($_FILES['banner']['name'])) {
        $banner = uploadImage($_FILES['banner'], 'event', $e['banner'] ?: 'uploads/default_banner.webp', $errors);
    }

    if ($name === '' || $date === '' || $location === '' || $main_event === '') {
        $errors[] = 'Preenche os campos obrigatórios.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE events SET name=?, date=?, location=?, main_event=?, banner=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $date, $location, $main_event, $banner, $id);

        if ($stmt->execute()) {
            $success = "Evento atualizado!";
            $e = array_merge($e, compact('name', 'date', 'location', 'main_event', 'banner'));
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
<title>Editar Evento</title>
<script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css"></head>

<body class="bg-neutral-900 text-neutral-100">
<div class="pt-20 max-w-3xl mx-auto px-6">
<h1 class="text-5xl font-bold text-red-500 mb-6">Editar Evento</h1>
<?php if ($success): ?><p class="bg-green-600 p-3 rounded mb-4"><?= e($success) ?></p><?php endif; ?>
<?php if ($error): ?><p class="bg-red-600 p-3 rounded mb-4"><?= e($error) ?></p><?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700">
<?= csrf_field(); ?>
<label>Nome</label><input type="text" name="name" value="<?= e($e['name']) ?>" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Data</label><input type="date" name="date" value="<?= e($e['date']) ?>" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Local</label><input type="text" name="location" value="<?= e($e['location']) ?>" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Main Event</label><input type="text" name="main_event" value="<?= e($e['main_event']) ?>" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Banner</label><input type="file" name="banner" accept="image/*" class="mb-4">
<button class="bg-red-600 px-6 py-3 rounded hover:bg-red-700">Guardar</button>
</form>

<a href="admin_events.php" class="text-red-500 mt-6 inline-block">Voltar</a>
</div>
</body>
</html>

