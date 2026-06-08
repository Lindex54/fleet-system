<?php
$activePage = 'driver-profile';
require_once __DIR__ . '/../handlers/driver-panel.php';
driverPanelRequireAuthenticatedDriver(true);

if ((int) ($_SESSION['must_change_password'] ?? 0) !== 1) {
    header('Location: ' . driverPanelDashboardUrl());
    exit;
}

$passwordFlash = driverPanelPullPasswordFlash();
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="driver-panel-page min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1536px] px-4 py-5 sm:px-6 lg:px-8">
        <div class="dashboard-shell driver-page-shell">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Set Password</h1>
                    <p class="mt-1 text-sm text-fleet-muted">Create a private password before using the driver panel</p>
                </div>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>

            <section class="driver-card mx-auto max-w-xl rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <h2 class="text-lg font-extrabold text-fleet-ink">Password Setup Required</h2>
                <p class="mt-2 text-sm text-fleet-muted">Your one-time password worked. Set a new password to continue.</p>

                <?php if (!empty($passwordFlash)): ?>
                    <?php $isSuccess = ($passwordFlash['type'] ?? '') === 'success'; ?>
                    <div class="mt-4 rounded-lg border px-4 py-3 text-sm font-semibold <?= $isSuccess ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-fleet-danger'; ?>">
                        <?= htmlspecialchars((string) ($passwordFlash['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form action="<?= htmlspecialchars(driverPanelHandlerUrl(), ENT_QUOTES, 'UTF-8'); ?>" method="post" class="mt-5 space-y-4">
                    <input type="hidden" name="driver_panel_action" value="change_password">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">New Password</span>
                        <input name="new_password" type="password" autocomplete="new-password" minlength="8" required class="vehicle-form-control">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Confirm Password</span>
                        <input name="confirm_password" type="password" autocomplete="new-password" minlength="8" required class="vehicle-form-control">
                    </label>
                    <button type="submit" class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Save Password</button>
                </form>
            </section>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
