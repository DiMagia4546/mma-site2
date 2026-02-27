<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE id = $user_id";
$res = $conn->query($sql);
$user = $res->fetch_assoc();

$success = "";
$error = "";

// PROCESSAR FORMULÁRIO
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    // Upload da foto
    $profile_pic = $user['profile_pic'];

    if (!empty($_FILES['profile_pic']['name'])) {

        $file = $_FILES['profile_pic'];
        $allowed = ['jpg','jpeg','png','webp'];

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Formato inválido. Apenas JPG, PNG, WEBP.";
        } else {
            $newName = "user_" . $user_id . "_" . time() . "." . $ext;
            $path = "uploads/" . $newName;

            if (move_uploaded_file($file['tmp_name'], $path)) {
                $profile_pic = $path;
            } else {
                $error = "Erro ao fazer upload da imagem.";
            }
        }
    }

    if (empty($error)) {
        $update = $conn->prepare("UPDATE users SET name=?, email=?, profile_pic=? WHERE id=?");
        $update->bind_param("sssi", $name, $email, $profile_pic, $user_id);

        if ($update->execute()) {
            $success = "Perfil atualizado com sucesso!";
            $user['name'] = $name;
            $user['email'] = $email;
            $user['profile_pic'] = $profile_pic;
        } else {
            $error = "Erro ao atualizar perfil.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil | MMA 360</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Teko', sans-serif; }
    </style>
</head>

<body class="bg-neutral-900 text-neutral-100">

<!-- NAVBAR -->
<nav class="fixed top-0 w-full z-40 bg-neutral-900/70 backdrop-blur border-b border-neutral-700">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-3">
            <img src="pf-removebg-preview.png" class="h-10">
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

    <h1 class="text-6xl font-bold text-red-500 tracking-widest mb-10">EDITAR PERFIL</h1>

    <?php if (!empty($success)): ?>
        <p class="bg-green-600 text-white px-4 py-2 rounded mb-6"><?= $success ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="bg-red-600 text-white px-4 py-2 rounded mb-6"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-neutral-800 border border-neutral-700 p-8 rounded-xl shadow-xl">

        <div class="flex items-center gap-6 mb-8">
            <img src="<?= $user['profile_pic'] ?: 'uploads/default_user.png' ?>" 
                 class="w-28 h-28 rounded-full object-cover border-2 border-red-600">

            <div>
                <label class="block text-neutral-300 mb-1">Nova Foto</label>
                <input type="file" name="profile_pic" class="text-neutral-300">
            </div>
        </div>

        <label class="block text-neutral-300 mb-1">Nome</label>
        <input type="text" name="name" value="<?= $user['name'] ?>"
               class="w-full bg-neutral-900 border border-neutral-700 rounded px-4 py-2 mb-6 text-neutral-100">

        <label class="block text-neutral-300 mb-1">Email</label>
        <input type="email" name="email" value="<?= $user['email'] ?>"
               class="w-full bg-neutral-900 border border-neutral-700 rounded px-4 py-2 mb-6 text-neutral-100">

        <button class="bg-red-600 px-6 py-3 rounded-lg hover:bg-red-700 transition text-white text-lg">
            Guardar Alterações
        </button>

    </form>

    <div class="mt-10">
        <a href="dashboard.php" class="text-red-500 hover:text-red-400 text-sm uppercase tracking-[0.25em]">
            ← Voltar ao Painel
        </a>
    </div>

</section>

</body>
</html>
