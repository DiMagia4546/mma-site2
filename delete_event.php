<?php
require_once "auth.php";
require_once "security.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Metodo invalido.");
}

verify_csrf_or_die();
requireAdmin($conn);

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;
if ($id <= 0) {
    exit("ID invalido.");
}

$stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: admin_events.php");
exit;
