<?php
session_start();
include "db.php";
include "activity.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$fighter_id = $_POST['fighter_id'] ?? null;
$event_id = $_POST['event_id'] ?? null;

if ($fighter_id) {
    $check = $conn->query("SELECT * FROM user_favorites WHERE user_id=$user_id AND fighter_id=$fighter_id");

    if ($check->num_rows > 0) {
        $conn->query("DELETE FROM user_favorites WHERE user_id=$user_id AND fighter_id=$fighter_id");
        logActivity($conn, $user_id, "Removeu um lutador dos favoritos");
    } else {
        $conn->query("INSERT INTO user_favorites (user_id, fighter_id) VALUES ($user_id, $fighter_id)");
        logActivity($conn, $user_id, "Adicionou um lutador aos favoritos");
    }
}

if ($event_id) {
    $check = $conn->query("SELECT * FROM user_favorites WHERE user_id=$user_id AND event_id=$event_id");

    if ($check->num_rows > 0) {
        $conn->query("DELETE FROM user_favorites WHERE user_id=$user_id AND event_id=$event_id");
        logActivity($conn, $user_id, "Removeu um evento dos favoritos");
    } else {
        $conn->query("INSERT INTO user_favorites (user_id, event_id) VALUES ($user_id, $event_id)");
        logActivity($conn, $user_id, "Adicionou um evento aos favoritos");
    }
}

header("Location: dashboard.php");
exit;
