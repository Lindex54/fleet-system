<?php
$activePage = 'admins';
require_once __DIR__ . '/../../handlers/admin.php';
extract(adminFetchPageData());
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Admin Management</h1>
                <p class="mt-2 text-sm text-fleet-muted">Create and review administrator accounts</p>
            </div>
            <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                <span class="text-xl leading-none">&#9776;</span>
            </button>
        </div>

        <?php if (!empty($adminNotification)): ?>
            <?php $isSuccessNotice = ($adminNotification['type'] ?? '') === 'success'; ?>
            <section
                data-flash-notice
                data-flash-type="<?= $isSuccessNotice ? 'success' : 'error'; ?>"
                class="pointer-events-none fixed left-1/2 top-8 z-[70] hidden w-[min(92vw,38rem)] -translate-x-1/2 overflow-hidden rounded-2xl border bg-white shadow-2xl transition duration-500 <?= $isSuccessNotice ? 'border-green-200 text-green-900' : 'border-red-200 text-red-900'; ?>"
            >
                <div class="absolute inset-x-0 top-0 h-1.5 <?= $isSuccessNotice ? 'bg-green-500' : 'bg-red-500'; ?>"></div>
                <div class="flex items-center gap-4 px-5 py-4 sm:px-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-sm font-extrabold shadow-lg <?= $isSuccessNotice ? 'bg-green-600 text-white shadow-green-200' : 'bg-red-600 text-white shadow-red-200'; ?>">
                        <?= $isSuccessNotice ? 'OK' : '!'; ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-sm font-extrabold uppercase tracking-[0.18em]">
                            <?= htmlspecialchars((string) ($adminNotification['title'] ?? 'Admin update'), ENT_QUOTES, 'UTF-8'); ?>
                        </h2>
                        <p class="mt-1 text-sm leading-6 text-fleet-ink">
                            <?= htmlspecialchars((string) ($adminNotification['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <div class="mt-3 h-1.5 overflow-hidden rounded-full <?= $isSuccessNotice ? 'bg-green-100' : 'bg-red-100'; ?>">
                            <div data-flash-progress class="h-full w-full origin-left rounded-full <?= $isSuccessNotice ? 'bg-green-600' : 'bg-red-600'; ?>"></div>
                        </div>
                    </div>
                    <button type="button" data-dismiss-flash class="pointer-events-auto inline-flex h-9 w-9 items-center justify-center rounded-full border text-base font-bold transition <?= $isSuccessNotice ? 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' : 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'; ?>" aria-label="Dismiss notification">x</button>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($adminCredentials)): ?>
            <section class="mb-6 rounded-lg border border-green-200 bg-green-50 p-5 shadow-fleet-card">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-green-700">Admin Login Created</p>
                        <h2 class="mt-1 text-lg font-extrabold text-fleet-ink">Give these credentials to the new admin now</h2>
                        <p class="mt-1 text-sm text-fleet-muted">The password is shown only this once. The admin must set a new password on first login.</p>
                    </div>
                    <div class="grid gap-3 text-sm sm:grid-cols-2 md:min-w-[420px]">
                        <div class="rounded-lg border border-green-200 bg-white px-4 py-3">
                            <p class="text-xs font-bold uppercase tracking-wide text-fleet-muted">Username</p>
                            <p class="mt-1 font-extrabold text-fleet-ink"><?= htmlspecialchars((string) ($adminCredentials['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="rounded-lg border border-green-200 bg-white px-4 py-3">
                            <p class="text-xs font-bold uppercase tracking-wide text-fleet-muted">One-Time Password</p>
                            <p class="mt-1 font-extrabold text-fleet-ink"><?= htmlspecialchars((string) ($adminCredentials['one_time_password'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="grid gap-6 xl:grid-cols-[minmax(320px,0.75fr)_minmax(0,1.25fr)]">
            <!-- jQuery adds inline validation and double-submit protection to admin creation. -->
            <form action="<?= htmlspecialchars($adminFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" class="rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <input type="hidden" name="admin_action" value="create">
                <h2 class="text-lg font-extrabold text-fleet-ink">Create Admin</h2>
                <p class="mt-1 text-sm text-fleet-muted">Username and one-time password are generated automatically.</p>
                <div class="mt-4" data-fleet-feedback-host></div>

                <div class="mt-5 space-y-4">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Full Name *</span>
                        <input name="name" type="text" required class="vehicle-form-control" value="<?= htmlspecialchars((string) ($adminFormData['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Email *</span>
                        <input name="email" type="email" required class="vehicle-form-control" value="<?= htmlspecialchars((string) ($adminFormData['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Status</span>
                        <select name="status" class="vehicle-form-control">
                            <option value="active" <?= (($adminFormData['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?= (($adminFormData['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="suspended" <?= (($adminFormData['status'] ?? '') === 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </label>
                </div>

                <button type="submit" class="mt-5 h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active" data-loading-text="Creating Admin...">Add Admin</button>
            </form>

            <section class="overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
                <div class="border-b border-fleet-line-soft px-5 py-4">
                    <h2 class="text-lg font-extrabold text-fleet-ink">Admin Accounts</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-fleet-surface-muted text-fleet-muted">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Admin</th>
                                <th class="px-5 py-4 font-semibold">Username</th>
                                <th class="px-5 py-4 font-semibold">Status</th>
                                <th class="px-5 py-4 font-semibold">Password Change</th>
                                <th class="px-5 py-4 font-semibold">Last Login</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-fleet-line-soft">
                            <?php foreach ($admins as $admin): ?>
                                <tr class="hover:bg-fleet-surface-muted/70">
                                    <td class="px-5 py-4">
                                        <p class="font-extrabold text-fleet-ink"><?= htmlspecialchars($admin['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p class="text-xs text-fleet-muted"><?= htmlspecialchars($admin['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-fleet-ink"><?= htmlspecialchars($admin['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-5 py-4">
                                        <?php if ($admin['status'] === 'Active'): ?>
                                            <span class="rounded-lg border border-green-200 bg-fleet-success-soft px-3 py-1 text-xs font-semibold text-fleet-success">Active</span>
                                        <?php else: ?>
                                            <span class="rounded-lg border border-fleet-line bg-slate-100 px-3 py-1 text-xs font-semibold text-fleet-muted"><?= htmlspecialchars($admin['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($admin['must_change_password'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($admin['last_login_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
