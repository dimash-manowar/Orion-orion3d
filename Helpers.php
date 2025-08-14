<?php
// URL base del proyecto
function base_url(): string
{
    return BASE_URL;
}

// URL base de assets
function media(): string
{
    return BASE_URL . 'Assets/';
}

/**
 * Requiere rol (usa $_SESSION['user']['rol'])
 */
function requireRole(array $roles = []): void
{
    if (empty($_SESSION['user'])) {
        header('Location: ' . BASE_URL . 'Auth');
        exit;
    }
    $usuario = $_SESSION['user'];
    if (!in_array($usuario['rol'] ?? 'usuario', $roles, true)) {
        $_SESSION['alert'] = [
            'icon'  => 'error',
            'title' => 'Acceso denegado',
            'text'  => 'No tienes permiso para acceder a esta sección.'
        ];
        header('Location: ' . BASE_URL . 'Home');
        exit;
    }
}

/* ----------------------------
 *  CSRF helpers (UNIFICADOS)
 *  Usan SIEMPRE $_SESSION['csrf']
 * ---------------------------- */
function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' .
        htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_verify(?string $t): bool
{
    return isset($_SESSION['csrf']) && is_string($t) && hash_equals($_SESSION['csrf'], (string)$t);
}

/* ----------------------------
 *  Email (PHPMailer)
 * ---------------------------- */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_mail(string $toEmail, string $toName, string $subject, string $html): bool
{
    // Composer autoload
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST; // Config.php
        $mail->SMTPAuth   = true;
        $mail->Username   = EMAIL_REMITENTE;
        $mail->Password   = PASSWORD_EMAIL;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom(EMAIL_REMITENTE, NOMBRE_REMITENTE ?? 'Orion3D');
        $mail->addAddress($toEmail, $toName ?: $toEmail);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('send_mail error: '.$mail->ErrorInfo);
        return false;
    }
}
