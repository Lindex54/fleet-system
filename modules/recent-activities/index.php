<?php
$activePage = 'recent-activities';
require_once __DIR__ . '/../../handlers/recent-activities.php';
extract(recentActivitiesFetchPageData());
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Recent Activities</h1>
                <p class="mt-2 text-sm text-fleet-muted">Track the latest updates, record changes, and operational actions happening across fleet modules.</p>
            </div>
            <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                <span class="text-xl leading-none">&#9776;</span>
            </button>
        </div>

        <section class="mb-8 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <p class="summary-card-label text-slate-800">Total Activities</p>
                <p class="summary-card-value mt-3 text-slate-900"><?= (int) $recentActivitiesSummary['total']; ?></p>
            </article>
            <article class="rounded-lg border border-blue-200 bg-blue-50 p-6 shadow-fleet-card">
                <p class="summary-card-label text-blue-900">Today</p>
                <p class="summary-card-value mt-3 text-slate-900"><?= (int) $recentActivitiesSummary['today']; ?></p>
            </article>
            <article class="rounded-lg border border-amber-200 bg-amber-50 p-6 shadow-fleet-card">
                <p class="summary-card-label text-amber-900">Modules Active</p>
                <p class="summary-card-value mt-3 text-slate-900"><?= (int) $recentActivitiesSummary['modules']; ?></p>
            </article>
            <article class="rounded-lg border border-green-200 bg-green-50 p-6 shadow-fleet-card">
                <p class="summary-card-label text-green-900">Users Acting</p>
                <p class="summary-card-value mt-3 text-slate-900"><?= (int) $recentActivitiesSummary['actors']; ?></p>
            </article>
        </section>

        <section class="overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="border-b border-fleet-line p-6">
                <form action="<?= htmlspecialchars($recentActivitiesPageUrl, ENT_QUOTES, 'UTF-8'); ?>" method="get" class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_240px_160px]">
                    <label class="relative block">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                            </svg>
                        </span>
                        <input type="search" name="q" value="<?= htmlspecialchars($recentActivitiesSearch, ENT_QUOTES, 'UTF-8'); ?>" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-12 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search by actor, module, action or target">
                    </label>
                    <select name="module" class="h-11 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm text-fleet-ink shadow-sm outline-none transition focus:border-fleet-primary focus:ring-4 focus:ring-blue-100">
                        <option value="">All modules</option>
                        <?php foreach ($recentActivitiesModuleOptions as $moduleOption): ?>
                            <option value="<?= htmlspecialchars($moduleOption, ENT_QUOTES, 'UTF-8'); ?>" <?= $recentActivitiesModuleFilter === $moduleOption ? 'selected' : ''; ?>>
                                <?= htmlspecialchars(ucwords(str_replace('-', ' ', $moduleOption)), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="h-11 rounded-lg bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Filter Activity</button>
                </form>
            </div>

            <?php if (!$recentActivitiesHasRows): ?>
                <div class="flex min-h-[280px] items-center justify-center px-6 py-12">
                    <div class="text-center">
                        <h2 class="text-xl font-extrabold text-fleet-ink">No recent activity found</h2>
                        <p class="mt-2 text-sm text-fleet-muted">Tracked updates and operational actions will appear here once activity is recorded.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1080px] text-left text-sm">
                        <thead class="bg-fleet-surface-muted text-fleet-muted">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Date &amp; Time</th>
                                <th class="px-5 py-4 font-semibold">Actor</th>
                                <th class="px-5 py-4 font-semibold">Module</th>
                                <th class="px-5 py-4 font-semibold">Action</th>
                                <th class="px-5 py-4 font-semibold">Description</th>
                                <th class="px-5 py-4 font-semibold">Target</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-fleet-line-soft">
                            <?php foreach ($recentActivityRows as $row): ?>
                                <tr class="hover:bg-fleet-surface-muted/60">
                                    <td class="px-5 py-4 text-fleet-muted"><?= htmlspecialchars($row['occurred_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-5 py-4">
                                        <p class="font-extrabold text-fleet-ink"><?= htmlspecialchars($row['actor_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p class="text-xs text-fleet-muted"><?= htmlspecialchars(ucfirst($row['actor_role']), ENT_QUOTES, 'UTF-8'); ?></p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full border border-fleet-line bg-white px-3 py-1 text-xs font-semibold text-fleet-sidebar"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $row['module_key'])), ENT_QUOTES, 'UTF-8'); ?></span>
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-fleet-ink"><?= htmlspecialchars($row['action_label'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-5 py-4 text-fleet-muted">
                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $row['target_type'])), ENT_QUOTES, 'UTF-8'); ?>:
                                        <?= htmlspecialchars($row['target_label'], ENT_QUOTES, 'UTF-8'); ?>
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
