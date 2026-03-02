<?php
if (!isset($conn)) {
    require_once "db.php";
}

function logActivity(mysqli $conn, int $user_id, string $action): void
{
    $stmt = $conn->prepare("INSERT INTO user_activity (user_id, action) VALUES (?, ?)");
    if ($stmt) {
        $stmt->bind_param("is", $user_id, $action);
        $stmt->execute();
        $stmt->close();
    }
}
