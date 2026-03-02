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
    <link rel="stylesheet" href="assets/site.css"></head>

<body class="bg-neutral-900 text-neutral-100">

<nav class="fixed top-0 w-full z-40 bg-neutral-900/70 backdrop-blur border-b border-neutral-700">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-3">
            <img src="pf-removebg-preview.png" class="h-10" alt="Logo">
            <span class="text-xl font-semibold tracking-widest text-red-500">MMA 360</span>
        </a>

        <ul class="hidden md:flex gap-8 text-sm uppercase tracking-wide">
            <li><a href="index.php" class="hover:text-red-500 transition">Início</a></li>
            <li><a href="noticias.php" class="hover:text-red-500 transition">Notícias</a></li>
            <li><a href="about.php" class="hover:text-red-500 transition">Quem Somos</a></li>
            <li><a href="fighters.php" class="hover:text-red-500 transition">Lutadores</a></li>
            <li><a href="eventos.php" class="text-red-500">Eventos</a></li>
            <li><a href="contacto.php" class="hover:text-red-500 transition">Contacto</a></li>
            <li><a href="login.php" class="hover:text-red-500 transition">Login</a></li>
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
        ?>

        <a href="evento.php?id=<?= $event_id ?>" class="block group">
            <div class="bg-neutral-800 border border-neutral-700 rounded-xl p-6 shadow-lg hover:shadow-xl transition">
                <div class="relative mb-6">
                    <div class="overflow-hidden rounded-lg">
                        <div class="flex transition-all duration-500" id="slider-<?= $event_id ?>">

                            <?php if ($fights && $fights->num_rows > 0): ?>
                                <?php while ($fight = $fights->fetch_assoc()): ?>
                                    <div class="min-w-full flex items-center justify-between px-6 py-4">
                                        <img src="<?= e($fight['fighter1_image']) ?>" class="w-24 h-24 md:w-28 md:h-28 object-cover rounded-full border-2 border-red-600" alt="<?= e($fight['fighter1_name']) ?>">
                                        <span class="text-3xl md:text-4xl font-bold text-white mx-4">VS</span>
                                        <img src="<?= e($fight['fighter2_image']) ?>" class="w-24 h-24 md:w-28 md:h-28 object-cover rounded-full border-2 border-red-600" alt="<?= e($fight['fighter2_name']) ?>">
                                    </div>
                                <?php endwhile; ?>
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
                <p class="text-neutral-300 text-sm"><?= e(date("d/m/Y", strtotime($event['date']))) ?>  •  <?= e($event['location']) ?></p>
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

