<?php
session_start();
include "db.php";
include "activity.php";
include "security.php";
include "favorites_helper.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

verify_csrf_or_die();

$user_id = (int) $_SESSION['user_id'];
$fighter_id = isset($_POST['fighter_id']) ? (int) $_POST['fighter_id'] : 0;
$event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
$favTable = favoritesTable($conn);

if ($fighter_id > 0) {
    $check = $conn->prepare("SELECT id FROM {$favTable} WHERE user_id=? AND fighter_id=? LIMIT 1");
    $check->bind_param("ii", $user_id, $fighter_id);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();

    if ($exists) {
        $del = $conn->prepare("DELETE FROM {$favTable} WHERE id=?");
        $del->bind_param("i", $exists['id']);
        $del->execute();
        logActivity($conn, $user_id, "Removeu um lutador dos favoritos");
    } else {
        $ins = $conn->prepare("INSERT INTO {$favTable} (user_id, fighter_id) VALUES (?, ?)");
        $ins->bind_param("ii", $user_id, $fighter_id);
        $ins->execute();
        logActivity($conn, $user_id, "Adicionou um lutador aos favoritos");
    }

    header("Location: fighter.php?id={$fighter_id}");
    exit;
}

if ($event_id > 0) {
    $check = $conn->prepare("SELECT id FROM {$favTable} WHERE user_id=? AND event_id=? LIMIT 1");
    $check->bind_param("ii", $user_id, $event_id);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();

    if ($exists) {
        $del = $conn->prepare("DELETE FROM {$favTable} WHERE id=?");
        $del->bind_param("i", $exists['id']);
        $del->execute();
        logActivity($conn, $user_id, "Removeu um evento dos favoritos");
    } else {
        $ins = $conn->prepare("INSERT INTO {$favTable} (user_id, event_id) VALUES (?, ?)");
        $ins->bind_param("ii", $user_id, $event_id);
        $ins->execute();
        logActivity($conn, $user_id, "Adicionou um evento aos favoritos");
    }

    header("Location: evento.php?id={$event_id}");
    exit;
}

header("Location: dashboard.php");
exit;
