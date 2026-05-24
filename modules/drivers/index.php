<?php
// Static frontend page for university fleet driver records.
$activePage = 'drivers';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

$drivers = [
    ['name' => 'Simali Habert', 'email' => 'simalihabert@gmail.com', 'phone' => '+256 772 123 456', 'license' => 'CM 78452', 'assigned' => 'UAJ 433X', 'status' => 'Active'],
    ['name' => 'Moses Okello', 'email' => 'moses.okello@busitema.ac.ug', 'phone' => '+256 701 450 220', 'license' => 'CM 21984', 'assigned' => 'UBR 123C', 'status' => 'Active'],
    ['name' => 'Grace Namuli', 'email' => 'grace.namuli@busitema.ac.ug', 'phone' => '+256 758 802 114', 'license' => 'CM 66310', 'assigned' => '-', 'status' => 'Inactive'],
];

$hasDrivers = count($drivers) > 0;
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Driver Management</h1>
                <p class="mt-2 text-sm text-fleet-muted">University fleet driver records</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" data-print-page class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-fleet-card hover:bg-fleet-surface-muted">
                    <span class="text-base">P</span>
                    <span>Print</span>
                </button>
                <button type="button" class="inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>Add Driver</span>
                </button>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>
        </div>

        <div class="mb-6 max-w-md">
            <label class="relative block">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">Q</span>
                <input id="driver-search" type="search" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-11 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search drivers...">
            </label>
        </div>

        <section class="<?= $hasDrivers ? 'hidden' : 'flex'; ?> min-h-[420px] items-center justify-center">
            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-200 text-fleet-muted">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.9"></path>
                        <path d="M16 3.1a4 4 0 0 1 0 7.8"></path>
                    </svg>
                </div>
                <h2 class="mt-5 text-lg font-extrabold text-fleet-ink">No drivers found</h2>
                <p class="mt-2 text-sm text-fleet-muted">Add drivers to manage your fleet personnel.</p>
                <button type="button" class="mt-6 inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>Add Driver</span>
                </button>
            </div>
        </section>

        <section class="<?= $hasDrivers ? 'block' : 'hidden'; ?> overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[920px] text-left text-sm" data-driver-table>
                    <thead class="bg-fleet-surface-muted text-fleet-muted">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Driver</th>
                            <th class="px-5 py-4 font-semibold">Contact</th>
                            <th class="px-5 py-4 font-semibold">License No.</th>
                            <th class="px-5 py-4 font-semibold">Assigned Vehicle</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-fleet-line-soft">
                        <?php foreach ($drivers as $driver): ?>
                            <tr class="driver-row hover:bg-fleet-surface-muted/70" data-search="<?= htmlspecialchars(strtolower(implode(' ', $driver)), ENT_QUOTES, 'UTF-8'); ?>">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-fleet-primary-soft text-sm font-extrabold text-fleet-primary">
                                            <?= htmlspecialchars(strtoupper(substr($driver['name'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <div>
                                            <p class="font-extrabold text-fleet-ink" contenteditable="true"><?= htmlspecialchars($driver['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="text-xs text-fleet-muted" contenteditable="true"><?= htmlspecialchars($driver['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-fleet-ink" contenteditable="true"><?= htmlspecialchars($driver['phone'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 font-semibold text-fleet-ink" contenteditable="true"><?= htmlspecialchars($driver['license'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 text-fleet-ink" contenteditable="true"><?= htmlspecialchars($driver['assigned'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4">
                                    <?php if ($driver['status'] === 'Active'): ?>
                                        <span class="rounded-lg border border-green-200 bg-fleet-success-soft px-3 py-1 text-xs font-semibold text-fleet-success">Active</span>
                                    <?php else: ?>
                                        <span class="rounded-lg border border-fleet-line bg-slate-100 px-3 py-1 text-xs font-semibold text-fleet-muted">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-3">
                                        <button type="button" class="text-fleet-sidebar hover:text-fleet-primary" aria-label="Edit <?= htmlspecialchars($driver['name'], ENT_QUOTES, 'UTF-8'); ?>">Edit</button>
                                        <button type="button" class="text-fleet-danger hover:text-fleet-danger-strong" aria-label="Delete <?= htmlspecialchars($driver['name'], ENT_QUOTES, 'UTF-8'); ?>">Del</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
