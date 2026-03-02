<?php
require_once "auth.php";
require_once "activity.php";

$user_id   = requireLogin();
$fighter_id = isset($_POST['fighter_id']) ? (int)$_POST['fighter_id'] : null;
$event_id   = isset($_POST['event_id']) ? (int)$_POST['event_id'] : null;

if (!$fighter_id && !$event_id) {
    header("Location: dashboard.php");
    exit;
}

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
}

header("Location: dashboard.php");
exit;
