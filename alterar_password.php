<?php
session_start();
include "db.php";
include "security.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf_or_die();

    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $user['password'])) {
        $error = "A password atual está incorreta.";
    } elseif ($new !== $confirm) {
        $error = "As passwords novas não coincidem.";
    } elseif (strlen($new) < 8) {
        $error = "A nova password deve ter pelo menos 8 caracteres.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $update->bind_param("si", $hashed, $user_id);

        if ($update->execute()) {
            $success = "Password alterada com sucesso!";
        } else {
            $error = "Erro ao atualizar password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Password | MMA 360</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Teko', sans-serif; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css"></head>

<body class="bg-neutral-900 text-neutral-100">
<nav class="fixed top-0 w-full z-40 bg-neutral-900/70 backdrop-blur border-b border-neutral-700">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-3">
            <img src="assets/logo-mma360.png.png" class="h-12 md:h-14" alt="Logo">
            <span class="text-xl font-semibold tracking-widest text-red-500">MMA 360</span>
        </a>

        <ul class="hidden md:flex gap-8 text-sm uppercase tracking-wide">
            <li><a href="dashboard.php" class="hover:text-red-500 transition">Painel</a></li>
            <li><a href="fighters.php" class="hover:text-red-500 transition">Lutadores</a></li>
            <li><a href="eventos.php" class="hover:text-red-500 transition">Eventos</a></li>
            <li><a href="logout.php" class="text-red-500">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="pt-28"></div>

<section class="max-w-3xl mx-auto px-6">
    <h1 class="text-6xl font-bold text-red-500 tracking-widest mb-10">ALTERAR PASSWORD</h1>

    <?php if (!empty($success)): ?><p class="bg-green-600 text-white px-4 py-2 rounded mb-6"><?= e($success) ?></p><?php endif; ?>
    <?php if (!empty($error)): ?><p class="bg-red-600 text-white px-4 py-2 rounded mb-6"><?= e($error) ?></p><?php endif; ?>

    <form method="POST" class="bg-neutral-800 border border-neutral-700 p-8 rounded-xl shadow-xl">
        <?= csrf_field(); ?>

        <label class="block text-neutral-300 mb-1">Password Atual</label>
        <input type="password" name="current_password" required class="w-full bg-neutral-900 border border-neutral-700 rounded px-4 py-2 mb-6 text-neutral-100">

        <label class="block text-neutral-300 mb-1">Nova Password</label>
        <input type="password" name="new_password" required class="w-full bg-neutral-900 border border-neutral-700 rounded px-4 py-2 mb-6 text-neutral-100">

        <label class="block text-neutral-300 mb-1">Confirmar Nova Password</label>
        <input type="password" name="confirm_password" required class="w-full bg-neutral-900 border border-neutral-700 rounded px-4 py-2 mb-6 text-neutral-100">

        <button class="bg-red-600 px-6 py-3 rounded-lg hover:bg-red-700 transition text-white text-lg">Alterar Password</button>
    </form>

    <div class="mt-10">
        <a href="dashboard.php" class="text-red-500 hover:text-red-400 text-sm uppercase tracking-[0.25em]">Voltar ao Painel</a>
    </div>
</section>

</body>
</html>


