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

// Allow a dedicated profile image without breaking existing databases.
$hasProfileImage = false;
$checkProfileImage = $conn->query("SHOW COLUMNS FROM fighters LIKE 'profile_image'");
if ($checkProfileImage && $checkProfileImage->num_rows > 0) {
    $hasProfileImage = true;
} else {
    $conn->query("ALTER TABLE fighters ADD COLUMN profile_image VARCHAR(255) NULL AFTER image");
    $hasProfileImage = true;
}

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
    $isChampion = isset($_POST['is_champion']) ? 1 : 0;

    $errors = [];
    $image = uploadImage($_FILES['image'] ?? [], 'fighter', 'uploads/default_fighter.webp', $errors);
    $profileImage = uploadImage($_FILES['profile_image'] ?? [], 'fighter_profile', '', $errors);
    if ($profileImage === '') {
        $profileImage = $image;
    }

    if ($name === '' || $weight === '' || $nationality === '') {
        $errors[] = 'Preenche os campos obrigatórios.';
    }

    if (empty($errors)) {
        if ($hasProfileImage) {
            $stmt = $conn->prepare("INSERT INTO fighters (name, weight_class, wins, losses, age, height, reach, nationality, image, profile_image, is_champion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiiisssssi", $name, $weight, $wins, $losses, $age, $height, $reach, $nationality, $image, $profileImage, $isChampion);
        } else {
            $stmt = $conn->prepare("INSERT INTO fighters (name, weight_class, wins, losses, age, height, reach, nationality, image, is_champion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiiissssi", $name, $weight, $wins, $losses, $age, $height, $reach, $nationality, $image, $isChampion);
        }

        if ($stmt->execute()) $success = "Lutador criado com sucesso!";
        else $error = "Erro ao criar lutador.";
    } else {
        $error = implode(' ', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Novo Lutador</title>
<script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css"></head>

<body class="bg-neutral-900 text-neutral-100">
<div class="pt-20 max-w-3xl mx-auto px-6">
<h1 class="text-5xl font-bold text-red-500 mb-6">Criar Novo Lutador</h1>
<?php if ($success): ?><p class="bg-green-600 p-3 rounded mb-4"><?= e($success) ?></p><?php endif; ?>
<?php if ($error): ?><p class="bg-red-600 p-3 rounded mb-4"><?= e($error) ?></p><?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700">
<?= csrf_field(); ?>
<label>Nome</label><input type="text" name="name" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Peso</label><input type="text" name="weight_class" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Vitórias</label><input type="number" name="wins" class="w-full bg-neutral-900 p-2 rounded mb-4" min="0" required>
<label>Derrotas</label><input type="number" name="losses" class="w-full bg-neutral-900 p-2 rounded mb-4" min="0" required>
<label>Idade</label><input type="number" name="age" class="w-full bg-neutral-900 p-2 rounded mb-4" min="0" required>
<label>Altura (m)</label><input type="text" name="height" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Alcance (cm)</label><input type="text" name="reach" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Nacionalidade</label><input type="text" name="nationality" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Imagem da Lista/Card</label><input type="file" name="image" accept="image/*" class="mb-4">
<label>Imagem do Perfil (opcional)</label><input type="file" name="profile_image" accept="image/*" class="mb-4">
<label class="flex items-center gap-2 mb-4"><input type="checkbox" name="is_champion" value="1"> Marcar como campeão</label>
<button class="bg-red-600 px-6 py-3 rounded hover:bg-red-700">Criar</button>
</form>

<a href="admin_fighters.php" class="text-red-500 mt-6 inline-block">Voltar</a>
</div>
</body>
</html>

