<?php
session_start();
include "security.php";
include "navbar.php";
include "db.php";

function existing_image_or(string $path, string $fallback): string
{
    $candidate = trim($path);
    if ($candidate !== '' && is_file(__DIR__ . '/' . $candidate)) {
        return $candidate;
    }
    return $fallback;
}

function short_text(string $text, int $max = 150): string
{
    return mb_strimwidth(trim($text), 0, $max, "...");
}

$today = date("Y-m-d");

$nextEvent = null;
$stmtEvent = $conn->prepare("SELECT id, name, date, location, main_event, banner FROM events WHERE date >= ? ORDER BY date ASC LIMIT 1");
$stmtEvent->bind_param("s", $today);
$stmtEvent->execute();
$resEvent = $stmtEvent->get_result();
if ($resEvent && $resEvent->num_rows > 0) {
    $nextEvent = $resEvent->fetch_assoc();
}
$stmtEvent->close();

$latestNews = null;
$stmtNews = $conn->prepare("SELECT id, title, content, image_path, created_at FROM news ORDER BY created_at DESC, id DESC LIMIT 1");
$stmtNews->execute();
$resNews = $stmtNews->get_result();
if ($resNews && $resNews->num_rows > 0) {
    $latestNews = $resNews->fetch_assoc();
}
$stmtNews->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MMA 360 | Plataforma de MMA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css">
    <script src="assets/account-menu.js" defer></script>
</head>
<body class="text-neutral-100 bg-neutral-950">

<?php if (isset($_SESSION['welcome'])): ?>
    <div id="welcomeBox" class="fixed top-6 left-1/2 -translate-x-1/2 bg-red-600 text-white px-6 py-3 rounded-lg shadow-xl flex items-center gap-4 z-50">
        <span class="text-lg"><?= e($_SESSION['welcome']) ?></span>
        <button onclick="closeWelcome()" class="text-xl hover:text-black transition">x</button>
    </div>
    <?php unset($_SESSION['welcome']); ?>
<?php endif; ?>

<?php render_main_nav('index'); ?>

<section class="relative min-h-[88vh] flex items-center pt-28 pb-16">
    <div class="absolute inset-0">
        <img src="https://i2-prod.mirror.co.uk/incoming/article1516071.ece/ALTERNATES/s1227b/_MG_0139.jpg" class="w-full h-full object-cover opacity-45" alt="MMA arena">
        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/55 to-black/80"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-6 w-full grid grid-cols-1 lg:grid-cols-5 gap-8 items-center">
        <div class="lg:col-span-3">
            <span class="inline-block bg-red-600 text-white px-4 py-1 rounded-full text-sm uppercase tracking-[0.2em] mb-6">MMA 360</span>
            <h1 class="text-6xl md:text-7xl leading-[0.95] mb-6">Cobertura MMA com foco no que importa</h1>
            <p class="text-lg md:text-xl text-neutral-300 max-w-2xl mb-8">
                Eventos, lutadores e notícias numa experiência direta e profissional.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="eventos.php" class="bg-red-600 text-white px-8 py-3 rounded-lg text-lg">Ver Eventos</a>
                <a href="noticias.php" class="bg-neutral-800 border border-neutral-700 px-8 py-3 rounded-lg text-lg">Ler Notícias</a>
            </div>
        </div>

        <div class="lg:col-span-2 bg-neutral-900/80 border border-neutral-700 rounded-2xl p-6">
            <h2 class="text-3xl mb-4">Acesso Rápido</h2>
            <div class="space-y-3">
                <a href="fighters.php" class="block bg-neutral-800 hover:bg-neutral-700 border border-neutral-700 rounded-xl p-4 transition">
                    <p class="text-xl">Explorar Lutadores</p>
                </a>
                <a href="eventos.php" class="block bg-neutral-800 hover:bg-neutral-700 border border-neutral-700 rounded-xl p-4 transition">
                    <p class="text-xl">Cards e Odds</p>
                </a>
                <a href="dashboard.php" class="block bg-neutral-800 hover:bg-neutral-700 border border-neutral-700 rounded-xl p-4 transition">
                    <p class="text-xl">Minha Conta</p>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-14">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-7">
        <article class="bg-neutral-800 border border-neutral-700 rounded-2xl overflow-hidden">
            <?php if ($nextEvent): ?>
                <?php $eventBanner = existing_image_or($nextEvent['banner'] ?? '', 'uploads/default_banner.webp'); ?>
                <img src="<?= e($eventBanner) ?>" class="w-full h-56 object-cover" alt="<?= e($nextEvent['name']) ?>">
                <div class="p-6">
                    <p class="text-red-400 uppercase tracking-[0.2em] text-xs mb-2">Próximo Evento</p>
                    <h3 class="text-4xl mb-2"><?= e($nextEvent['name']) ?></h3>
                    <p class="text-neutral-300"><?= e($nextEvent['main_event']) ?></p>
                    <p class="text-neutral-500 text-sm mt-2"><?= e(date("d/m/Y", strtotime($nextEvent['date']))) ?> • <?= e(short_text($nextEvent['location'], 90)) ?></p>
                    <a href="evento.php?id=<?= (int) $nextEvent['id'] ?>" class="inline-block mt-5 bg-red-600 hover:bg-red-700 transition px-5 py-2 rounded-lg">Abrir Evento</a>
                </div>
            <?php else: ?>
                <div class="p-6">
                    <p class="text-neutral-400">Sem eventos futuros registados.</p>
                </div>
            <?php endif; ?>
        </article>

        <article class="bg-neutral-800 border border-neutral-700 rounded-2xl overflow-hidden">
            <?php if ($latestNews): ?>
                <?php $newsImage = existing_image_or($latestNews['image_path'] ?? '', 'uploads/default_banner.webp'); ?>
                <img src="<?= e($newsImage) ?>" class="w-full h-56 object-cover" alt="<?= e($latestNews['title']) ?>">
                <div class="p-6">
                    <p class="text-red-400 uppercase tracking-[0.2em] text-xs mb-2">Última Notícia</p>
                    <h3 class="text-4xl mb-2"><?= e(short_text($latestNews['title'], 75)) ?></h3>
                    <p class="text-neutral-300"><?= e(short_text($latestNews['content'], 170)) ?></p>
                    <p class="text-neutral-500 text-sm mt-2"><?= e(date("d/m/Y H:i", strtotime($latestNews['created_at']))) ?></p>
                    <a href="noticias.php" class="inline-block mt-5 bg-neutral-700 hover:bg-neutral-600 transition px-5 py-2 rounded-lg">Ver Notícias</a>
                </div>
            <?php else: ?>
                <div class="p-6">
                    <p class="text-neutral-400">Sem notícias publicadas.</p>
                </div>
            <?php endif; ?>
        </article>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 pb-20">
    <div class="bg-gradient-to-r from-red-700/30 via-neutral-900 to-red-700/20 border border-red-700/40 rounded-2xl p-8 text-center">
        <h3 class="text-5xl mb-3">Acompanha o MMA sem perder tempo</h3>
        <p class="text-neutral-300 mb-6">Cria conta para guardar favoritos, receber alertas e usar o dashboard completo.</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="register.php" class="bg-red-600 hover:bg-red-700 transition px-7 py-3 rounded-lg">Criar Conta</a>
            <a href="login.php" class="bg-neutral-800 border border-neutral-700 hover:bg-neutral-700 transition px-7 py-3 rounded-lg">Iniciar Sessão</a>
        </div>
    </div>
</section>

<footer class="border-t border-neutral-700 py-10 text-center">
    <p class="text-neutral-300 text-lg">mma360.project@gmail.com</p>
    <p class="mt-4 text-neutral-500 text-sm">© 2026 MMA 360 - Todos os direitos reservados</p>
</footer>

<script>
function closeWelcome() {
    const box = document.getElementById('welcomeBox');
    if (!box) return;
    box.style.opacity = '0';
    setTimeout(() => box.remove(), 300);
}
</script>

</body>
</html>

