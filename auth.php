<?php
session_start();
require_once "db.php";

function requireLogin(): int
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    return (int)$_SESSION['user_id'];
}

function getUserRole(mysqli $conn, int $user_id): ?string
{
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $role = $res->num_rows ? $res->fetch_assoc()['role'] : null;
    $stmt->close();
    return $role;
}

function requireAdmin(mysqli $conn): int
{
    $user_id = requireLogin();
    $role = getUserRole($conn, $user_id);

    if ($role !== 'admin') {
        die("Acesso negado.");
    }
    return $user_id;
}
