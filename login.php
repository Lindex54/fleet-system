<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/activity-tracker.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$errorMessage = '';
$username = trim((string) ($_POST['username'] ?? ''));
$basePath = '';
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$projectRoot = realpath(__DIR__);

if ($documentRoot && $projectRoot && substr($projectRoot, 0, strlen($documentRoot)) === $documentRoot) {
    $basePath = str_replace('\\', '/', substr($projectRoot, strlen($documentRoot)));
}

$loginVehicleImage = ($basePath ?: '') . '/assets/images/hero/login-fleet-vehicle-removebg-preview.png';
$brandingLogoImage = ($basePath ?: '') . '/assets/images/branding/logo1.png';

function loginIsAdminUsername(string $username): bool
{
    return preg_match('/^BUESMISadmin\d{3}$/i', $username) === 1;
}

function loginIsDriverUsername(string $username): bool
{
    return preg_match('/^BUESMIS(?!admin)[A-Z]{4}\d{3}$/i', $username) === 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $errorMessage = 'Enter your username and password.';
    } else {
        try {
            $pdo = fleetDb();
            $statement = $pdo->prepare(
                'SELECT
                    u.id AS user_id,
                    u.username,
                    u.name,
                    u.email,
                    u.password_hash,
                    u.role,
                    u.status AS user_status,
                    u.must_change_password,
                    d.id AS driver_id,
                    d.status AS driver_status
                FROM users u
                LEFT JOIN drivers d ON d.user_id = u.id
                WHERE u.username = :login_username OR u.email = :login_email
                LIMIT 1'
            );
            $statement->execute([
                'login_username' => $username,
                'login_email' => $username,
            ]);
            $user = $statement->fetch();

            if (!$user || !password_verify($password, (string) $user['password_hash'])) {
                fleetTrackAuthEvent([
                    'user_id' => $user['user_id'] ?? null,
                    'username' => $username,
                    'name' => $user['name'] ?? null,
                    'email' => $user['email'] ?? null,
                    'role' => $user['role'] ?? null,
                    'event_type' => 'login_failed',
                    'event_description' => 'Failed login attempt',
                ]);
                $errorMessage = 'Invalid username or password.';
            } elseif ((string) $user['user_status'] !== 'active') {
                $errorMessage = 'This account is not active.';
            } elseif ((string) $user['role'] === 'driver' && ($user['driver_id'] === null || (string) $user['driver_status'] !== 'active')) {
                $errorMessage = 'This driver account is not available.';
            } elseif ((string) $user['role'] === 'admin' && !loginIsAdminUsername((string) $user['username'])) {
                $errorMessage = 'This admin account username is not valid.';
            } elseif ((string) $user['role'] === 'driver' && !loginIsDriverUsername((string) $user['username'])) {
                $errorMessage = 'This driver account username is not valid.';
            } else {
                session_regenerate_id(true);
                unset($_SESSION['driver_id'], $_SESSION['admin_user_id'], $_SESSION['admin_role']);

                $_SESSION['user_id'] = (int) $user['user_id'];
                $_SESSION['username'] = (string) $user['username'];
                $_SESSION['user_name'] = (string) $user['name'];
                $_SESSION['user_role'] = (string) $user['role'];
                $_SESSION['must_change_password'] = (int) $user['must_change_password'];

                $updateLogin = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
                $updateLogin->execute(['id' => (int) $user['user_id']]);
                fleetTrackAuthEvent([
                    'user_id' => (int) $user['user_id'],
                    'username' => (string) $user['username'],
                    'name' => (string) $user['name'],
                    'email' => (string) $user['email'],
                    'role' => (string) $user['role'],
                    'event_type' => 'login',
                    'event_description' => 'User signed in successfully',
                ], $pdo);

                if ((string) $user['role'] === 'admin') {
                    $_SESSION['admin_user_id'] = (int) $user['user_id'];
                    $_SESSION['admin_role'] = 'admin';

                    if ((int) $user['must_change_password'] === 1) {
                        header('Location: /fleet-system/change-password');
                        exit;
                    }

                    header('Location: /fleet-system/dashboard');
                    exit;
                }

                if ((string) $user['role'] === 'driver') {
                    $_SESSION['driver_id'] = (int) $user['driver_id'];

                    if ((int) $user['must_change_password'] === 1) {
                        header('Location: /fleet-system/driver-panel/change-password');
                        exit;
                    }

                    header('Location: /fleet-system/driver-panel/');
                    exit;
                }

                $errorMessage = 'This account role is not allowed to use this system.';
            }
        } catch (Throwable $exception) {
            error_log('Fleet login error: ' . $exception->getMessage());
            $errorMessage = 'Login is unavailable right now. Please try again.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
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
                    <img src="<?= htmlspecialchars($brandingLogoImage, ENT_QUOTES, 'UTF-8'); ?>" alt="BUESMIS logo">
                </div>
                <div class="login-panel-brand-copy">
                    <span class="login-panel-brand-label">Buesmis Fleet</span>
                    <span class="login-panel-brand-name">Fleet Management</span>
                </div>
            </div>

            <div class="login-panel-hero">
                <span class="login-panel-tag">Vehicle . Driver . Trip</span>
                <h1>Smart <span>fleet access</span><br>for your team</h1>
                <p>Secure, real-time control over every vehicle, driver, and trip all from one powerful dashboard.</p>
            </div>

            <div class="login-panel-stats">
                <div class="login-panel-stat">
                    <span class="login-panel-stat-num">2.4k+</span>
                    <span class="login-panel-stat-label">Active vehicles</span>
                </div>
                <div class="login-panel-stat">
                    <span class="login-panel-stat-num">98%</span>
                    <span class="login-panel-stat-label">Uptime</span>
                </div>
                <div class="login-panel-stat">
                    <span class="login-panel-stat-num">150+</span>
                    <span class="login-panel-stat-label">Fleets managed</span>
                </div>
            </div>

            <div class="login-panel-vehicle" aria-hidden="true">
                <img src="<?= htmlspecialchars($loginVehicleImage, ENT_QUOTES, 'UTF-8'); ?>" alt="Fleet vehicle">
            </div>
        </div>

        <div class="login-panel login-panel-form">
            <section class="login-card">
                <p class="login-card-tag">Login</p>
                <h2>Sign in</h2>
                <p class="login-card-sub">Enter your account details to continue.</p>

                <?php if ($errorMessage !== ''): ?>
                    <div class="login-card-alert">
                        <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <!-- jQuery adds inline validation and submit-state feedback to the login form. -->
                <form method="post">
                    <div data-fleet-feedback-host></div>
                    <div class="login-card-field">
                        <label for="login-username">Username</label>
                        <div class="login-input-shell">
                            <input id="login-username" name="username" type="text" autocomplete="username" required class="login-input" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Enter your username">
                        </div>
                    </div>

                    <div class="login-card-field">
                        <label for="login-password">Password</label>
                        <div class="login-input-shell">
                            <input id="login-password" name="password" type="password" autocomplete="current-password" required class="login-input" placeholder="Enter your password">
                        </div>
                        <div class="login-card-row">
                            <a href="#" class="login-card-link">Forgot password?</a>
                        </div>
                    </div>

                    <button type="submit" class="login-submit" data-loading-text="Signing In...">
                        <span>Log In</span>
                        <span class="login-submit-arrow" aria-hidden="true">&rarr;</span>
                    </button>
                </form>

                <div class="login-form-divider">
                    <span>Need access?</span>
                </div>

                <p class="login-card-footer">
                    Contact your administrator or <a href="#" class="login-card-link">request an account</a>.
                </p>
            </section>
        </div>
    </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
