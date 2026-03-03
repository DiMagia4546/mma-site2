<?php
session_start();
include "db.php";
include "security.php";

$newsItems = [];
$stmtNews = $conn->prepare(
    "SELECT n.id, n.title, n.content, n.created_at, n.image_path, u.name AS author_name
     FROM news n
     LEFT JOIN users u ON u.id = n.author_id
     ORDER BY n.created_at DESC"
);
$stmtNews->execute();
$resNews = $stmtNews->get_result();
while ($row = $resNews->fetch_assoc()) {
    $newsItems[] = $row;
}
$stmtNews->close();

$featured = $newsItems[0] ?? null;
$others = array_slice($newsItems, 1);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notícias | MMA 360</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css">
    <script src="assets/account-menu.js" defer></script>
</head>
<body class="text-neutral-100">

<nav class="fixed top-0 w-full z-40 backdrop-blur border-b border-neutral-700">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-3">
            <img src="assets/logo-mma360.png.png" class="h-12 md:h-14" alt="Logo MMA 360">
            <span class="text-xl font-semibold tracking-widest text-red-500">MMA 360</span>
        </a>

        <ul class="hidden md:flex gap-8 text-sm uppercase tracking-wide">
            <li><a href="index.php" class="hover:text-red-400 transition">Início</a></li>
            <li><a href="noticias.php" class="text-red-400">Notícias</a></li>
            <li><a href="about.php" class="hover:text-red-400 transition">Quem Somos</a></li>
            <li><a href="fighters.php" class="hover:text-red-400 transition">Lutadores</a></li>
            <li><a href="eventos.php" class="hover:text-red-400 transition">Eventos</a></li>
            <li><a href="contacto.php" class="hover:text-red-400 transition">Contacto</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                $displayName = $_SESSION['user_name'] ?? 'Conta';
                $displayEmail = $_SESSION['user_email'] ?? '';
                $displayPic = $_SESSION['user_profile_pic'] ?? '';
                $initial = strtoupper(substr(trim($displayName) ?: 'U', 0, 1));
                ?>
                <li class="relative account-menu">
                    <button type="button" class="account-menu-toggle flex items-center gap-2 text-neutral-100 hover:text-red-400 transition">
                        <?php if (!empty($displayPic)): ?>
                            <img src="<?= e($displayPic) ?>" class="w-9 h-9 rounded-full object-cover border border-red-500" alt="Perfil">
                        <?php else: ?>
                            <span class="w-9 h-9 rounded-full bg-red-600 text-white flex items-center justify-center text-sm font-bold"><?= e($initial) ?></span>
                        <?php endif; ?>
                        <span class="hidden lg:block normal-case text-sm"><?= e($displayName) ?></span>
                    </button>

                    <div class="account-menu-panel hidden absolute right-0 top-12 w-72 bg-neutral-900/95 border border-neutral-700 rounded-xl shadow-2xl overflow-hidden normal-case">
                        <div class="px-4 py-3 border-b border-neutral-700">
                            <p class="text-sm font-semibold text-white"><?= e($displayName) ?></p>
                            <p class="text-xs text-neutral-400"><?= e($displayEmail) ?></p>
                        </div>
                        <a href="dashboard.php" class="block px-4 py-3 text-sm hover:bg-neutral-800">Dashboard</a>
                        <a href="logout.php" class="block px-4 py-3 text-sm text-red-400 hover:bg-neutral-800">Terminar Sessão</a>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="login.php" class="hover:text-red-400 transition">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<section class="relative min-h-[50vh] flex items-end pt-28 pb-12">
    <div class="absolute inset-0">
        <img src="https://cdn.vox-cdn.com/uploads/chorus_asset/file/24687096/1458973403.jpg" class="w-full h-full object-cover opacity-40" alt="Notícias MMA">
        <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/55 to-black/85"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-6 w-full">
        <span class="inline-block bg-red-600 text-white px-4 py-1 rounded-full text-xs uppercase tracking-[0.2em] mb-4">Redação MMA 360</span>
        <h1 class="text-6xl md:text-7xl leading-[0.95]">Notícias e Análise</h1>
        <p class="text-neutral-300 text-lg mt-4 max-w-2xl">Atualizações de eventos, bastidores e contexto técnico num formato editorial profissional.</p>
    </div>
</section>

<main class="max-w-7xl mx-auto px-6 py-12">
    <?php if ($featured): ?>
        <article class="bg-neutral-800 border border-neutral-700 rounded-2xl p-6 md:p-8 mb-10">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-stretch">
                <div class="lg:col-span-3">
                    <div class="flex flex-wrap items-center gap-3 text-xs mb-4">
                        <span class="px-3 py-1 rounded-full bg-red-600 text-white uppercase tracking-wide">Destaque</span>
                        <span class="px-3 py-1 rounded-full bg-neutral-900 border border-neutral-700 text-neutral-300">
                            <?= e(date("d/m/Y H:i", strtotime($featured["created_at"]))) ?>
                        </span>
                        <span class="px-3 py-1 rounded-full bg-neutral-900 border border-neutral-700 text-neutral-300">
                            Autor: <?= e($featured["author_name"] ?: "Redação MMA 360") ?>
                        </span>
                    </div>
                    <h2 class="text-4xl md:text-5xl text-white mb-5"><?= e($featured["title"]) ?></h2>
                    <p class="text-neutral-300 text-lg leading-relaxed"><?= nl2br(e($featured["content"])) ?></p>
                </div>

                <div class="lg:col-span-2 rounded-xl overflow-hidden border border-neutral-700 min-h-[220px]">
                    <?php if (!empty($featured["image_path"])): ?>
                        <img src="<?= e($featured["image_path"]) ?>" class="w-full h-full object-cover" alt="<?= e($featured["title"]) ?>">
                    <?php else: ?>
                        <div class="w-full h-full bg-gradient-to-br from-red-700/30 via-neutral-900 to-neutral-800 flex items-center justify-center text-neutral-400 text-sm uppercase tracking-[0.2em]">
                            MMA 360
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    <?php endif; ?>

    <?php if (!empty($others)): ?>
        <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($others as $news): ?>
                <article class="bg-neutral-800 border border-neutral-700 rounded-2xl p-6">
                    <div class="rounded-lg overflow-hidden border border-neutral-700 mb-4">
                        <?php if (!empty($news["image_path"])): ?>
                            <img src="<?= e($news["image_path"]) ?>" class="w-full h-44 object-cover" alt="<?= e($news["title"]) ?>">
                        <?php else: ?>
                            <div class="w-full h-44 bg-gradient-to-br from-red-700/20 via-neutral-900 to-neutral-800"></div>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center justify-between text-xs text-neutral-400 mb-3">
                        <span><?= e(date("d/m/Y H:i", strtotime($news["created_at"]))) ?></span>
                        <span><?= e($news["author_name"] ?: "Redação MMA 360") ?></span>
                    </div>
                    <h3 class="text-3xl text-white mb-3"><?= e($news["title"]) ?></h3>
                    <p class="text-neutral-300 leading-relaxed"><?= e(mb_strimwidth($news["content"], 0, 260, "...")) ?></p>
                </article>
            <?php endforeach; ?>
        </section>
    <?php elseif (!$featured): ?>
        <section class="bg-neutral-800 border border-dashed border-neutral-700 rounded-2xl p-10 text-center">
            <h2 class="text-4xl mb-3">Sem notícias publicadas</h2>
            <p class="text-neutral-400">Quando adicionares notícias na base de dados, elas vão aparecer aqui automaticamente.</p>
        </section>
    <?php endif; ?>
</main>

<footer class="border-t border-neutral-700 py-10 text-center">
    <p class="text-neutral-300 text-lg">mma360@gmail.com</p>
    <p class="mt-4 text-neutral-500 text-sm">© 2026 MMA 360 - Todos os direitos reservados</p>
</footer>

</body>
</html>

