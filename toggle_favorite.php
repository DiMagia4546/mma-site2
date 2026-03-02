<<<<<<< HEAD
<?php
require_once "auth.php";
require_once "activity.php";
=======
﻿<?php
session_start();
include "db.php";
include "activity.php";
include "security.php";
include "favorites_helper.php";
>>>>>>> bb0e1c37f01ca30bb9c897503cc0cf8c0a0a5224

$user_id   = requireLogin();
$fighter_id = isset($_POST['fighter_id']) ? (int)$_POST['fighter_id'] : null;
$event_id   = isset($_POST['event_id']) ? (int)$_POST['event_id'] : null;

if (!$fighter_id && !$event_id) {
    header("Location: dashboard.php");
    exit;
}

<<<<<<< HEAD
if ($fighter_id) {
    $stmt = $conn->prepare("SELECT id FROM user_favorites WHERE user_id = ? AND fighter_id = ?");
    $stmt->bind_param("ii", $user_id, $fighter_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $del = $conn->prepare("DELETE FROM user_favorites WHERE user_id = ? AND fighter_id = ?");
        $del->bind_param("ii", $user_id, $fighter_id);
        $del->execute();
        $del->close();
        logActivity($conn, $user_id, "Removeu um lutador dos favoritos");
    } else {
        $ins = $conn->prepare("INSERT INTO user_favorites (user_id, fighter_id) VALUES (?, ?)");
        $ins->bind_param("ii", $user_id, $fighter_id);
        $ins->execute();
        $ins->close();
        logActivity($conn, $user_id, "Adicionou um lutador aos favoritos");
    }

    $stmt->close();
}

if ($event_id) {
    $stmt = $conn->prepare("SELECT id FROM user_favorites WHERE user_id = ? AND event_id = ?");
    $stmt->bind_param("ii", $user_id, $event_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $del = $conn->prepare("DELETE FROM user_favorites WHERE user_id = ? AND event_id = ?");
        $del->bind_param("ii", $user_id, $event_id);
        $del->execute();
        $del->close();
        logActivity($conn, $user_id, "Removeu um evento dos favoritos");
    } else {
        $ins = $conn->prepare("INSERT INTO user_favorites (user_id, event_id) VALUES (?, ?)");
        $ins->bind_param("ii", $user_id, $event_id);
        $ins->execute();
        $ins->close();
        logActivity($conn, $user_id, "Adicionou um evento aos favoritos");
    }

    $stmt->close();
=======
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
>>>>>>> bb0e1c37f01ca30bb9c897503cc0cf8c0a0a5224
}

header("Location: dashboard.php");
exit;
