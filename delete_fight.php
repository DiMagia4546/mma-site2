<?php
session_start();
include "db.php";
include "security.php";

if (!isset($_SESSION['user_id'])) exit("Acesso negado.");
if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit("Método inválido.");
verify_csrf_or_die();

$user_id = (int) $_SESSION['user_id'];
$stmtRole = $conn->prepare("SELECT role FROM users WHERE id=? LIMIT 1");
$stmtRole->bind_param("i", $user_id);
$stmtRole->execute();
$role = $stmtRole->get_result()->fetch_assoc()['role'] ?? 'user';
if ($role !== 'admin') exit("Acesso negado.");

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
if ($id <= 0 || $event_id <= 0) exit("Dados inválidos.");

$stmt = $conn->prepare("DELETE FROM event_fights WHERE id=? AND event_id=?");
$stmt->bind_param("ii", $id, $event_id);
$stmt->execute();

header("Location: admin_event_fights.php?event_id={$event_id}");
exit;
