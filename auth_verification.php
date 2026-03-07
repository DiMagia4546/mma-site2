<?php

function ensure_auth_verification_schema(mysqli $conn): void
{
    $hasEmailVerified = false;
    $colRes = $conn->query("SHOW COLUMNS FROM users LIKE 'email_verified'");
    if ($colRes && $colRes->num_rows > 0) {
        $hasEmailVerified = true;
    }
    if (!$hasEmailVerified) {
        $conn->query("ALTER TABLE users ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0");
    }

    $conn->query(
        "CREATE TABLE IF NOT EXISTS auth_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            purpose VARCHAR(30) NOT NULL,
            code_hash VARCHAR(255) NOT NULL,
            attempts INT NOT NULL DEFAULT 0,
            expires_at DATETIME NOT NULL,
            used_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_purpose (user_id, purpose),
            INDEX idx_expires (expires_at),
            CONSTRAINT fk_auth_codes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );

    $hasAttempts = false;
    $attemptsRes = $conn->query("SHOW COLUMNS FROM auth_codes LIKE 'attempts'");
    if ($attemptsRes && $attemptsRes->num_rows > 0) {
        $hasAttempts = true;
    }
    if (!$hasAttempts) {
        $conn->query("ALTER TABLE auth_codes ADD COLUMN attempts INT NOT NULL DEFAULT 0");
    }

    $conn->query(
        "CREATE TABLE IF NOT EXISTS pending_registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_pending_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );

    $conn->query(
        "CREATE TABLE IF NOT EXISTS pending_auth_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pending_id INT NOT NULL,
            purpose VARCHAR(30) NOT NULL,
            code_hash VARCHAR(255) NOT NULL,
            attempts INT NOT NULL DEFAULT 0,
            expires_at DATETIME NOT NULL,
            used_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_pending_purpose (pending_id, purpose),
            INDEX idx_pending_expires (expires_at),
            CONSTRAINT fk_pending_auth_codes_pending FOREIGN KEY (pending_id) REFERENCES pending_registrations(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );
}

function is_pending_auth_purpose(string $purpose): bool
{
    return $purpose === 'register_pending';
}

function generate_numeric_code(int $length = 6): string
{
    $min = (int) str_pad('1', $length, '0');
    $max = (int) str_pad('', $length, '9');
    return (string) random_int($min, $max);
}

function create_auth_code(mysqli $conn, int $userId, string $purpose, int $ttlSeconds = 600): string
{
    $table = is_pending_auth_purpose($purpose) ? 'pending_auth_codes' : 'auth_codes';
    $idCol = is_pending_auth_purpose($purpose) ? 'pending_id' : 'user_id';

    $delete = $conn->prepare("DELETE FROM {$table} WHERE {$idCol} = ? AND purpose = ? AND used_at IS NULL");
    $delete->bind_param("is", $userId, $purpose);
    $delete->execute();
    $delete->close();

    $code = generate_numeric_code(6);
    $hash = password_hash($code, PASSWORD_DEFAULT);
    $expiresAt = date("Y-m-d H:i:s", time() + $ttlSeconds);

    $stmt = $conn->prepare(
        "INSERT INTO {$table} ({$idCol}, purpose, code_hash, expires_at) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("isss", $userId, $purpose, $hash, $expiresAt);
    $stmt->execute();
    $stmt->close();

    return $code;
}

function verify_auth_code(mysqli $conn, int $userId, string $purpose, string $code): bool
{
    $result = verify_auth_code_detailed($conn, $userId, $purpose, $code);
    return (bool) ($result['ok'] ?? false);
}

function verify_auth_code_detailed(mysqli $conn, int $userId, string $purpose, string $code): array
{
    $table = is_pending_auth_purpose($purpose) ? 'pending_auth_codes' : 'auth_codes';
    $idCol = is_pending_auth_purpose($purpose) ? 'pending_id' : 'user_id';

    $stmt = $conn->prepare(
        "SELECT id, code_hash, attempts, expires_at
         FROM {$table}
         WHERE {$idCol} = ? AND purpose = ? AND used_at IS NULL AND expires_at >= NOW()
         ORDER BY id DESC
         LIMIT 1"
    );
    $stmt->bind_param("is", $userId, $purpose);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        return ['ok' => false, 'reason' => 'missing'];
    }

    $attempts = (int) ($row['attempts'] ?? 0);
    if ($attempts >= 5) {
        return ['ok' => false, 'reason' => 'locked', 'attempts_left' => 0];
    }

    if (!password_verify($code, $row['code_hash'])) {
        $id = (int) $row['id'];
        $upFail = $conn->prepare("UPDATE {$table} SET attempts = attempts + 1 WHERE id = ?");
        $upFail->bind_param("i", $id);
        $upFail->execute();
        $upFail->close();
        $left = max(0, 4 - $attempts);
        return ['ok' => false, 'reason' => 'invalid', 'attempts_left' => $left];
    }

    $id = (int) $row['id'];
    $up = $conn->prepare("UPDATE {$table} SET used_at = NOW() WHERE id = ?");
    $up->bind_param("i", $id);
    $up->execute();
    $up->close();

    return ['ok' => true];
}

function mark_email_verified(mysqli $conn, int $userId): void
{
    $stmt = $conn->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}

function get_latest_auth_code_meta(mysqli $conn, int $userId, string $purpose): ?array
{
    $table = is_pending_auth_purpose($purpose) ? 'pending_auth_codes' : 'auth_codes';
    $idCol = is_pending_auth_purpose($purpose) ? 'pending_id' : 'user_id';

    $stmt = $conn->prepare(
        "SELECT id, attempts, created_at, expires_at
         FROM {$table}
         WHERE {$idCol} = ? AND purpose = ? AND used_at IS NULL
         ORDER BY id DESC
         LIMIT 1"
    );
    $stmt->bind_param("is", $userId, $purpose);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

function can_resend_auth_code(mysqli $conn, int $userId, string $purpose, int $cooldownSeconds = 30): array
{
    $meta = get_latest_auth_code_meta($conn, $userId, $purpose);
    if (!$meta) {
        return ['allowed' => true, 'wait' => 0];
    }

    $createdTs = strtotime((string) ($meta['created_at'] ?? ''));
    if (!$createdTs) {
        return ['allowed' => true, 'wait' => 0];
    }

    $elapsed = time() - $createdTs;
    if ($elapsed >= $cooldownSeconds) {
        return ['allowed' => true, 'wait' => 0];
    }

    return ['allowed' => false, 'wait' => $cooldownSeconds - $elapsed];
}

function upsert_pending_registration(mysqli $conn, string $name, string $email, string $passwordHash): int
{
    $sql = "INSERT INTO pending_registrations (name, email, password_hash)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                password_hash = VALUES(password_hash),
                created_at = CURRENT_TIMESTAMP";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $passwordHash);
    $stmt->execute();
    $stmt->close();

    $get = $conn->prepare("SELECT id FROM pending_registrations WHERE email = ? LIMIT 1");
    $get->bind_param("s", $email);
    $get->execute();
    $res = $get->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $get->close();

    return (int) ($row['id'] ?? 0);
}

function get_pending_registration_by_id(mysqli $conn, int $pendingId): ?array
{
    $stmt = $conn->prepare("SELECT id, name, email, password_hash FROM pending_registrations WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $pendingId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

function create_user_from_pending_registration(mysqli $conn, int $pendingId): ?int
{
    $pending = get_pending_registration_by_id($conn, $pendingId);
    if (!$pending) {
        return null;
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $check->bind_param("s", $pending['email']);
    $check->execute();
    $res = $check->get_result();
    $existing = $res ? $res->fetch_assoc() : null;
    $check->close();

    if ($existing) {
        $delete = $conn->prepare("DELETE FROM pending_registrations WHERE id = ?");
        $delete->bind_param("i", $pendingId);
        $delete->execute();
        $delete->close();
        return (int) $existing['id'];
    }

    $insert = $conn->prepare("INSERT INTO users (name, email, password, role, email_verified) VALUES (?, ?, ?, 'user', 1)");
    $insert->bind_param("sss", $pending['name'], $pending['email'], $pending['password_hash']);
    $ok = $insert->execute();
    $newId = $ok ? (int) $insert->insert_id : 0;
    $insert->close();

    if ($newId > 0) {
        $delete = $conn->prepare("DELETE FROM pending_registrations WHERE id = ?");
        $delete->bind_param("i", $pendingId);
        $delete->execute();
        $delete->close();
        return $newId;
    }

    return null;
}
