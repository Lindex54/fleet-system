<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Builds the project's base path from the current document root.
 */
function driverLoginVerificationBasePath(): string
{
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $projectRoot = realpath(dirname(__DIR__));

    if ($documentRoot && $projectRoot && substr($projectRoot, 0, strlen($documentRoot)) === $documentRoot) {
        return str_replace('\\', '/', substr($projectRoot, strlen($documentRoot)));
    }

    return '';
}

/**
 * Returns the asset URL with a cache-busting timestamp.
 */
function driverLoginVerificationAssetUrl(string $relativePath): string
{
    $projectRoot = realpath(dirname(__DIR__));
    $normalizedPath = str_replace('/', DIRECTORY_SEPARATOR, ltrim($relativePath, '/'));
    $fullPath = $projectRoot . DIRECTORY_SEPARATOR . $normalizedPath;
    $version = file_exists($fullPath) ? filemtime($fullPath) : time();

    return driverLoginVerificationBasePath() . '/' . ltrim($relativePath, '/') . '?v=' . $version;
}

/**
 * Uses the existing login-page imagery and branding paths.
 */
function driverLoginVerificationThemeAssets(): array
{
    $basePath = driverLoginVerificationBasePath();

    return [
        'base_path' => $basePath,
        'vehicle_image' => $basePath . '/assets/images/hero/login-fleet-vehicle-removebg-preview.png',
        'branding_logo' => $basePath . '/assets/images/branding/logo1.png',
        'css' => driverLoginVerificationAssetUrl('assets/css/app.css'),
        'app_js' => driverLoginVerificationAssetUrl('assets/js/app.js'),
        'module_js' => driverLoginVerificationAssetUrl('assets/js/module-modals.js'),
        'jquery_js' => driverLoginVerificationAssetUrl('assets/js/fleet-jquery.js'),
    ];
}

/**
 * Creates the isolated verification table if it does not already exist.
 */
function driverLoginVerificationEnsureTable(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS driver_login_verifications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL,
            token CHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            confirmed_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_driver_login_verifications_token (token),
            KEY idx_driver_login_verifications_email (email),
            KEY idx_driver_login_verifications_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}

/**
 * Finds an active driver account by the username and Gmail address entered on the login page.
 */
function driverLoginVerificationFindDriverAccount(PDO $pdo, string $username, string $email): ?array
{
    $statement = $pdo->prepare(
        'SELECT
            u.id AS user_id,
            u.username,
            u.name,
            u.email,
            u.status AS user_status,
            u.must_change_password,
            d.id AS driver_id,
            d.full_name,
            d.status AS driver_status
         FROM users u
         INNER JOIN drivers d ON d.user_id = u.id
         WHERE u.role = \'driver\'
           AND u.username = :username
           AND LOWER(u.email) = LOWER(:email)
         LIMIT 1'
    );
    $statement->execute([
        'username' => $username,
        'email' => $email,
    ]);
    $driver = $statement->fetch();

    return $driver ?: null;
}

/**
 * Stores a fresh verification request for a driver login setup email.
 */
function driverLoginVerificationCreateRequest(PDO $pdo, string $username, string $email): array
{
    $token = bin2hex(random_bytes(32));
    $expiresAt = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

    $insert = $pdo->prepare(
        'INSERT INTO driver_login_verifications (username, email, token, expires_at, confirmed_at)
         VALUES (:username, :email, :token, :expires_at, NULL)'
    );
    $insert->execute([
        'username' => $username,
        'email' => $email,
        'token' => $token,
        'expires_at' => $expiresAt,
    ]);

    return [
        'token' => $token,
        'expires_at' => $expiresAt,
    ];
}

/**
 * Finds a verification request by token.
 */
function driverLoginVerificationFindRequest(PDO $pdo, string $token): ?array
{
    $select = $pdo->prepare(
        'SELECT id, username, email, token, expires_at, confirmed_at, created_at
         FROM driver_login_verifications
         WHERE token = :token
         LIMIT 1'
    );
    $select->execute(['token' => $token]);
    $verification = $select->fetch();

    return $verification ?: null;
}

/**
 * Completes activation by saving a private password and marking the verification as confirmed.
 */
function driverLoginVerificationActivateDriver(PDO $pdo, array $verification, string $newPassword): array
{
    $driver = driverLoginVerificationFindDriverAccount($pdo, (string) $verification['username'], (string) $verification['email']);

    if (!$driver) {
        throw new RuntimeException('The driver account linked to this verification request no longer exists.');
    }

    $pdo->beginTransaction();

    try {
        $updateUser = $pdo->prepare(
            'UPDATE users
             SET password_hash = :password_hash,
                 must_change_password = 0
             WHERE id = :user_id
               AND role = \'driver\''
        );
        $updateUser->execute([
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'user_id' => (int) $driver['user_id'],
        ]);

        $updateVerification = $pdo->prepare(
            'UPDATE driver_login_verifications
             SET confirmed_at = NOW()
             WHERE id = :id'
        );
        $updateVerification->execute([
            'id' => (int) $verification['id'],
        ]);

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }

    return $driver;
}

/**
 * Starts a driver session immediately after successful email verification and password creation.
 */
function driverLoginVerificationStartDriverSession(PDO $pdo, array $driver): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    session_regenerate_id(true);
    unset($_SESSION['admin_user_id'], $_SESSION['admin_role']);

    $_SESSION['user_id'] = (int) $driver['user_id'];
    $_SESSION['username'] = (string) $driver['username'];
    $_SESSION['user_name'] = (string) ($driver['name'] ?? $driver['full_name'] ?? '');
    $_SESSION['user_role'] = 'driver';
    $_SESSION['driver_id'] = (int) $driver['driver_id'];
    $_SESSION['must_change_password'] = 0;

    $updateLogin = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
    $updateLogin->execute(['id' => (int) $driver['user_id']]);
}

/**
 * Loads SMTP settings from the dedicated configuration file.
 */
function driverLoginVerificationMailConfig(): array
{
    $config = require __DIR__ . '/../config/mail.php';

    if (!is_array($config)) {
        throw new RuntimeException('Mail configuration is invalid.');
    }

    return $config;
}

/**
 * Builds the verification URL sent inside the test email.
 */
function driverLoginVerificationUrl(string $token): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = driverLoginVerificationBasePath();

    return sprintf(
        '%s://%s%s/verify-driver-login.php?token=%s',
        $scheme,
        $host,
        $basePath,
        urlencode($token)
    );
}

/**
 * Sends the PHPMailer verification email using the external SMTP config.
 */
function driverLoginVerificationSendEmail(string $username, string $email, string $token): void
{
    $mailConfig = driverLoginVerificationMailConfig();
    $verificationUrl = driverLoginVerificationUrl($token);

    $mail = new PHPMailer(true);

    try {
        // Keep debug configurable, but default it to 0 so SMTP traffic is not printed into the page.
        $mail->SMTPDebug = (int) ($mailConfig['debug'] ?? 0);
        $mail->Debugoutput = static function (string $message, int $level): void {
            error_log(sprintf('PHPMailer debug [%d]: %s', $level, trim($message)));
        };

        // Send through Gmail SMTP instead of PHP's local mail transport.
        $mail->isSMTP();
        $mail->Host = (string) ($mailConfig['host'] ?? '');

        // Gmail requires authenticated SMTP sessions.
        $mail->SMTPAuth = true;
        $mail->Username = (string) ($mailConfig['username'] ?? '');
        $mail->Password = (string) ($mailConfig['password'] ?? '');

        // Match Gmail's TLS transport on port 587.
        $encryption = strtolower((string) ($mailConfig['encryption'] ?? 'tls'));
        if ($encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        $mail->Port = (int) ($mailConfig['port'] ?? 587);

        // Use the same Gmail address as the authenticated sender during testing.
        $fromAddress = (string) ($mailConfig['from_address'] ?? $mail->Username);
        $fromName = (string) ($mailConfig['from_name'] ?? 'Fleet Management System');

        $mail->setFrom($fromAddress, $fromName);
        $mail->addAddress($email, $username);
        $mail->isHTML(false);
        $mail->Subject = 'Driver Login Verification';

        // The body mirrors the requested login verification wording for this isolated test flow.
        $mail->Body = "Hello {$username},\n\n"
            . "A login verification request was submitted for your account.\n\n"
            . "Please click the link below to confirm your email address:\n\n"
            . $verificationUrl . "\n\n"
            . "If you did not request this verification, please ignore this email.\n\n"
            . "Regards,\n"
            . "Fleet Management System";

        $mail->send();
    } catch (Exception $exception) {
        // Return PHPMailer's exact error so the test page can report send failures clearly.
        throw new RuntimeException('Verification email could not be sent: ' . $mail->ErrorInfo, 0, $exception);
    }
}

/**
 * Stores the welcome message in the communications tables so it appears in the driver dashboard.
 */
function driverLoginVerificationStoreWelcomeMessage(
    PDO $pdo,
    array $driver,
    string $deliveryStatus = 'sent',
    ?string $sentAt = null,
    ?string $failureReason = null
): void
{
    $subject = 'Welcome to Fleet Management System';
    $driverId = (int) ($driver['driver_id'] ?? 0);
    $userId = (int) ($driver['user_id'] ?? 0);
    $recipientEmail = strtolower((string) ($driver['email'] ?? ''));
    $recipientName = (string) ($driver['name'] ?? $driver['full_name'] ?? 'Driver');

    if ($driverId <= 0 || $userId <= 0 || $recipientEmail === '') {
        return;
    }

    $existing = $pdo->prepare(
        "SELECT cr.id
         FROM communication_recipients cr
         INNER JOIN communications c ON c.id = cr.communication_id
         WHERE cr.driver_id = :driver_id
           AND c.subject = :subject
         LIMIT 1"
    );
    $existing->execute([
        'driver_id' => $driverId,
        'subject' => $subject,
    ]);

    // Guard against duplicate welcome messages if the activation flow is retried.
    if ($existing->fetchColumn() !== false) {
        return;
    }

    $message = "Hello {$recipientName},\n\n"
        . "Welcome to the Fleet Management System.\n\n"
        . "Your driver account is now active and ready to use. We are glad to have you on board.\n\n"
        . "Regards,\n"
        . "Transport Office";

    $pdo->beginTransaction();

    try {
        $insertCommunication = $pdo->prepare(
            "INSERT INTO communications (sender_user_id, subject, message, message_type)
             VALUES (NULL, :subject, :message, 'system')"
        );
        $insertCommunication->execute([
            'subject' => $subject,
            'message' => $message,
        ]);

        $communicationId = (int) $pdo->lastInsertId();

        $insertRecipient = $pdo->prepare(
            "INSERT INTO communication_recipients (
                communication_id,
                driver_id,
                user_id,
                recipient_name,
                recipient_email,
                recipient_type,
                delivery_status,
                sent_at,
                failure_reason
             ) VALUES (
                :communication_id,
                :driver_id,
                :user_id,
                :recipient_name,
                :recipient_email,
                'driver',
                :delivery_status,
                :sent_at,
                :failure_reason
             )"
        );
        $insertRecipient->execute([
            'communication_id' => $communicationId,
            'driver_id' => $driverId,
            'user_id' => $userId,
            'recipient_name' => $recipientName,
            'recipient_email' => $recipientEmail,
            'delivery_status' => $deliveryStatus,
            'sent_at' => $sentAt,
            'failure_reason' => $failureReason,
        ]);

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

/**
 * Sends a one-time welcome email and records it in the driver message history.
 */
function driverLoginVerificationSendWelcomeNotification(PDO $pdo, array $driver): void
{
    $mailConfig = require __DIR__ . '/../config/mail.php';
    if (!is_array($mailConfig)) {
        throw new RuntimeException('Mail configuration is invalid.');
    }

    $recipientEmail = strtolower((string) ($driver['email'] ?? ''));
    $recipientName = (string) ($driver['name'] ?? $driver['full_name'] ?? 'Driver');
    $subject = 'Welcome to Fleet Management System';
    $message = "Hello {$recipientName},\n\n"
        . "Welcome to the Fleet Management System.\n\n"
        . "Your driver account is now active and ready to use. We are glad to have you on board.\n\n"
        . "Regards,\n"
        . "Transport Office";

    $deliveryStatus = 'pending';
    $sentAt = null;
    $failureReason = null;

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string) ($mailConfig['host'] ?? '');
        $mail->SMTPAuth = true;
        $mail->Username = (string) ($mailConfig['username'] ?? '');
        $mail->Password = (string) ($mailConfig['password'] ?? '');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) ($mailConfig['port'] ?? 587);

        $fromAddress = (string) ($mailConfig['from_address'] ?? $mail->Username);
        $fromName = 'Transport Office';

        $mail->setFrom($fromAddress, $fromName);
        $mail->addAddress($recipientEmail, $recipientName);
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->send();

        $deliveryStatus = 'sent';
        $sentAt = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    } catch (Throwable $exception) {
        $deliveryStatus = 'failed';
        $failureReason = $exception->getMessage();
        error_log('Welcome email failed: ' . $exception->getMessage());
    }

    driverLoginVerificationStoreWelcomeMessage($pdo, $driver, $deliveryStatus, $sentAt, $failureReason);
}
