<?php
session_start();
include "db.php";
include "security.php";
include "upload_helper.php";

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

// Ensure table supports image field for newsroom cards.
$hasImagePath = false;
$colCheck = $conn->query("SHOW COLUMNS FROM news LIKE 'image_path'");
if ($colCheck && $colCheck->num_rows > 0) {
    $hasImagePath = true;
} else {
    $conn->query("ALTER TABLE news ADD COLUMN image_path VARCHAR(255) NULL AFTER content");
    $hasImagePath = true;
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf_or_die();

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    $errors = [];
    $imagePath = uploadImage($_FILES['image'] ?? [], 'news', '', $errors);
    if ($imagePath === '') {
        $imagePath = null;
    }

    if ($title === '' || $content === '') {
        $errors[] = 'Preenche título e conteúdo.';
    }

    if (empty($errors)) {
        if ($hasImagePath) {
            $stmtInsert = $conn->prepare("INSERT INTO news (title, content, image_path, author_id) VALUES (?, ?, ?, ?)");
            $stmtInsert->bind_param("sssi", $title, $content, $imagePath, $user_id);
        } else {
            $stmtInsert = $conn->prepare("INSERT INTO news (title, content, author_id) VALUES (?, ?, ?)");
            $stmtInsert->bind_param("ssi", $title, $content, $user_id);
        }

        if ($stmtInsert->execute()) {
            $success = "Notícia criada com sucesso.";
        } else {
            $error = "Erro ao criar notícia.";
        }
        $stmtInsert->close();
    } else {
        $error = implode(' ', $errors);
    }
}

$news = [];
$stmtNews = $conn->prepare(
    "SELECT n.id, n.title, n.content, n.created_at, n.image_path, u.name AS author_name
     FROM news n
     LEFT JOIN users u ON u.id = n.author_id
     ORDER BY n.created_at DESC, n.id DESC"
);
$stmtNews->execute();
$resNews = $stmtNews->get_result();
while ($row = $resNews->fetch_assoc()) {
    $news[] = $row;
}
$stmtNews->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Notícias | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css">
</head>
<body class="bg-neutral-900 text-neutral-100">

<div class="pt-20 max-w-6xl mx-auto px-6">
    <h1 class="text-6xl font-bold text-red-500 mb-8">GERIR NOTÍCIAS</h1>
    <a href="admin_panel.php" class="text-red-500 hover:text-red-400 text-sm uppercase tracking-[0.25em]">Voltar ao Painel Admin</a>

    <section class="mt-8 bg-neutral-800 border border-neutral-700 rounded-xl p-8">
        <h2 class="text-4xl mb-5">Nova Notícia</h2>
        <?php if ($success): ?><p class="bg-green-600 p-3 rounded mb-4"><?= e($success) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="bg-red-600 p-3 rounded mb-4"><?= e($error) ?></p><?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field(); ?>
            <div>
                <label class="block mb-1">Título</label>
                <input type="text" name="title" class="w-full bg-neutral-900 p-3 rounded" required>
            </div>

            <div>
                <label class="block mb-1">Conteúdo</label>
                <textarea name="content" rows="7" class="w-full bg-neutral-900 p-3 rounded" required></textarea>
            </div>

            <div>
                <label class="block mb-1">Imagem (opcional)</label>
                <input type="file" name="image" accept="image/*">
            </div>

            <button class="bg-red-600 px-6 py-3 rounded hover:bg-red-700">Publicar Notícia</button>
        </form>
    </section>

    <section class="mt-8 mb-12">
        <h2 class="text-4xl mb-5">Publicadas</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($news as $item): ?>
                <article class="bg-neutral-800 border border-neutral-700 rounded-xl p-5">
                    <?php if (!empty($item['image_path'])): ?>
                        <img src="<?= e($item['image_path']) ?>" class="w-full h-44 object-cover rounded-lg mb-4" alt="<?= e($item['title']) ?>">
                    <?php endif; ?>
                    <h3 class="text-3xl mb-2"><?= e($item['title']) ?></h3>
                    <p class="text-xs text-neutral-400 mb-3">
                        <?= e(date("d/m/Y H:i", strtotime($item['created_at']))) ?> • <?= e($item['author_name'] ?: 'Redação MMA 360') ?>
                    </p>
                    <p class="text-neutral-300"><?= e(mb_strimwidth($item['content'], 0, 240, "...")) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</div>

</body>
</html>
