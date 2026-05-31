<?php
// Shared configuration and helper functions for Monlei SiPao portal.
// Store secrets in environment variables. Do not commit real credentials.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'monleisiopao';

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_error) {
    http_response_code(500);
    exit('Database connection failed.');
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_role(?string $role = null): void {
    $user = current_user();
    if (!$user) {
        header('Location: /monleisiopao/login.php');
        exit;
    }
    if ($role && ($user['role'] ?? null) !== $role) {
        http_response_code(403);
        exit('Forbidden');
    }
}

function redirect_with_message(string $location, string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    header('Location: ' . $location);
    exit;
}

function consume_flash(): ?array {
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function mailer(): PHPMailer\PHPMailer\PHPMailer {
    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        require __DIR__ . '/vendor/autoload.php';
    }
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    // Use env vars; fall back to placeholders if not set.
    $mail->Username = getenv('SMTP_USER') ?: 'jadesupremo0@gmail.com';
    $mail->Password = getenv('SMTP_PASS') ?: 'lfns yegc vqba ywbq';
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    // Optional debug to error_log when SMTP_DEBUG=1
    if (getenv('SMTP_DEBUG') === '1') {
        $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
        $mail->Debugoutput = 'error_log';
    }
    // Loosen SSL checks only if needed (self-signed envs)
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];
    $mail->setFrom($mail->Username, 'Monlei SiPao');
    return $mail;
}

function hash_password(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

function sanitize_email(?string $email): ?string {
    return filter_var($email, FILTER_VALIDATE_EMAIL) ?: null;
}

function sanitize_text(?string $text): string {
    return trim((string)($text ?? ''));
}
?>
