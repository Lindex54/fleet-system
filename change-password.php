<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

fleetAuthStartSession();

if ((string) ($_SESSION['user_role'] ?? '') !== 'admin' || empty($_SESSION['admin_user_id'])) {
    header('Location: ' . fleetAuthLoginUrl());
    exit;
}

if ((int) ($_SESSION['must_change_password'] ?? 0) !== 1) {
    header('Location: /fleet-system/dashboard');
    exit;
}

$message = '';
$messageType = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if (strlen($newPassword) < 8) {
        $message = 'Your new password must be at least 8 characters long.';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'The new password and confirmation do not match.';
    } else {
        try {
            $statement = fleetDb()->prepare(
                'UPDATE users
                SET password_hash = :password_hash,
                    must_change_password = 0
                WHERE id = :id
                    AND role = \'admin\''
            );
            $statement->execute([
                'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                'id' => (int) $_SESSION['admin_user_id'],
            ]);

            if ($statement->rowCount() === 0) {
                throw new RuntimeException('Password was not updated.');
            }

            $_SESSION['must_change_password'] = 0;
            header('Location: /fleet-system/dashboard');
            exit;
        } catch (Throwable $exception) {
            $message = 'Your password could not be updated right now.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="min-h-screen bg-fleet-canvas px-4 py-10">
    <section class="mx-auto mt-10 w-full max-w-md rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
        <div class="mb-6">
            <p class="text-sm font-semibold text-fleet-primary">BUESMIS Admin</p>
            <h1 class="mt-1 text-2xl font-extrabold text-fleet-ink">Set Password</h1>
            <p class="mt-2 text-sm text-fleet-muted">Create your private admin password before using the dashboard.</p>
        </div>

        <?php if ($message !== ''): ?>
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-fleet-danger">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <label class="block">
                <span class="mb-2 block text-sm font-semibold text-fleet-ink">New Password</span>
                <input name="new_password" type="password" autocomplete="new-password" minlength="8" required class="vehicle-form-control">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-semibold text-fleet-ink">Confirm Password</span>
                <input name="confirm_password" type="password" autocomplete="new-password" minlength="8" required class="vehicle-form-control">
            </label>
            <button type="submit" class="h-11 w-full rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Save Password</button>
        </form>
    </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
