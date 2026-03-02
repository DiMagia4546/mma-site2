<?php
session_start();
include "db.php";
include "security.php";

if (!isset($_SESSION['user_id'])) exit("Acesso negado.");

$user_id = (int) $_SESSION['user_id'];
$stmtRole = $conn->prepare("SELECT role FROM users WHERE id=? LIMIT 1");
$stmtRole->bind_param("i", $user_id);
$stmtRole->execute();
$roleCurrent = $stmtRole->get_result()->fetch_assoc()['role'] ?? 'user';
if ($roleCurrent !== 'admin') exit("Acesso negado.");

if (!isset($_GET['id'])) exit("Utilizador não encontrado.");

$id = (int) $_GET['id'];
$stmtGet = $conn->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
$stmtGet->bind_param("i", $id);
$stmtGet->execute();
$u = $stmtGet->get_result()->fetch_assoc();
if (!$u) exit("Utilizador não encontrado.");

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf_or_die();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'user';
    if (!in_array($role, ['user', 'admin'], true)) {
        $role = 'user';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email inválido.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $email, $role, $id);

        if ($stmt->execute()) {
            $success = "Utilizador atualizado!";
            $u = array_merge($u, compact('name', 'email', 'role'));
        } else {
            $error = "Erro ao atualizar.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Editar Utilizador</title>
<script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css"></head>

<body class="bg-neutral-900 text-neutral-100">
<div class="pt-20 max-w-3xl mx-auto px-6">
<h1 class="text-5xl font-bold text-red-500 mb-6">Editar Utilizador</h1>
<?php if ($success): ?><p class="bg-green-600 p-3 rounded mb-4"><?= e($success) ?></p><?php endif; ?>
<?php if ($error): ?><p class="bg-red-600 p-3 rounded mb-4"><?= e($error) ?></p><?php endif; ?>

<form method="POST" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700">
<?= csrf_field(); ?>
<label>Nome</label><input type="text" name="name" value="<?= e($u['name']) ?>" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Email</label><input type="email" name="email" value="<?= e($u['email']) ?>" class="w-full bg-neutral-900 p-2 rounded mb-4" required>
<label>Role</label>
<select name="role" class="w-full bg-neutral-900 p-2 rounded mb-4">
    <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
</select>
<button class="bg-red-600 px-6 py-3 rounded hover:bg-red-700">Guardar</button>
</form>

<a href="admin_users.php" class="text-red-500 mt-6 inline-block">Voltar</a>
</div>
</body>
</html>

