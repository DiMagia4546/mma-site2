<<<<<<< HEAD
<?php
require_once "auth.php";

$user_id = requireAdmin($conn);
=======
﻿<?php
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
>>>>>>> bb0e1c37f01ca30bb9c897503cc0cf8c0a0a5224

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) exit("ID inválido.");

<<<<<<< HEAD
$id = (int)$_GET['id'];

$stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
=======
$stmt = $conn->prepare("DELETE FROM events WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
>>>>>>> bb0e1c37f01ca30bb9c897503cc0cf8c0a0a5224

header("Location: admin_events.php");
exit;
