<?php
$activePage = 'driver-profile';
require_once __DIR__ . '/../handlers/driver-panel.php';
extract(driverPanelFetchProfilePageData());
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="driver-panel-page min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1536px] px-4 py-5 sm:px-6 lg:px-8">
        <div class="dashboard-shell driver-page-shell">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Profile</h1>
                    <p class="mt-1 text-sm text-fleet-muted">Driver account and personal details</p>
                </div>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>

            <section class="driver-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                    <div class="flex items-start gap-4">
                        <?php if ($driverPhotoUrl !== '' && $driverPhotoIsImage): ?>
                            <img src="<?= htmlspecialchars($driverPhotoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($profileDriver['full_name'], ENT_QUOTES, 'UTF-8'); ?>" class="h-16 w-16 rounded-2xl object-cover ring-2 ring-fleet-primary-soft">
                        <?php else: ?>
                            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-fleet-primary-soft text-xl font-extrabold text-fleet-primary">
                                <?= htmlspecialchars(strtoupper(substr((string) $profileDriver['full_name'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        <?php endif; ?>
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-sidebar">Logged-in Driver</p>
                            <h2 class="mt-1 text-xl font-extrabold text-fleet-ink"><?= htmlspecialchars((string) $profileDriver['full_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            <p class="mt-1 text-sm text-fleet-muted"><?= htmlspecialchars((string) ($profileDriver['driver_code'] ?: 'Driver ID not assigned'), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                    <div class="rounded-lg border px-4 py-3 text-sm font-extrabold <?= htmlspecialchars($licenseExpiryStatus['classes'], ENT_QUOTES, 'UTF-8'); ?>">
                        Permit Expiry: <?= htmlspecialchars($licenseExpiryStatus['label'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>

                <div class="mt-6 grid gap-5 lg:grid-cols-2">
                    <?php foreach ($profileRows as $sectionTitle => $rows): ?>
                        <article class="rounded-lg border border-fleet-line-soft bg-white/70 p-5">
                            <h3 class="text-sm font-extrabold uppercase tracking-[0.14em] text-fleet-sidebar"><?= htmlspecialchars((string) $sectionTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                            <dl class="mt-4 space-y-3 text-sm">
                                <?php foreach ($rows as $label => $value): ?>
                                    <div class="flex items-start justify-between gap-4">
                                        <dt class="text-fleet-muted"><?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8'); ?></dt>
                                        <dd class="text-right font-semibold text-fleet-ink"><?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                        </article>
                    <?php endforeach; ?>
                </div>

                <section class="mt-5 rounded-lg border border-fleet-line-soft bg-white/70 p-5">
                    <h3 class="text-sm font-extrabold uppercase tracking-[0.14em] text-fleet-sidebar">Documents</h3>
                    <div class="mt-4 flex flex-wrap gap-3 text-sm">
                        <?php if ($driverPhotoUrl !== ''): ?>
                            <a href="<?= htmlspecialchars($driverPhotoUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-fleet-line bg-fleet-surface px-4 py-2 font-semibold text-fleet-primary hover:bg-fleet-surface-muted">Driver Photo</a>
                        <?php endif; ?>
                        <?php if ($nationalIdPhotoUrl !== ''): ?>
                            <a href="<?= htmlspecialchars($nationalIdPhotoUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-fleet-line bg-fleet-surface px-4 py-2 font-semibold text-fleet-primary hover:bg-fleet-surface-muted">National ID</a>
                        <?php endif; ?>
                        <?php if ($licenseScanUrl !== ''): ?>
                            <a href="<?= htmlspecialchars($licenseScanUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-fleet-line bg-fleet-surface px-4 py-2 font-semibold text-fleet-primary hover:bg-fleet-surface-muted">Driving License Scan</a>
                        <?php endif; ?>
                        <?php if ($driverPhotoUrl === '' && $nationalIdPhotoUrl === '' && $licenseScanUrl === ''): ?>
                            <span class="text-fleet-muted">No uploaded documents on file.</span>
                        <?php endif; ?>
                    </div>
                </section>
            </section>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
