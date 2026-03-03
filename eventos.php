<?php
session_start();
include "db.php";
include "security.php";

$today = date("Y-m-d");
$stmt = $conn->prepare("SELECT * FROM events WHERE date > ? ORDER BY date ASC");
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos | MMA 360</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Teko', sans-serif; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css">
    <script src="assets/account-menu.js" defer></script>
</head>

<body class="bg-neutral-900 text-neutral-100">

<nav class="fixed top-0 w-full z-40 bg-neutral-900/70 backdrop-blur border-b border-neutral-700">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-3">
            <img src="assets/logo-mma360.png.png" class="h-12 md:h-14" alt="Logo">
            <span class="text-xl font-semibold tracking-widest text-red-500">MMA 360</span>
        </a>

        <ul class="hidden md:flex gap-8 text-sm uppercase tracking-wide">
            <li><a href="index.php" class="hover:text-red-500 transition">Início</a></li>
            <li><a href="noticias.php" class="hover:text-red-500 transition">Notícias</a></li>
            <li><a href="about.php" class="hover:text-red-500 transition">Quem Somos</a></li>
            <li><a href="fighters.php" class="hover:text-red-500 transition">Lutadores</a></li>
            <li><a href="eventos.php" class="text-red-500">Eventos</a></li>
            <li><a href="contacto.php" class="hover:text-red-500 transition">Contacto</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                $displayName = $_SESSION['user_name'] ?? 'Conta';
                $displayEmail = $_SESSION['user_email'] ?? '';
                $displayPic = $_SESSION['user_profile_pic'] ?? '';
                $initial = strtoupper(substr(trim($displayName) ?: 'U', 0, 1));
                ?>
                <li class="relative account-menu">
                    <button type="button" class="account-menu-toggle flex items-center gap-2 text-neutral-100 hover:text-red-500 transition">
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
                        <a href="logout.php" class="block px-4 py-3 text-sm text-red-500 hover:bg-neutral-800">Terminar Sessão</a>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="login.php" class="hover:text-red-500 transition">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<section class="pt-32 pb-10 text-center">
    <h1 class="text-6xl font-bold tracking-widest text-red-500">EVENTOS FUTUROS</h1>
    <p class="text-neutral-400 text-lg mt-2">Próximas noites de combate</p>
</section>

<div class="max-w-6xl mx-auto px-6 pb-20 grid grid-cols-1 md:grid-cols-2 gap-10">

<?php if ($result && $result->num_rows > 0): ?>
    <?php while ($event = $result->fetch_assoc()): ?>

        <?php
        $event_id = (int) $event['id'];
        $stmtFights = $conn->prepare("SELECT * FROM event_fights WHERE event_id = ? ORDER BY fight_order ASC");
        $stmtFights->bind_param("i", $event_id);
        $stmtFights->execute();
        $fights = $stmtFights->get_result();

        $fightList = [];
        while ($fightRow = $fights->fetch_assoc()) {
            $fightList[] = $fightRow;
        }

        $fightCount = count($fightList);
        $locationLabel = preg_replace('/\s+/', ' ', trim((string) $event['location']));
        if (strlen($locationLabel) > 58) {
            $locationLabel = substr($locationLabel, 0, 55) . '...';
        }

        $banner = !empty($event['banner']) ? $event['banner'] : 'uploads/default_banner.webp';
        $bannerBackdrop = 'assets/days-badge-bg.avif';
        $daysTo = (int) floor((strtotime($event['date']) - strtotime($today)) / 86400);
        if ($daysTo < 0) {
            $daysTo = 0;
        }
        $daysLabel = $daysTo === 0 ? 'Hoje' : 'Faltam ' . $daysTo . ' dias';
        ?>

        <a href="evento.php?id=<?= $event_id ?>" class="block group">
            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-6 shadow-lg hover:shadow-xl transition">
                <div class="relative rounded-xl overflow-hidden mb-5 border border-neutral-700">
                    <img src="<?= e($bannerBackdrop) ?>" class="absolute inset-0 w-full h-full object-cover scale-105 opacity-80" alt="">
                    <img src="<?= e($banner) ?>" class="relative w-full h-36 object-cover mix-blend-screen opacity-85" alt="<?= e($event['name']) ?>">
                    <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/40 to-black/75"></div>
                    <div class="absolute left-4 right-4 bottom-3 flex justify-between items-center text-xs">
                        <span class="px-3 py-1 rounded-full bg-red-600/90 text-white font-semibold"><?= e($daysLabel) ?></span>
                        <span class="px-3 py-1 rounded-full bg-neutral-900/90 text-neutral-200 border border-neutral-600"><?= (int) $fightCount ?> lutas</span>
                    </div>
                </div>

                <div class="relative mb-5">
                    <div class="overflow-hidden rounded-lg">
                        <div class="flex transition-all duration-500" id="slider-<?= $event_id ?>">

                            <?php if ($fightCount > 0): ?>
                                <?php foreach ($fightList as $fight): ?>
                                    <div class="min-w-full flex items-center justify-between px-6 py-4">
                                        <img src="<?= e($fight['fighter1_image']) ?>" class="w-24 h-24 md:w-28 md:h-28 object-cover rounded-full border-2 border-red-600" alt="<?= e($fight['fighter1_name']) ?>">
                                        <span class="text-3xl md:text-4xl font-bold text-white mx-4">VS</span>
                                        <img src="<?= e($fight['fighter2_image']) ?>" class="w-24 h-24 md:w-28 md:h-28 object-cover rounded-full border-2 border-red-600" alt="<?= e($fight['fighter2_name']) ?>">
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="min-w-full text-center text-neutral-400 py-10">Sem lutas registadas ainda.</div>
                            <?php endif; ?>

                        </div>
                    </div>

                    <button type="button" onclick="event.preventDefault(); prevFight(<?= $event_id ?>)" class="absolute left-0 top-1/2 -translate-y-1/2 bg-neutral-700/70 hover:bg-neutral-600 text-white px-3 py-2 rounded-full">‹</button>
                    <button type="button" onclick="event.preventDefault(); nextFight(<?= $event_id ?>)" class="absolute right-0 top-1/2 -translate-y-1/2 bg-neutral-700/70 hover:bg-neutral-600 text-white px-3 py-2 rounded-full">›</button>
                </div>

                <h2 class="text-3xl md:text-4xl font-bold text-white tracking-wide mb-1"><?= e($event['name']) ?></h2>
                <p class="text-red-500 text-lg mb-3"><?= e($event['main_event']) ?></p>
                <div class="flex flex-wrap gap-2 text-xs mb-3">
                    <span class="px-2.5 py-1 rounded-full bg-neutral-900 border border-neutral-700 text-neutral-300"><?= e(date("d/m/Y", strtotime($event['date']))) ?></span>
                    <span class="px-2.5 py-1 rounded-full bg-neutral-900 border border-neutral-700 text-neutral-300"><?= e($locationLabel) ?></span>
                </div>
                <p class="text-neutral-400 text-sm">Main card preview profissional com imagens dos atletas e navegação por combate.</p>
            </div>
        </a>

    <?php endwhile; ?>
<?php else: ?>
    <p class="text-center text-neutral-400 text-lg col-span-2">Ainda não há eventos futuros registados.</p>
<?php endif; ?>

</div>

<footer class="bg-neutral-900 border-t border-neutral-700 py-10 text-center">
    <p class="text-neutral-300 text-lg">mma360@gmail.com</p>
    <p class="mt-4 text-neutral-500 text-sm">© 2026 MMA 360 - Todos os direitos reservados</p>
</footer>

<script>
let positions = {};

function nextFight(eventId) {
    const slider = document.getElementById("slider-" + eventId);
    if (!slider) return;
    const total = slider.children.length;
    if (total === 0) return;

    if (positions[eventId] === undefined) positions[eventId] = 0;
    positions[eventId] = (positions[eventId] + 1) % total;
    slider.style.transform = `translateX(-${positions[eventId] * 100}%)`;
}

function prevFight(eventId) {
    const slider = document.getElementById("slider-" + eventId);
    if (!slider) return;
    const total = slider.children.length;
    if (total === 0) return;

    if (positions[eventId] === undefined) positions[eventId] = 0;
    positions[eventId] = (positions[eventId] - 1 + total) % total;
    slider.style.transform = `translateX(-${positions[eventId] * 100}%)`;
}
</script>

</body>
</html>


