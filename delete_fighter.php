<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) exit("Acesso negado.");

$user_id = $_SESSION['user_id'];
$role = $conn->query("SELECT role FROM users WHERE id=$user_id")->fetch_assoc()['role'];
if ($role !== 'admin') exit("Acesso negado.");

if (!isset($_GET['id'])) exit("ID inválido.");

$id = intval($_GET['id']);

$conn->query("DELETE FROM fighters WHERE id=$id");

header("Location: admin_fighters.php");
exit;
