<?php
require_once "auth.php";
require_once "security.php";
require_once "activity.php";
require_once "favorites_helper.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit;
}

verify_csrf_or_die();

$user_id = requireLogin();
$fighter_id = isset($_POST["fighter_id"]) ? (int) $_POST["fighter_id"] : 0;
$event_id = isset($_POST["event_id"]) ? (int) $_POST["event_id"] : 0;
$favTable = favoritesTable($conn);

if ($fighter_id > 0) {
    $check = $conn->prepare("SELECT id FROM {$favTable} WHERE user_id = ? AND fighter_id = ? LIMIT 1");
    $check->bind_param("ii", $user_id, $fighter_id);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();
    $check->close();

    if ($exists) {
        $delete = $conn->prepare("DELETE FROM {$favTable} WHERE id = ?");
        $delete->bind_param("i", $exists["id"]);
        $delete->execute();
        $delete->close();
        logActivity($conn, $user_id, "Removeu um lutador dos favoritos");
    } else {
        $insert = $conn->prepare("INSERT INTO {$favTable} (user_id, fighter_id) VALUES (?, ?)");
        $insert->bind_param("ii", $user_id, $fighter_id);
        $insert->execute();
        $insert->close();
        logActivity($conn, $user_id, "Adicionou um lutador aos favoritos");
    }

    header("Location: fighter.php?id={$fighter_id}");
    exit;
}

if ($event_id > 0) {
    $check = $conn->prepare("SELECT id FROM {$favTable} WHERE user_id = ? AND event_id = ? LIMIT 1");
    $check->bind_param("ii", $user_id, $event_id);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();
    $check->close();

    if ($exists) {
        $delete = $conn->prepare("DELETE FROM {$favTable} WHERE id = ?");
        $delete->bind_param("i", $exists["id"]);
        $delete->execute();
        $delete->close();
        logActivity($conn, $user_id, "Removeu um evento dos favoritos");
    } else {
        $insert = $conn->prepare("INSERT INTO {$favTable} (user_id, event_id) VALUES (?, ?)");
        $insert->bind_param("ii", $user_id, $event_id);
        $insert->execute();
        $insert->close();
        logActivity($conn, $user_id, "Adicionou um evento aos favoritos");
    }

    header("Location: evento.php?id={$event_id}");
    exit;
}

header("Location: dashboard.php");
exit;
