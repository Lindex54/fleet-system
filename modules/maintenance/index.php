<?php
// Static frontend page for vehicle service and repair history.
$activePage = 'maintenance';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

$records = [
    ['date' => '18/05/2026', 'vehicle' => 'UAJ 433X', 'type' => 'Repair', 'description' => 'Engine over haul Brakes Windscreen ...', 'provider' => '-', 'cost' => 4200000, 'status' => 'Completed'],
];

$hasRecords = count($records) > 0;
$totalCost = array_sum(array_map(static fn ($record) => (int) $record['cost'], $records));
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Maintenance Records</h1>
                <p class="mt-2 text-sm text-fleet-muted">Vehicle service and repair history</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" data-print-page class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-fleet-card hover:bg-fleet-surface-muted">
                    <span class="text-base">P</span>
                    <span>Print</span>
                </button>
                <button type="button" class="inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>New Record</span>
                </button>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>
        </div>

        <div class="mb-6 grid gap-3 lg:grid-cols-[1fr_180px]">
            <label class="relative block">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">Q</span>
                <input id="maintenance-search" type="search" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-11 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search maintenance records...">
            </label>
            <select id="maintenance-status" class="vehicle-form-control h-11">
                <option value="all">All Status</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="in progress">In Progress</option>
            </select>
        </div>

        <section class="mb-6 flex items-center justify-between rounded-lg border border-fleet-line bg-fleet-surface px-5 py-4 text-sm shadow-sm">
            <span class="text-fleet-muted"><?= count($records); ?> record(s)</span>
            <span class="font-extrabold text-fleet-ink">Total Cost: UGX <?= number_format($totalCost); ?></span>
        </section>

        <section class="<?= $hasRecords ? 'hidden' : 'flex'; ?> min-h-[420px] items-center justify-center">
            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-200 text-fleet-muted">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.7 2.7-3-3z"></path>
                    </svg>
                </div>
                <h2 class="mt-5 text-lg font-extrabold text-fleet-ink">No maintenance records found</h2>
                <p class="mt-2 text-sm text-fleet-muted">Create records to track repairs, service history, and costs.</p>
                <button type="button" class="mt-6 inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>New Record</span>
                </button>
            </div>
        </section>

        <section class="<?= $hasRecords ? 'block' : 'hidden'; ?> overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1040px] text-left text-sm" data-maintenance-table>
                    <thead class="bg-fleet-surface-muted text-fleet-muted">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Date</th>
                            <th class="px-5 py-4 font-semibold">Vehicle</th>
                            <th class="px-5 py-4 font-semibold">Type</th>
                            <th class="px-5 py-4 font-semibold">Description</th>
                            <th class="px-5 py-4 font-semibold">Provider</th>
                            <th class="px-5 py-4 font-semibold">Cost (UGX)</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-fleet-line-soft">
                        <?php foreach ($records as $record): ?>
                            <tr class="maintenance-row hover:bg-fleet-surface-muted/70" data-status="<?= htmlspecialchars(strtolower($record['status']), ENT_QUOTES, 'UTF-8'); ?>" data-search="<?= htmlspecialchars(strtolower(implode(' ', $record)), ENT_QUOTES, 'UTF-8'); ?>">
                                <td class="px-5 py-4 text-fleet-ink" contenteditable="true"><?= htmlspecialchars($record['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 font-extrabold text-fleet-ink" contenteditable="true"><?= htmlspecialchars($record['vehicle'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 text-fleet-muted" contenteditable="true"><?= htmlspecialchars($record['type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 text-fleet-muted" contenteditable="true"><?= htmlspecialchars($record['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 text-fleet-muted" contenteditable="true"><?= htmlspecialchars($record['provider'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 font-extrabold text-fleet-ink" contenteditable="true"><?= number_format($record['cost']); ?></td>
                                <td class="px-5 py-4">
                                    <span class="rounded-lg border border-green-200 bg-fleet-success-soft px-3 py-1 text-xs font-semibold text-fleet-success"><?= htmlspecialchars($record['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-3">
                                        <button type="button" class="text-fleet-sidebar hover:text-fleet-primary" aria-label="Edit maintenance record">Edit</button>
                                        <button type="button" class="text-fleet-danger hover:text-fleet-danger-strong" aria-label="Delete maintenance record">Del</button>
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
