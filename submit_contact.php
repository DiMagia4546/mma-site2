<?php
session_start();
include 'db.php';
include 'security.php';
include 'mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contacto.php');
    exit;
}

verify_csrf_or_die();

if (!is_logged_in()) {
    $_SESSION['error'] = 'Precisas de iniciar sessão para enviar mensagem.';
    header('Location: contacto.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$sessionEmail = trim((string) ($_SESSION['user_email'] ?? ''));
$sessionUserId = (int) ($_SESSION['user_id'] ?? 0);

if ($name === '' || $email === '' || $message === '') {
    $_SESSION['error'] = 'Preenche todos os campos.';
    header('Location: contacto.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Email inválido.';
    header('Location: contacto.php');
    exit;
}

if ($sessionUserId <= 0 || !filter_var($sessionEmail, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Sessão inválida. Inicia sessão novamente.';
    header('Location: login.php');
    exit;
}

$stmtUser = $conn->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
$stmtUser->bind_param('i', $sessionUserId);
$stmtUser->execute();
$resUser = $stmtUser->get_result();
$userRow = $resUser ? $resUser->fetch_assoc() : null;
$stmtUser->close();

if (!$userRow || strcasecmp(trim((string) ($userRow['email'] ?? '')), $sessionEmail) !== 0) {
    $_SESSION['error'] = 'Não foi possível validar o teu email registado. Inicia sessão novamente.';
    header('Location: login.php');
    exit;
}

if (strcasecmp($email, $sessionEmail) !== 0) {
    $_SESSION['error'] = 'Só podes enviar mensagem com o email registado na tua conta.';
    header('Location: contacto.php');
    exit;
}

$conn->query(
    "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(120) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
);

$stmt = $conn->prepare('INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $name, $email, $message);
$saved = $stmt->execute();
$stmt->close();

$mailSent = send_contact_email_to_team($name, $email, $message);

if ($saved && $mailSent) {
    $_SESSION['success'] = 'Mensagem enviada com sucesso para a equipa MMA 360.';
} elseif ($saved && !$mailSent) {
    $_SESSION['error'] = 'Mensagem guardada, mas o envio por email falhou. Verifica a configuracao SMTP.';
} elseif (!$saved && $mailSent) {
    $_SESSION['success'] = 'Mensagem enviada por email com sucesso.';
} else {
    $_SESSION['error'] = 'Erro ao enviar mensagem.';
}

header('Location: contacto.php');
exit;
