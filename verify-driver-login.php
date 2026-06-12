<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/driver-login-verification-test.php';

$themeAssets = driverLoginVerificationThemeAssets();
$status = 'error';
$message = 'Invalid verification token.';
$verification = null;
$activatedDriver = null;
$token = trim((string) ($_POST['token'] ?? $_GET['token'] ?? ''));

try {
    $pdo = fleetDb();
    driverLoginVerificationEnsureTable($pdo);

    if ($token !== '') {
        $verification = driverLoginVerificationFindRequest($pdo, $token);
    }

    if (!$verification) {
        $message = 'Invalid verification token.';
    } elseif ((string) $verification['confirmed_at'] !== '') {
        $message = 'This setup link has already been used. Sign in with your username and password.';
    } elseif (strtotime((string) $verification['expires_at']) < time()) {
        $message = 'Token expired. Please request a new driver setup link.';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        // The verification page becomes the password-creation step after a valid email link is opened.
        if (strlen($newPassword) < 8) {
            $message = 'Your new password must be at least 8 characters long.';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'The new password and confirmation do not match.';
        } else {
            $activatedDriver = driverLoginVerificationActivateDriver($pdo, $verification, $newPassword);
            driverLoginVerificationStartDriverSession($pdo, $activatedDriver);
            header('Location: /fleet-system/driver-panel/');
            exit;
        }
    } else {
        $status = 'form';
        $message = 'Email verified. Create your private password to finish driver account setup.';
    }
} catch (Throwable $exception) {
    $status = 'error';
    $message = 'Verification is unavailable right now. Please try again later.';
    error_log('Driver verification lookup error: ' . $exception->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Account Setup</title>
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
                <span class="login-panel-tag">Driver . Email . Password</span>
                <h1>Driver <span>account setup</span><br>made secure</h1>
                <p>This verification link confirms the driver's Gmail access, then lets them create the private password they will use for future sign-ins.</p>
            </div>

            <div class="login-panel-stats">
                <div class="login-panel-stat">
                    <span class="login-panel-stat-num">Username</span>
                    <span class="login-panel-stat-label">Generated at driver setup</span>
                </div>
                <div class="login-panel-stat">
                    <span class="login-panel-stat-num">Gmail</span>
                    <span class="login-panel-stat-label">Verified before activation</span>
                </div>
                <div class="login-panel-stat">
                    <span class="login-panel-stat-num">Password</span>
                    <span class="login-panel-stat-label">Created by the driver</span>
                </div>
            </div>

            <div class="login-panel-vehicle" aria-hidden="true">
                <img src="<?= htmlspecialchars($themeAssets['vehicle_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Fleet vehicle">
            </div>
        </div>

        <div class="login-panel login-panel-form">
            <section class="login-card">
                <p class="login-card-tag">Driver Setup</p>
                <h2>
                    <?php if ($status === 'success'): ?>
                        Password Created
                    <?php elseif ($status === 'form'): ?>
                        Create Password
                    <?php else: ?>
                        Setup Unavailable
                    <?php endif; ?>
                </h2>
                <p class="login-card-sub"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>

                <?php if ($status === 'form' && is_array($verification)): ?>
                    <form method="post">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="login-card-field">
                            <label>Username</label>
                            <div class="login-input-shell">
                                <input type="text" class="login-input" value="<?= htmlspecialchars((string) $verification['username'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                        </div>

                        <div class="login-card-field">
                            <label>Email</label>
                            <div class="login-input-shell">
                                <input type="email" class="login-input" value="<?= htmlspecialchars((string) $verification['email'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                        </div>

                        <div class="login-card-field">
                            <label for="setup-password">New Password</label>
                            <div class="login-input-shell">
                                <input id="setup-password" name="new_password" type="password" minlength="8" required class="login-input" placeholder="Create a private password">
                            </div>
                        </div>

                        <div class="login-card-field">
                            <label for="setup-confirm-password">Confirm Password</label>
                            <div class="login-input-shell">
                                <input id="setup-confirm-password" name="confirm_password" type="password" minlength="8" required class="login-input" placeholder="Repeat your password">
                            </div>
                        </div>

                        <button type="submit" class="login-submit" data-loading-text="Saving Password...">
                            <span>Save Password</span>
                            <span class="login-submit-arrow" aria-hidden="true">&rarr;</span>
                        </button>
                    </form>
                <?php else: ?>
                    <div class="login-card-alert" style="<?= $status === 'success' ? 'border-color:#bbf7d0;background:#f0fdf4;color:#15803d;' : ''; ?>">
                        <?php if ($status === 'success' && is_array($activatedDriver)): ?>
                            Username: <?= htmlspecialchars((string) $activatedDriver['username'], ENT_QUOTES, 'UTF-8'); ?><br>
                            Email: <?= htmlspecialchars((string) $activatedDriver['email'], ENT_QUOTES, 'UTF-8'); ?><br><br>
                            Your driver account is now ready for normal password sign-in.
                        <?php else: ?>
                            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                    </div>

                    <div class="login-form-divider">
                        <span>Next step</span>
                    </div>

                    <p class="login-card-footer">
                        <a href="<?= htmlspecialchars($themeAssets['base_path'] . '/login', ENT_QUOTES, 'UTF-8'); ?>" class="login-card-link">Return to the sign-in page</a>
                    </p>
                <?php endif; ?>
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
