<?php
$activePage = 'vehicle-requests';
require_once __DIR__ . '/../../handlers/vehicle-request.php';
extract(vehicleRequestFetchAdminPageData());
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Vehicle Request Notifications</h1>
                <p class="mt-2 text-sm text-fleet-muted">Review public transport requests submitted through the BUESMIS home page.</p>
            </div>
            <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                <span class="text-xl leading-none">&#9776;</span>
            </button>
        </div>

        <?php if (!empty($vehicleRequestAdminNotification)): ?>
            <?php $isSuccessNotice = ($vehicleRequestAdminNotification['type'] ?? '') === 'success'; ?>
            <section class="mb-6 rounded-2xl border px-5 py-4 shadow-sm <?= $isSuccessNotice ? 'border-green-200 bg-green-50 text-green-900' : 'border-red-200 bg-red-50 text-red-900'; ?>">
                <h2 class="text-sm font-extrabold uppercase tracking-[0.18em]">
                    <?= htmlspecialchars($vehicleRequestAdminNotification['title'] ?? 'Vehicle requests update', ENT_QUOTES, 'UTF-8'); ?>
                </h2>
                <p class="mt-2 text-sm leading-6"><?= htmlspecialchars($vehicleRequestAdminNotification['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
            </section>
        <?php endif; ?>

        <section class="mb-8 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <p class="text-sm font-semibold text-fleet-muted">Total Requests</p>
                <p class="mt-3 text-3xl font-extrabold text-fleet-ink"><?= (int) $vehicleRequestAdminSummary['total']; ?></p>
            </article>
            <article class="rounded-lg border border-orange-200 bg-orange-50 p-6 shadow-fleet-card">
                <p class="text-sm font-semibold text-orange-700">Pending Notifications</p>
                <p class="mt-3 text-3xl font-extrabold text-orange-800"><?= (int) $vehicleRequestAdminSummary['pending']; ?></p>
            </article>
            <article class="rounded-lg border border-green-200 bg-green-50 p-6 shadow-fleet-card">
                <p class="text-sm font-semibold text-green-700">Reviewed</p>
                <p class="mt-3 text-3xl font-extrabold text-green-800"><?= (int) $vehicleRequestAdminSummary['reviewed']; ?></p>
            </article>
            <article class="rounded-lg border border-blue-200 bg-blue-50 p-6 shadow-fleet-card">
                <p class="text-sm font-semibold text-blue-700">Submitted Today</p>
                <p class="mt-3 text-3xl font-extrabold text-blue-800"><?= (int) $vehicleRequestAdminSummary['today']; ?></p>
            </article>
        </section>

        <section class="overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="border-b border-fleet-line p-6">
                <form action="<?= htmlspecialchars($vehicleRequestAdminPageUrl, ENT_QUOTES, 'UTF-8'); ?>" method="get" class="flex flex-col gap-3 lg:flex-row">
                    <label class="relative block flex-1">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                            </svg>
                        </span>
                        <input type="search" name="q" value="<?= htmlspecialchars($vehicleRequestAdminSearch, ENT_QUOTES, 'UTF-8'); ?>" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-12 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search by requester, department, email, phone, purpose or reason">
                    </label>
                    <select name="status" class="h-11 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm text-fleet-ink shadow-sm outline-none transition focus:border-fleet-primary focus:ring-4 focus:ring-blue-100">
                        <option value="">All statuses</option>
                        <option value="pending" <?= $vehicleRequestAdminStatusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="reviewed" <?= $vehicleRequestAdminStatusFilter === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                    </select>
                    <button type="submit" class="h-11 rounded-lg bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Filter Requests</button>
                </form>
            </div>

            <?php if (!$vehicleRequestAdminHasRows): ?>
                <div class="flex min-h-[280px] items-center justify-center px-6 py-12">
                    <div class="text-center">
                        <h2 class="text-xl font-extrabold text-fleet-ink">No vehicle requests found</h2>
                        <p class="mt-2 text-sm text-fleet-muted">New public requests will appear here automatically.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1500px] text-left text-sm">
                        <thead class="bg-fleet-surface-muted text-fleet-muted">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Submitted</th>
                                <th class="px-5 py-4 font-semibold">Requester</th>
                                <th class="px-5 py-4 font-semibold">Contact</th>
                                <th class="px-5 py-4 font-semibold">Role / Department</th>
                                <th class="px-5 py-4 font-semibold">Trip Details</th>
                                <th class="px-5 py-4 font-semibold">Reason</th>
                                <th class="px-5 py-4 font-semibold">Purpose</th>
                                <th class="px-5 py-4 font-semibold">Status</th>
                                <th class="px-5 py-4 text-right font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-fleet-line-soft">
                            <?php foreach ($vehicleRequestAdminRows as $row): ?>
                                <?php $statusTone = $row['status'] === 'reviewed' ? 'border-green-200 bg-green-50 text-green-700' : 'border-orange-200 bg-orange-50 text-orange-700'; ?>
                                <tr class="align-top hover:bg-fleet-surface-muted/60">
                                    <td class="px-5 py-4 text-fleet-muted">
                                        <p><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p class="mt-1 text-xs">Travel date: <?= htmlspecialchars($row['request_date'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-extrabold text-fleet-ink"><?= htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </td>
                                    <td class="px-5 py-4 text-fleet-ink">
                                        <p><?= htmlspecialchars($row['email_address'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p class="mt-1 text-fleet-muted"><?= htmlspecialchars($row['phone_number'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </td>
                                    <td class="px-5 py-4 text-fleet-ink">
                                        <p class="font-semibold"><?= htmlspecialchars($row['job_title'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p class="mt-1 text-fleet-muted"><?= htmlspecialchars($row['department'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </td>
                                    <td class="px-5 py-4 text-fleet-ink">
                                        <p class="font-semibold"><?= htmlspecialchars($row['trip_destination'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p class="mt-1 text-fleet-muted">Vehicle: <?= htmlspecialchars($row['preferred_vehicle_type'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </td>
                                    <td class="px-5 py-4 text-fleet-ink"><?= nl2br(htmlspecialchars($row['reason'], ENT_QUOTES, 'UTF-8')); ?></td>
                                    <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($row['purpose_of_trip'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full border px-3 py-1 text-xs font-semibold <?= $statusTone; ?>"><?= htmlspecialchars($row['status_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if ($row['status'] === 'reviewed'): ?>
                                            <p class="mt-2 text-xs text-fleet-muted">By <?= htmlspecialchars($row['reviewed_by_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="mt-1 text-xs text-fleet-muted"><?= htmlspecialchars($row['reviewed_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end">
                                            <?php if ($row['status'] === 'pending'): ?>
                                                <form action="<?= htmlspecialchars($vehicleRequestFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post">
                                                    <input type="hidden" name="vehicle_request_action" value="mark_reviewed">
                                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars((string) $row['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button type="submit" class="inline-flex h-10 items-center rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Mark Reviewed</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-xs font-semibold text-fleet-muted">No action needed</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
