<?php
require_once "auth.php";

$user_id = requireAdmin($conn);

if (!isset($_GET['id'])) exit("ID inválido.");

$id = (int)$_GET['id'];

$stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: admin_events.php");
exit;
