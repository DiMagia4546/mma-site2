<?php
session_start();
include "db.php";
include "security.php";
include "navbar.php";
include "favorites_helper.php";
include "odds_helper.php";
require_login();

if (!isset($_GET["id"])) {
    die("Evento nao encontrado.");
}

$event_id = (int) $_GET["id"];

function firstExistingAsset(array $relativePaths): string
{
    foreach ($relativePaths as $path) {
        if (is_file(__DIR__ . "/" . $path)) {
            return $path;
        }
    }

    return "";
}

function eventBannerPath(?string $banner): string
{
    $candidate = trim((string) $banner);
    if ($candidate !== '' && is_file(__DIR__ . '/' . $candidate)) {
        return $candidate;
    }
    return 'uploads/default_banner.webp';
}

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
        'holandÃªs' => 'NL',
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
        return 'ðŸ³ï¸';
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

function fighterStrength(?array $meta, string $name): float
{
    if (!$meta) {
        $seed = abs(crc32($name)) % 100;
        return 0.50 + (($seed - 50) / 800.0);
    }

    $wins = max(0, (int) ($meta['wins'] ?? 0));
    $losses = max(0, (int) ($meta['losses'] ?? 0));
    $sample = $wins + $losses;
    $base = ($wins + 1.0) / ($sample + 2.0);

    $experienceBoost = min(0.08, $sample * 0.0025);
    $seed = abs(crc32($name)) % 100;
    $microBias = ($seed - 50) / 1200.0;

    return max(0.28, min(0.72, $base + $experienceBoost + $microBias));
}

function probToAmericanOdds(float $p): int
{
    $p = max(0.05, min(0.95, $p));
    if ($p >= 0.5) {
        return (int) round(-100 * ($p / (1 - $p)));
    }

    return (int) round(100 * ((1 - $p) / $p));
}

function formatAmericanOdds(int $odds): string
{
    return $odds > 0 ? '+' . $odds : (string) $odds;
}

function impliedProbabilityFromAmerican(int $odds): float
{
    if ($odds < 0) {
        return abs($odds) / (abs($odds) + 100);
    }

    if ($odds > 0) {
        return 100 / ($odds + 100);
    }

    return 0.5;
}

function calculateFightOdds(?array $f1Meta, ?array $f2Meta, string $fighter1, string $fighter2): array
{
    $s1 = fighterStrength($f1Meta, $fighter1);
    $s2 = fighterStrength($f2Meta, $fighter2);
    $p1True = $s1 / ($s1 + $s2);
    $p2True = 1 - $p1True;

    // Bookmaker margin for display realism (overround ~4.5%)
    $margin = 1.045;
    $p1Book = min(0.95, $p1True * $margin);
    $p2Book = min(0.95, $p2True * $margin);

    return [
        'p1' => $p1Book,
        'p2' => $p2Book,
        'odds1' => probToAmericanOdds($p1Book),
        'odds2' => probToAmericanOdds($p2Book),
    ];
}

$fightersMap = [];
$fightersMetaRes = $conn->query("SELECT id, name, nationality, wins, losses FROM fighters");
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

$officialOddsMap = build_official_odds_map();
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
        .event-hero-wrap {
            background-color: #04070d;
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
        }

        .event-hero-card {
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(2px);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.45);
            position: relative;
            min-height: 280px;
            background: rgba(5, 8, 14, 0.72);
        }

        .event-hero-art {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            filter: saturate(1.08) contrast(1.04);
        }

        .event-hero-overlay {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, rgba(3, 6, 12, 0.88) 0%, rgba(3, 6, 12, 0.52) 38%, rgba(3, 6, 12, 0.74) 100%),
                linear-gradient(180deg, rgba(3, 6, 12, 0.16), rgba(3, 6, 12, 0.78));
        }

        .event-hero-content {
            position: relative;
            z-index: 1;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/site.css">
    <script src="assets/account-menu.js" defer></script>
</head>

<body class="bg-neutral-900 text-neutral-100">

<?php render_main_nav('eventos'); ?>

<div class="pt-24"></div>

<?php
$banner = eventBannerPath($event["banner"] ?? '');
$heroBackground = "background-image:
    radial-gradient(circle at 12% 20%, rgba(255, 43, 86, 0.14), transparent 38%),
    radial-gradient(circle at 88% 18%, rgba(255, 43, 86, 0.12), transparent 36%),
    linear-gradient(180deg, rgba(3,6,12,0.90), rgba(3,6,12,0.74)),
    url('{$banner}');";
?>
<div class="event-hero-wrap w-full border-b border-neutral-700" style="<?= e($heroBackground) ?>">
    <div class="max-w-5xl mx-auto px-6 py-8 md:py-10">
        <div class="event-hero-card rounded-2xl overflow-hidden p-6 md:p-8">
            <img src="<?= e($banner) ?>" class="event-hero-art" alt="<?= e($event["name"]) ?>">
            <div class="event-hero-overlay"></div>
            <div class="event-hero-content">
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
                $officialOdds = official_odds_for_fight($officialOddsMap, $fight["fighter1_name"], $fight["fighter2_name"]);
                if ($officialOdds) {
                    $odds = [
                        'odds1' => (int) $officialOdds['odds1'],
                        'odds2' => (int) $officialOdds['odds2'],
                        'p1' => impliedProbabilityFromAmerican((int) $officialOdds['odds1']),
                        'p2' => impliedProbabilityFromAmerican((int) $officialOdds['odds2']),
                        'source' => 'official',
                        'bookmaker' => $officialOdds['bookmaker'] ?? 'Sportsbook',
                        'last_update' => $officialOdds['last_update'] ?? '',
                    ];
                } else {
                    $odds = calculateFightOdds($f1Meta, $f2Meta, $fight["fighter1_name"], $fight["fighter2_name"]);
                    $odds['source'] = 'simulated';
                    $odds['bookmaker'] = '';
                    $odds['last_update'] = '';
                }
                ?>
                <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-8 shadow-lg">
                    <?php
                    $fighter1Id = (int) ($f1Meta['id'] ?? 0);
                    $fighter2Id = (int) ($f2Meta['id'] ?? 0);
                    ?>
                    <div class="flex flex-col md:flex-row items-center justify-between gap-10">
                        <div class="flex flex-col items-center">
                            <?php if ($fighter1Id > 0): ?>
                                <a href="fighter.php?id=<?= $fighter1Id ?>" class="block">
                                    <img src="<?= e($fight["fighter1_image"]) ?>" class="w-40 h-40 object-cover rounded-full border-2 border-red-600 hover:scale-105 transition" alt="<?= e($fight["fighter1_name"]) ?>">
                                </a>
                            <?php else: ?>
                                <img src="<?= e($fight["fighter1_image"]) ?>" class="w-40 h-40 object-cover rounded-full border-2 border-red-600" alt="<?= e($fight["fighter1_name"]) ?>">
                            <?php endif; ?>
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
                            <?php if ($fighter2Id > 0): ?>
                                <a href="fighter.php?id=<?= $fighter2Id ?>" class="block">
                                    <img src="<?= e($fight["fighter2_image"]) ?>" class="w-40 h-40 object-cover rounded-full border-2 border-red-600 hover:scale-105 transition" alt="<?= e($fight["fighter2_name"]) ?>">
                                </a>
                            <?php else: ?>
                                <img src="<?= e($fight["fighter2_image"]) ?>" class="w-40 h-40 object-cover rounded-full border-2 border-red-600" alt="<?= e($fight["fighter2_name"]) ?>">
                            <?php endif; ?>
                            <p class="text-3xl font-bold mt-4 text-white"><?= e($fight["fighter2_name"]) ?></p>
                            <?php if ($f2Meta): ?>
                                <p class="text-sm text-neutral-300 mt-1"><?= renderFlagHtml($f2Meta["nationality"]) ?><span><?= e($f2Meta["nationality"]) ?></span></p>
                                <p class="text-sm text-neutral-400">Recorde: <?= (int) $f2Meta["wins"] ?>-<?= (int) $f2Meta["losses"] ?>-0</p>
                            <?php else: ?>
                                <p class="text-sm text-neutral-500 mt-1">Dados de carreira indisponiveis</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch">
                        <div class="rounded-lg border border-neutral-700 bg-neutral-900/60 px-4 py-3 text-center">
                            <p class="text-xs uppercase tracking-[0.2em] text-neutral-400">Moneyline</p>
                            <p class="text-2xl font-bold text-white mt-1"><?= e($fight["fighter1_name"]) ?> <span class="text-red-400"><?= e(formatAmericanOdds($odds['odds1'])) ?></span></p>
                            <p class="text-xs text-neutral-500 mt-1">Prob. implicita: <?= e((string) round($odds['p1'] * 100, 1)) ?>%</p>
                        </div>

                        <div class="rounded-lg border border-neutral-700 bg-neutral-900/40 px-4 py-3 text-center flex items-center justify-center">
                            <?php if (($odds['source'] ?? '') === 'official'): ?>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.2em] text-emerald-300">Odds oficiais</p>
                                    <p class="text-[11px] text-neutral-400 mt-1"><?= e($odds['bookmaker']) ?></p>
                                </div>
                            <?php else: ?>
                                <p class="text-xs uppercase tracking-[0.2em] text-neutral-500">Odds estimadas (simulacao)</p>
                            <?php endif; ?>
                        </div>

                        <div class="rounded-lg border border-neutral-700 bg-neutral-900/60 px-4 py-3 text-center">
                            <p class="text-xs uppercase tracking-[0.2em] text-neutral-400">Moneyline</p>
                            <p class="text-2xl font-bold text-white mt-1"><?= e($fight["fighter2_name"]) ?> <span class="text-red-400"><?= e(formatAmericanOdds($odds['odds2'])) ?></span></p>
                            <p class="text-xs text-neutral-500 mt-1">Prob. implicita: <?= e((string) round($odds['p2'] * 100, 1)) ?>%</p>
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



