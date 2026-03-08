<?php

if (file_exists(__DIR__ . '/mail_config.php')) {
    require_once __DIR__ . '/mail_config.php';
}

function mailer_config(string $key, string $fallback = ''): string
{
    $envValue = getenv($key);
    if ($envValue !== false && trim((string) $envValue) !== '') {
        return trim((string) $envValue);
    }

    if (defined($key)) {
        $constantValue = constant($key);
        if (is_string($constantValue) && trim($constantValue) !== '') {
            return trim($constantValue);
        }
    }

    return $fallback;
}

function mailer_from_address(): string
{
    $gmailUser = mailer_config('MMA_GMAIL_USER');
    if ($gmailUser !== '' && filter_var($gmailUser, FILTER_VALIDATE_EMAIL)) {
        return $gmailUser;
    }

    $from = mailer_config('MMA_MAIL_FROM');
    if (!$from || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $host = preg_replace('/:\d+$/', '', (string) $host);
        if (!$host) {
            $host = 'localhost';
        }
        $from = 'no-reply@' . $host;
    }

    return $from;
}

function smtp_read_response($socket): string
{
    $response = '';
    while (!feof($socket)) {
        $line = fgets($socket, 512);
        if ($line === false) {
            break;
        }

        $response .= $line;
        if (strlen($line) >= 4 && $line[3] === ' ') {
            break;
        }
    }

    return $response;
}

function smtp_expect($socket, array $expectedCodes): bool
{
    $response = smtp_read_response($socket);
    if ($response === '') {
        return false;
    }

    $code = substr($response, 0, 3);
    return in_array($code, $expectedCodes, true);
}

function smtp_send_line($socket, string $line): bool
{
    return fwrite($socket, $line . "\r\n") !== false;
}

function smtp_send_gmail(string $toEmail, string $subject, string $message, string $fromEmail, string $siteName): bool
{
    $smtpUser = mailer_config('MMA_GMAIL_USER');
    $smtpPass = mailer_config('MMA_GMAIL_APP_PASSWORD');

    if ($smtpUser === '' || $smtpPass === '') {
        return false;
    }

    if (!filter_var($smtpUser, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $socket = @stream_socket_client('tcp://smtp.gmail.com:587', $errno, $errstr, 20);
    if (!$socket) {
        return false;
    }

    stream_set_timeout($socket, 20);

    if (!smtp_expect($socket, ['220'])) {
        fclose($socket);
        return false;
    }

    if (!smtp_send_line($socket, 'EHLO localhost') || !smtp_expect($socket, ['250'])) {
        fclose($socket);
        return false;
    }

    if (!smtp_send_line($socket, 'STARTTLS') || !smtp_expect($socket, ['220'])) {
        fclose($socket);
        return false;
    }

    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($socket);
        return false;
    }

    if (!smtp_send_line($socket, 'EHLO localhost') || !smtp_expect($socket, ['250'])) {
        fclose($socket);
        return false;
    }

    if (!smtp_send_line($socket, 'AUTH LOGIN') || !smtp_expect($socket, ['334'])) {
        fclose($socket);
        return false;
    }

    if (!smtp_send_line($socket, base64_encode($smtpUser)) || !smtp_expect($socket, ['334'])) {
        fclose($socket);
        return false;
    }

    if (!smtp_send_line($socket, base64_encode($smtpPass)) || !smtp_expect($socket, ['235'])) {
        fclose($socket);
        return false;
    }

    if (!smtp_send_line($socket, 'MAIL FROM:<' . $fromEmail . '>') || !smtp_expect($socket, ['250'])) {
        fclose($socket);
        return false;
    }

    if (!smtp_send_line($socket, 'RCPT TO:<' . $toEmail . '>') || !smtp_expect($socket, ['250', '251'])) {
        fclose($socket);
        return false;
    }

    if (!smtp_send_line($socket, 'DATA') || !smtp_expect($socket, ['354'])) {
        fclose($socket);
        return false;
    }

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = [];
    $headers[] = 'Date: ' . date('r');
    $headers[] = 'From: ' . $siteName . ' <' . $fromEmail . '>';
    $headers[] = 'To: <' . $toEmail . '>';
    $headers[] = 'Subject: ' . $encodedSubject;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'Content-Transfer-Encoding: 8bit';

    $normalizedBody = str_replace(["\r\n", "\r"], "\n", $message);
    $normalizedBody = str_replace("\n.", "\n..", $normalizedBody);
    $normalizedBody = str_replace("\n", "\r\n", $normalizedBody);

    $data = implode("\r\n", $headers) . "\r\n\r\n" . $normalizedBody . "\r\n.";
    if (!smtp_send_line($socket, $data) || !smtp_expect($socket, ['250'])) {
        fclose($socket);
        return false;
    }

    smtp_send_line($socket, 'QUIT');
    fclose($socket);
    return true;
}

function send_welcome_email(string $name, string $email): bool
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $siteName = mailer_config('MMA_SITE_NAME', 'MMA 360');
    $safeName = trim($name) !== '' ? trim($name) : 'lutador';
    $subject = $siteName . ' - Conta criada com sucesso';

    $message = "Ola {$safeName},\r\n\r\n";
    $message .= "A tua conta foi criada com sucesso no {$siteName}.\r\n";
    $message .= "Ja podes iniciar sessao e personalizar a tua experiencia.\r\n\r\n";
    $message .= "Obrigado,\r\nEquipa {$siteName}";

    $from = mailer_from_address();
    if (smtp_send_gmail($email, $subject, $message, $from, $siteName)) {
        return true;
    }

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'From: ' . $siteName . ' <' . $from . '>';
    $headers[] = 'Reply-To: ' . $from;

    return @mail($email, $subject, $message, implode("\r\n", $headers));
}

function send_auth_code_email(string $name, string $email, string $code, string $context = 'login'): bool
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $siteName = mailer_config('MMA_SITE_NAME', 'MMA 360');
    $safeName = trim($name) !== '' ? trim($name) : 'utilizador';
    $isRegister = $context === 'register';
    $subject = $isRegister
        ? $siteName . ' - Confirmacao de email'
        : $siteName . ' - Codigo de seguranca de login';

    $message = "Ola {$safeName},\r\n\r\n";
    if ($isRegister) {
        $message .= "Usa este codigo para confirmar o teu email:\r\n";
    } else {
        $message .= "Usa este codigo para confirmar o teu login:\r\n";
    }
    $message .= "Codigo: {$code}\r\n";
    $message .= "Validade: 10 minutos.\r\n\r\n";
    $message .= "Se nao foste tu, ignora este email.\r\n\r\n";
    $message .= "Equipa {$siteName}";

    $from = mailer_from_address();
    if (smtp_send_gmail($email, $subject, $message, $from, $siteName)) {
        return true;
    }

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'From: ' . $siteName . ' <' . $from . '>';
    $headers[] = 'Reply-To: ' . $from;

    return @mail($email, $subject, $message, implode("\r\n", $headers));
}

function send_contact_email_to_team(string $fromName, string $fromEmail, string $content): bool
{
    if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $siteName = mailer_config('MMA_SITE_NAME', 'MMA 360');
    $teamEmail = mailer_config('MMA_CONTACT_TEAM_EMAIL', 'mma360.project@gmail.com');
    if (!filter_var($teamEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $subject = $siteName . ' - Nova mensagem de contacto';
    $message = "Nova mensagem recebida pelo formulario de contacto.\r\n\r\n";
    $message .= "Nome: {$fromName}\r\n";
    $message .= "Email: {$fromEmail}\r\n\r\n";
    $message .= "Mensagem:\r\n{$content}\r\n";

    $from = mailer_from_address();
    if (smtp_send_gmail($teamEmail, $subject, $message, $from, $siteName)) {
        return true;
    }

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'From: ' . $siteName . ' <' . $from . '>';
    $headers[] = 'Reply-To: ' . $fromEmail;

    return @mail($teamEmail, $subject, $message, implode("\r\n", $headers));
}
