<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/driver-login-verification.php';

$themeAssets = driverLoginVerificationThemeAssets();
$feedback = null;
$username = '';
$email = '';

try {
    // Ensure the isolated testing table exists before processing any form submissions.
    driverLoginVerificationEnsureTable(fleetDb());
} catch (Throwable $exception) {
    $feedback = [
        'type' => 'error',
        'message' => 'The verification test table could not be prepared. Please check the database connection.',
    ];
    error_log('Driver verification table error: ' . $exception->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $feedback === null) {
    // Sanitize incoming values before validation and database storage.
    $username = trim((string) filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $email = trim((string) filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));

    // Validate required fields and email format before generating a token.
    if ($username === '') {
        $feedback = [
            'type' => 'error',
            'message' => 'Username is required.',
        ];
    } elseif ($email === '') {
        $feedback = [
            'type' => 'error',
            'message' => 'Email address is required.',
        ];
    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $feedback = [
            'type' => 'error',
            'message' => 'Enter a valid email address.',
        ];
    } else {
        try {
            $pdo = fleetDb();

            // Generate a cryptographically secure verification token and a one-hour expiry window.
            $token = bin2hex(random_bytes(32));
            $expiresAt = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

            // Store the sanitized request in its own testing table without affecting the real login flow.
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

            // Send the verification email through PHPMailer using the external SMTP configuration.
            driverLoginVerificationSendEmail($username, $email, $token);

            $feedback = [
                'type' => 'success',
                'message' => 'Verification email sent successfully. Check the submitted inbox for the confirmation link.',
            ];
        } catch (Throwable $exception) {
            $feedback = [
                'type' => 'error',
                'message' => 'The verification request was saved, but the email could not be sent. Please review config/mail.php and your SMTP credentials.',
            ];
            error_log('Driver verification test error: ' . $exception->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Login Verification Test</title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($themeAssets['branding_logo'], ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($themeAssets['css'], ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="bg-fleet-canvas text-fleet-ink antialiased">
<main class="login-page">
    <div class="login-ambient" aria-hidden="true">
        <div class="login-orb login-orb-primary"></div>
        <div class="login-orb login-orb-sky"></div>
        <div class="login-orb login-orb-warm"></div>
    </div>

    <section class="login-layout">
        <div class="login-panel login-panel-story">
            <div class="login-panel-grid"></div>
            <div class="login-panel-blob login-panel-blob-teal"></div>
            <div class="login-panel-blob login-panel-blob-purple"></div>
            <div class="login-panel-blob login-panel-blob-blue"></div>

            <div class="login-panel-brand">
                <div class="login-panel-badge">
                    <img src="<?= htmlspecialchars($themeAssets['branding_logo'], ENT_QUOTES, 'UTF-8'); ?>" alt="BUESMIS logo">
                </div>
                <div class="login-panel-brand-copy">
                    <span class="login-panel-brand-label">Buesmis Fleet</span>
                    <span class="login-panel-brand-name">Fleet Management</span>
                </div>
            </div>

            <div class="login-panel-hero">
                <span class="login-panel-tag">PHPMailer . SMTP . Verification</span>
                <h1>Driver <span>email verification</span><br>test module</h1>
                <p>This standalone page tests SMTP delivery and token-based email verification without touching the existing driver login system.</p>
            </div>

            <div class="login-panel-stats">
                <div class="login-panel-stat">
                    <span class="login-panel-stat-num">SMTP</span>
                    <span class="login-panel-stat-label">PHPMailer delivery</span>
                </div>
                <div class="login-panel-stat">
                    <span class="login-panel-stat-num">Token</span>
                    <span class="login-panel-stat-label">Secure verification</span>
                </div>
                <div class="login-panel-stat">
                    <span class="login-panel-stat-num">1 Hour</span>
                    <span class="login-panel-stat-label">Expiry window</span>
                </div>
            </div>

            <div class="login-panel-vehicle" aria-hidden="true">
                <img src="<?= htmlspecialchars($themeAssets['vehicle_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Fleet vehicle">
            </div>
        </div>

        <div class="login-panel login-panel-form">
            <section class="login-card">
                <p class="login-card-tag">Test Module</p>
                <h2>Verify Email</h2>
                <p class="login-card-sub">Submit a username and email address to test PHPMailer and the isolated verification workflow.</p>

                <?php if ($feedback !== null): ?>
                    <div class="login-card-alert" style="<?= $feedback['type'] === 'success' ? 'border-color:#bbf7d0;background:#f0fdf4;color:#15803d;' : ''; ?>">
                        <?= htmlspecialchars($feedback['message'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="login-card-field">
                        <label for="test-username">Username</label>
                        <div class="login-input-shell">
                            <input id="test-username" name="username" type="text" required class="login-input" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Enter test username">
                        </div>
                    </div>

                    <div class="login-card-field">
                        <label for="test-email">Email Address</label>
                        <div class="login-input-shell">
                            <input id="test-email" name="email" type="email" required class="login-input" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Enter test email address">
                        </div>
                    </div>

                    <button type="submit" class="login-submit" data-loading-text="Sending Verification...">
                        <span>Send Verification</span>
                        <span class="login-submit-arrow" aria-hidden="true">&rarr;</span>
                    </button>
                </form>

                <div class="login-form-divider">
                    <span>Isolated test flow</span>
                </div>

                <p class="login-card-footer">
                    This page stores verification requests in <code>driver_login_verifications</code> only.
                </p>
            </section>
        </div>
    </section>
</main>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= htmlspecialchars($themeAssets['app_js'], ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?= htmlspecialchars($themeAssets['module_js'], ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?= htmlspecialchars($themeAssets['jquery_js'], ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
