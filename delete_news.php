<?php
session_start();
include "db.php";
include "security.php";

if (!isset($_SESSION['user_id'])) {
    exit("Acesso negado.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Metodo invalido.");
}

verify_csrf_or_die();

$user_id = (int) $_SESSION['user_id'];
$stmtRole = $conn->prepare("SELECT role FROM users WHERE id=? LIMIT 1");
$stmtRole->bind_param("i", $user_id);
$stmtRole->execute();
$role = $stmtRole->get_result()->fetch_assoc()['role'] ?? 'user';
$stmtRole->close();

if ($role !== 'admin') {
    exit("Acesso negado.");
}

$news_id = (int) ($_POST['news_id'] ?? 0);
if ($news_id <= 0) {
    exit("ID invalido.");
}

$stmt = $conn->prepare("DELETE FROM news WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $news_id);
$stmt->execute();
$stmt->close();

header("Location: admin_news.php");
exit;

