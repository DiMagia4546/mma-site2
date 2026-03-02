<?php
session_start();
include "db.php";
include "security.php";
include "favorites_helper.php";

if (!isset($_GET["id"])) {
    die("Evento nao encontrado.");
}

$event_id = (int) $_GET["id"];

$stmtEvent = $conn->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
$stmtEvent->bind_param("i", $event_id);
$stmtEvent->execute();
$res_event = $stmtEvent->get_result();
$stmtEvent->close();

if (!$res_event || $res_event->num_rows === 0) {
    die("Evento nao existe.");
}

$event = $res_event->fetch_assoc();
$isFavorite = false;
if (isset($_SESSION["user_id"])) {
    $favTable = favoritesTable($conn);
    $user_id = (int) $_SESSION["user_id"];
    $stmtFav = $conn->prepare("SELECT id FROM {$favTable} WHERE user_id = ? AND event_id = ? LIMIT 1");
    $stmtFav->bind_param("ii", $user_id, $event_id);
    $stmtFav->execute();
    $isFavorite = (bool) $stmtFav->get_result()->fetch_assoc();
    $stmtFav->close();
}

function normalizeFighterKey(string $name): string
{
    $normalized = preg_replace('/\s+/', ' ', trim($name));
    $normalized = preg_replace('/[^a-z0-9 ]/i', '', $normalized);
    return strtolower($normalized);
}

function flagCodesByNationality(string $nationality): array
{
    $n = strtolower(trim($nationality));
    if (str_contains($n, 'mexican-american') || str_contains($n, 'mexicano-americano')) {
        return ['US', 'MX'];
    }

    if (str_contains($n, 'american') && str_contains($n, 'mexican')) {
        return ['US', 'MX'];
    }

    $flags = [
        'american' => 'US',
        'north-american' => 'US',
        'norte-americano' => 'US',
        'united states' => 'US',
        'usa' => 'US',
        'brazilian' => 'BR',
        'brazil' => 'BR',
        'brasileiro' => 'BR',
        'portuguese' => 'PT',
        'portugal' => 'PT',
        'russian' => 'RU',
        'russia' => 'RU',
        'georgian' => 'GE',
        'georgia' => 'GE',
        'english' => 'GB',
        'british' => 'GB',
        'french' => 'FR',
        'france' => 'FR',
        'spanish' => 'ES',
        'spain' => 'ES',
        'dutch' => 'NL',
        'netherlands' => 'NL',
        'holandes' => 'NL',
        'holandês' => 'NL',
        'mexican' => 'MX',
        'mexicano' => 'MX',
        'puerto rican' => 'PR',
        'porto riquenho' => 'PR',
        'porto-riquenho' => 'PR',
        'puertorican' => 'PR',
        'unknown' => 'ZZ',
        'desconhecida' => 'ZZ',
        'desconhecido' => 'ZZ',
    ];

    $country = $flags[$n] ?? null;
    if (!$country) {
        return [];
    }

    return [$country];
}

function emojiByCountryCode(string $country): string
{
    if ($country === 'ZZ') {
        return '🏳️';
    }

    $first = ord($country[0]) - 65 + 127462;
    $second = ord($country[1]) - 65 + 127462;
    return mb_chr($first, 'UTF-8') . mb_chr($second, 'UTF-8');
}

function renderFlagHtml(string $nationality): string
{
    $codes = flagCodesByNationality($nationality);
    if (empty($codes)) {
        return '';
    }

    $parts = [];
    foreach ($codes as $country) {
        $code = strtolower($country);
        $imagePath = null;
        foreach (['png', 'jpg', 'jpeg', 'webp', 'avif'] as $ext) {
            $candidate = __DIR__ . "/assets/flags/{$code}.{$ext}";
            if (is_file($candidate)) {
                $imagePath = "assets/flags/{$code}.{$ext}";
                break;
            }
        }

        if ($imagePath) {
            $parts[] = '<img src="' . $imagePath . '" alt="' . htmlspecialchars($country, ENT_QUOTES, 'UTF-8') . '" class="inline-block w-5 h-3.5 object-cover rounded-sm border border-neutral-600 mr-1 align-middle">';
        } else {
            $parts[] = '<span class="mr-1 align-middle">' . emojiByCountryCode($country) . '</span>';
        }
    }

    return implode('', $parts);
}

$fightersMap = [];
$fightersMetaRes = $conn->query("SELECT name, nationality, wins, losses FROM fighters");
if ($fightersMetaRes) {
    while ($meta = $fightersMetaRes->fetch_assoc()) {
        $fightersMap[normalizeFighterKey($meta['name'])] = $meta;
    }
}

$stmtFights = $conn->prepare("SELECT * FROM event_fights WHERE event_id = ? ORDER BY fight_order ASC");
$stmtFights->bind_param("i", $event_id);
$stmtFights->execute();
$fights = $stmtFights->get_result();
$stmtFights->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($event["name"]) ?> | MMA 360</title>

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
            <img src="pf-removebg-preview.png" class="h-10" alt="Logo">
            <span class="text-xl font-semibold tracking-widest text-red-500">MMA 360</span>
        </a>

        <ul class="hidden md:flex gap-8 text-sm uppercase tracking-wide">
            <li><a href="index.php" class="hover:text-red-500 transition">Inicio</a></li>
            <li><a href="noticias.php" class="hover:text-red-500 transition">Noticias</a></li>
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

<div class="pt-24"></div>

<?php $banner = !empty($event["banner"]) ? $event["banner"] : "uploads/default_banner.webp"; ?>
<div class="w-full h-64 md:h-80 bg-cover bg-center border-b border-neutral-700"
     style="background-image: linear-gradient(to top, rgba(0,0,0,0.75), rgba(0,0,0,0.3)), url('<?= e($banner) ?>');">
    <div class="max-w-5xl mx-auto h-full flex flex-col justify-end px-6 pb-6">
        <h1 class="text-6xl font-bold text-white tracking-wide"><?= e($event["name"]) ?></h1>
        <p class="text-neutral-300 text-lg mt-2">
            <?= e(date("d/m/Y", strtotime($event["date"]))) ?>  -  <?= e($event["location"]) ?>
        </p>

        <?php if (isset($_SESSION["user_id"])): ?>
        <form method="POST" action="toggle_favorite.php">
            <?= csrf_field(); ?>
            <input type="hidden" name="event_id" value="<?= (int) $event["id"] ?>">
            <?php if ($isFavorite): ?>
                <button class="mt-4 bg-neutral-700 px-6 py-3 rounded-lg hover:bg-neutral-600 transition">Remover dos Favoritos</button>
            <?php else: ?>
                <button class="mt-4 bg-red-600 px-6 py-3 rounded-lg hover:bg-red-700 transition">Adicionar aos Favoritos</button>
            <?php endif; ?>
        </form>
        <?php endif; ?>
    </div>
</div>

<section class="max-w-5xl mx-auto px-6 py-12">
    <div class="mb-8 flex flex-wrap gap-2 text-xs">
        <span class="px-3 py-1 rounded-full bg-neutral-800 border border-neutral-700 text-neutral-300">Main event: <?= e($event["main_event"]) ?></span>
        <span class="px-3 py-1 rounded-full bg-neutral-800 border border-neutral-700 text-neutral-300">Local: <?= e($event["location"]) ?></span>
    </div>

    <h2 class="text-5xl font-bold text-white tracking-wide mb-10">Fight Card</h2>

    <div class="space-y-10">
        <?php if ($fights && $fights->num_rows > 0): ?>
            <?php while ($fight = $fights->fetch_assoc()): ?>
                <?php
                $f1Meta = $fightersMap[normalizeFighterKey($fight["fighter1_name"])] ?? null;
                $f2Meta = $fightersMap[normalizeFighterKey($fight["fighter2_name"])] ?? null;
                ?>
                <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-8 shadow-lg">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-10">
                        <div class="flex flex-col items-center">
                            <img src="<?= e($fight["fighter1_image"]) ?>" class="w-40 h-40 object-cover rounded-full border-2 border-red-600" alt="<?= e($fight["fighter1_name"]) ?>">
                            <p class="text-3xl font-bold mt-4 text-white"><?= e($fight["fighter1_name"]) ?></p>
                            <?php if ($f1Meta): ?>
                                <p class="text-sm text-neutral-300 mt-1"><?= renderFlagHtml($f1Meta["nationality"]) ?><span><?= e($f1Meta["nationality"]) ?></span></p>
                                <p class="text-sm text-neutral-400">Recorde: <?= (int) $f1Meta["wins"] ?>-<?= (int) $f1Meta["losses"] ?>-0</p>
                            <?php else: ?>
                                <p class="text-sm text-neutral-500 mt-1">Dados de carreira indisponiveis</p>
                            <?php endif; ?>
                        </div>

                        <span class="text-5xl font-bold text-white">VS</span>

                        <div class="flex flex-col items-center">
                            <img src="<?= e($fight["fighter2_image"]) ?>" class="w-40 h-40 object-cover rounded-full border-2 border-red-600" alt="<?= e($fight["fighter2_name"]) ?>">
                            <p class="text-3xl font-bold mt-4 text-white"><?= e($fight["fighter2_name"]) ?></p>
                            <?php if ($f2Meta): ?>
                                <p class="text-sm text-neutral-300 mt-1"><?= renderFlagHtml($f2Meta["nationality"]) ?><span><?= e($f2Meta["nationality"]) ?></span></p>
                                <p class="text-sm text-neutral-400">Recorde: <?= (int) $f2Meta["wins"] ?>-<?= (int) $f2Meta["losses"] ?>-0</p>
                            <?php else: ?>
                                <p class="text-sm text-neutral-500 mt-1">Dados de carreira indisponiveis</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-neutral-400">Ainda nao ha lutas registadas para este evento.</p>
        <?php endif; ?>
    </div>

    <div class="mt-10">
        <a href="eventos.php" class="text-red-500 hover:text-red-400 text-sm uppercase tracking-[0.25em]">Voltar aos eventos</a>
    </div>
</section>

</body>
</html>
