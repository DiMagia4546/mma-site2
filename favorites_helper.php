<?php
function favoritesTable(mysqli $conn): string
{
    static $table = null;

    if ($table !== null) {
        return $table;
    }

    $res = $conn->query("SHOW TABLES LIKE 'favorites'");
    if ($res && $res->num_rows > 0) {
        $table = 'favorites';
        return $table;
    }

    $res = $conn->query("SHOW TABLES LIKE 'user_favorites'");
    if ($res && $res->num_rows > 0) {
        $table = 'user_favorites';
        return $table;
    }

    $conn->query(
        "CREATE TABLE IF NOT EXISTS favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            fighter_id INT DEFAULT NULL,
            event_id INT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_user_fighter (user_id, fighter_id),
            UNIQUE KEY uniq_user_event (user_id, event_id)
        )"
    );

    $table = 'favorites';
    return $table;
}
