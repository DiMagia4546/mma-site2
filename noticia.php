<?php
session_start();
include "db.php";
include "security.php";
include "navbar.php";

if (!isset($_GET['id'])) {
    http_response_code(404);
    exit('Noticia nao encontrada.');
}

$newsId = (int) $_GET['id'];
if ($newsId <= 0) {
    http_response_code(404);
    exit('Noticia invalida.');
}

$stmt = $conn->prepare(
    "SELECT n.id, n.title, n.content, n.image_path, n.created_at, u.name AS author_name
     FROM news n
     LEFT JOIN users u ON u.id = n.author_id
     WHERE n.id = ?
     LIMIT 1"
);
$stmt->bind_param("i", $newsId);
$stmt->execute();
$res = $stmt->get_result();
$news = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$news) {
    http_response_code(404);
    exit('Noticia nao encontrada.');
}

$related = [];
$stmtRelated = $conn->prepare(
    "SELECT id, title, image_path, created_at
     FROM news
     WHERE id <> ?
     ORDER BY created_at DESC, id DESC
     LIMIT 3"
);
$stmtRelated->bind_param("i", $newsId);
$stmtRelated->execute();
$resRelated = $stmtRelated->get_result();
while ($row = $resRelated->fetch_assoc()) {
    $related[] = $row;
}
$stmtRelated->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($news['title']) ?> | MMA 360</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css">
    <script src="assets/account-menu.js" defer></script>
</head>
<body class="text-neutral-100 bg-neutral-950">

<?php render_main_nav('noticias'); ?>

<main class="pt-28 pb-16 max-w-6xl mx-auto px-6">
    <a href="noticias.php" class="inline-block text-red-400 hover:text-red-300 text-sm uppercase tracking-[0.2em] mb-6">Voltar às notícias</a>

    <article class="bg-neutral-900 border border-neutral-700 rounded-2xl overflow-hidden">
        <?php if (!empty($news['image_path'])): ?>
            <img src="<?= e($news['image_path']) ?>" class="w-full h-[260px] md:h-[420px] object-cover" alt="<?= e($news['title']) ?>">
        <?php endif; ?>

        <div class="p-6 md:p-10">
            <div class="flex flex-wrap gap-3 text-xs mb-5 text-neutral-400">
                <span class="px-3 py-1 rounded-full bg-neutral-800 border border-neutral-700"><?= e(date("d/m/Y H:i", strtotime($news['created_at']))) ?></span>
                <span class="px-3 py-1 rounded-full bg-neutral-800 border border-neutral-700">Autor: <?= e($news['author_name'] ?: 'Redação MMA 360') ?></span>
            </div>

            <h1 class="text-5xl md:text-6xl leading-[0.95] mb-8"><?= e($news['title']) ?></h1>
            <div class="text-neutral-200 text-lg leading-relaxed space-y-5">
                <?php foreach (preg_split('/\R{2,}/', trim((string) $news['content'])) as $paragraph): ?>
                    <?php if (trim($paragraph) !== ''): ?>
                        <p><?= nl2br(e(trim($paragraph))) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </article>

    <?php if (!empty($related)): ?>
        <section class="mt-10">
            <h2 class="text-4xl mb-5">Mais Notícias</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <?php foreach ($related as $item): ?>
                    <a href="noticia.php?id=<?= (int) $item['id'] ?>" class="bg-neutral-900 border border-neutral-700 rounded-xl overflow-hidden hover:border-red-500/50 transition">
                        <?php if (!empty($item['image_path'])): ?>
                            <img src="<?= e($item['image_path']) ?>" class="w-full h-36 object-cover" alt="<?= e($item['title']) ?>">
                        <?php endif; ?>
                        <div class="p-4">
                            <p class="text-xs text-neutral-400 mb-2"><?= e(date("d/m/Y H:i", strtotime($item['created_at']))) ?></p>
                            <p class="text-xl leading-tight"><?= e(mb_strimwidth($item['title'], 0, 85, '...')) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<footer class="border-t border-neutral-700 py-10 text-center">
    <p class="text-neutral-300 text-lg">mma360.project@gmail.com</p>
    <p class="mt-4 text-neutral-500 text-sm">© 2026 MMA 360 - Todos os direitos reservados</p>
</footer>

</body>
</html>

