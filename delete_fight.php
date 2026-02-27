<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) exit("Acesso negado.");

$user_id = $_SESSION['user_id'];
$role = $conn->query("SELECT role FROM users WHERE id=$user_id")->fetch_assoc()['role'];
if ($role !== 'admin') exit("Acesso negado.");

if (!isset($_GET['id']) || !isset($_GET['event_id'])) exit("Dados inválidos.");

$id = intval($_GET['id']);
$event_id = intval($_GET['event_id']);

$conn->query("DELETE FROM event_fights WHERE id=$id");

header("Location: admin_event_fights.php?event_id=$event_id");
exit;
