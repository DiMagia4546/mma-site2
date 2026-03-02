<?php
session_start();
include 'db.php';
include 'security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contacto.php');
    exit;
}

verify_csrf_or_die();

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

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

if ($stmt->execute()) {
    $_SESSION['success'] = 'Mensagem enviada com sucesso.';
} else {
    $_SESSION['error'] = 'Erro ao enviar mensagem.';
}

header('Location: contacto.php');
exit;
