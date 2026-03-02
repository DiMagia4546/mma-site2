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
if ($id <= 0) exit("ID inválido.");

if ($id === $user_id) exit("Não podes eliminar a tua conta admin em sessão.");

$stmt = $conn->prepare("DELETE FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: admin_users.php");
exit;
