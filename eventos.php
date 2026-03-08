<?php
session_start();
include "db.php";
include "security.php";
include "navbar.php";

$today = date("Y-m-d");
$activeView = $_GET['view'] ?? 'upcoming';
if ($activeView !== 'past') {
    $activeView = 'upcoming';
}

$stmt = $conn->prepare("SELECT * FROM events ORDER BY date ASC");
$stmt->execute();
$result = $stmt->get_result();

$upcomingEvents = [];
$pastEvents = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($row['date'] >= $today) {
            $upcomingEvents[] = $row;
        } else {
            $pastEvents[] = $row;
        }
    }
}

if (!empty($pastEvents)) {
    usort($pastEvents, function ($a, $b) {
        return strcmp($b['date'], $a['date']);
    });
}

$activeEvents = $activeView === 'past' ? $pastEvents : $upcomingEvents;
$activeTitle = $activeView === 'past' ? 'EVENTOS PASSADOS' : 'EVENTOS ATUAIS E FUTUROS';
$activeSubtitle = $activeView === 'past'
    ? 'Revive cards anteriores e resultados históricos'
    : 'Próximas noites de combate e eventos em curso';

function eventBannerPath(?string $banner): string
{
    $candidate = trim((string) $banner);
    if ($candidate !== '' && is_file(__DIR__ . '/' . $candidate)) {
        return $candidate;
    }
    return 'uploads/default_banner.webp';
}

function normalizeFighterKey(string $name): string
{
    $normalized = preg_replace('/\s+/', ' ', trim($name));
    $normalized = preg_replace('/[^a-z0-9 ]/i', '', $normalized);
    return strtolower($normalized);
}

$fighterIdMap = [];
$fightersRes = $conn->query("SELECT id, name FROM fighters");
if ($fightersRes) {
    while ($f = $fightersRes->fetch_assoc()) {
        $fighterIdMap[normalizeFighterKey($f['name'])] = (int) $f['id'];
    }
}
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

<?php render_main_nav('eventos'); ?>

<section class="pt-32 pb-10 text-center">
    <h1 class="text-6xl font-bold tracking-widest text-red-500"><?= e($activeTitle) ?></h1>
    <p class="text-neutral-400 text-lg mt-2"><?= e($activeSubtitle) ?></p>

    <div class="mt-6 inline-flex rounded-xl border border-neutral-700 bg-neutral-900/60 p-1">
        <a href="eventos.php?view=upcoming"
           class="px-5 py-2 rounded-lg text-sm uppercase tracking-wide transition <?= $activeView === 'upcoming' ? 'bg-red-600 text-white' : 'text-neutral-300 hover:bg-neutral-800' ?>">
            Atuais e Futuros (<?= count($upcomingEvents) ?>)
        </a>
        <a href="eventos.php?view=past"
           class="px-5 py-2 rounded-lg text-sm uppercase tracking-wide transition <?= $activeView === 'past' ? 'bg-red-600 text-white' : 'text-neutral-300 hover:bg-neutral-800' ?>">
            Passados (<?= count($pastEvents) ?>)
        </a>
    </div>
</section>

<div class="max-w-6xl mx-auto px-6 pb-20 grid grid-cols-1 md:grid-cols-2 gap-10">

<?php if (!empty($activeEvents)): ?>
    <?php foreach ($activeEvents as $event): ?>

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

        $banner = eventBannerPath($event['banner'] ?? '');
        $daysTo = (int) floor((strtotime($event['date']) - strtotime($today)) / 86400);
        if ($activeView === 'past') {
            $ago = abs($daysTo);
            $daysLabel = $ago === 0 ? 'Terminou hoje' : 'Terminou há ' . $ago . ' dias';
        } else {
            if ($daysTo < 0) {
                $daysTo = 0;
            }
            $daysLabel = $daysTo === 0 ? 'Hoje' : 'Faltam ' . $daysTo . ' dias';
        }
        ?>

        <div class="block group">
            <div
                class="event-card bg-neutral-800 border border-neutral-700 rounded-xl p-6 shadow-lg hover:shadow-xl transition h-full flex flex-col cursor-pointer"
                data-event-url="evento.php?id=<?= $event_id ?>"
            >
                <div class="relative rounded-xl overflow-hidden mb-5 border border-neutral-700 h-48">
                    <img src="<?= e($banner) ?>" class="w-full h-full object-cover" alt="<?= e($event['name']) ?>">
                    <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/30 to-black/60"></div>
                    <div class="absolute left-4 right-4 bottom-3 flex justify-between items-center text-xs">
                        <span class="px-3 py-1 rounded-full bg-red-600/90 text-white font-semibold"><?= e($daysLabel) ?></span>
                        <span class="px-3 py-1 rounded-full bg-neutral-900/90 text-neutral-200 border border-neutral-600"><?= (int) $fightCount ?> lutas</span>
                    </div>
                </div>

                <div class="relative mb-5 min-h-[9rem]">
                    <div class="overflow-hidden rounded-lg">
                        <div class="flex transition-all duration-500" id="slider-<?= $event_id ?>">

                            <?php if ($fightCount > 0): ?>
                                <?php foreach ($fightList as $fight): ?>
                                    <?php
                                    $f1Id = (int) ($fighterIdMap[normalizeFighterKey($fight['fighter1_name'])] ?? 0);
                                    $f2Id = (int) ($fighterIdMap[normalizeFighterKey($fight['fighter2_name'])] ?? 0);
                                    ?>
                                    <div class="min-w-full flex items-center justify-between px-6 py-4">
                                        <?php if ($f1Id > 0): ?>
                                            <a href="fighter.php?id=<?= $f1Id ?>" class="block">
                                                <img src="<?= e($fight['fighter1_image']) ?>" class="w-24 h-24 md:w-28 md:h-28 object-cover rounded-full border-2 border-red-600 hover:scale-105 transition" alt="<?= e($fight['fighter1_name']) ?>">
                                            </a>
                                        <?php else: ?>
                                            <img src="<?= e($fight['fighter1_image']) ?>" class="w-24 h-24 md:w-28 md:h-28 object-cover rounded-full border-2 border-red-600" alt="<?= e($fight['fighter1_name']) ?>">
                                        <?php endif; ?>
                                        <span class="text-3xl md:text-4xl font-bold text-white mx-4">VS</span>
                                        <?php if ($f2Id > 0): ?>
                                            <a href="fighter.php?id=<?= $f2Id ?>" class="block">
                                                <img src="<?= e($fight['fighter2_image']) ?>" class="w-24 h-24 md:w-28 md:h-28 object-cover rounded-full border-2 border-red-600 hover:scale-105 transition" alt="<?= e($fight['fighter2_name']) ?>">
                                            </a>
                                        <?php else: ?>
                                            <img src="<?= e($fight['fighter2_image']) ?>" class="w-24 h-24 md:w-28 md:h-28 object-cover rounded-full border-2 border-red-600" alt="<?= e($fight['fighter2_name']) ?>">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="min-w-full text-center text-neutral-400 py-10">Sem lutas registadas ainda.</div>
                            <?php endif; ?>

                        </div>
                    </div>

                    <button type="button" onclick="prevFight(<?= $event_id ?>)" class="absolute left-0 top-1/2 -translate-y-1/2 bg-neutral-700/70 hover:bg-neutral-600 text-white px-3 py-2 rounded-full">‹</button>
                    <button type="button" onclick="nextFight(<?= $event_id ?>)" class="absolute right-0 top-1/2 -translate-y-1/2 bg-neutral-700/70 hover:bg-neutral-600 text-white px-3 py-2 rounded-full">›</button>
                </div>

                <h2 class="text-3xl md:text-4xl font-bold text-white tracking-wide mb-1"><?= e($event['name']) ?></h2>
                <p class="text-red-500 text-lg mb-3"><?= e($event['main_event']) ?></p>
                <div class="flex flex-wrap gap-2 text-xs mb-3">
                    <span class="px-2.5 py-1 rounded-full bg-neutral-900 border border-neutral-700 text-neutral-300"><?= e(date("d/m/Y", strtotime($event['date']))) ?></span>
                    <span class="px-2.5 py-1 rounded-full bg-neutral-900 border border-neutral-700 text-neutral-300"><?= e($locationLabel) ?></span>
                </div>
                <p class="text-neutral-400 text-sm mt-auto">Main card preview profissional com imagens dos atletas e navegação por combate.</p>
                <a href="evento.php?id=<?= $event_id ?>" class="mt-4 inline-block text-sm uppercase tracking-wide text-red-400 hover:text-red-300">Abrir evento</a>
            </div>
        </div>

    <?php endforeach; ?>
<?php else: ?>
    <?php if ($activeView === 'past'): ?>
        <p class="text-center text-neutral-400 text-lg col-span-2">Ainda não há eventos passados registados.</p>
    <?php else: ?>
        <p class="text-center text-neutral-400 text-lg col-span-2">Ainda não há eventos atuais/futuros registados.</p>
    <?php endif; ?>
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

document.querySelectorAll('.event-card').forEach(function (card) {
    card.addEventListener('click', function (e) {
        if (e.target.closest('a, button')) {
            return;
        }
        const url = card.getAttribute('data-event-url');
        if (url) {
            window.location.href = url;
        }
    });
});
</script>

</body>
</html>


