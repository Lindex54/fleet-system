<?php
// Static frontend page for pre-trip vehicle condition reports.
$activePage = 'pre-inspection';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

$reports = [
    ['invoice' => '234', 'date' => '16/05/2026', 'vehicle' => 'UBP 401F', 'make_model' => 'TOYOTA LAND CRUISER', 'inspector' => 'Simali', 'overall' => '-', 'defects' => 'None'],
    ['invoice' => 'U2344', 'date' => '16/05/2026', 'vehicle' => 'UBP 401F', 'make_model' => 'TOYOTA LAND CRUISER', 'inspector' => 'Sh', 'overall' => 'Good', 'defects' => 'Cooling gfsf'],
    ['invoice' => 'U2344', 'date' => '16/05/2026', 'vehicle' => 'UBP 401F', 'make_model' => 'TOYOTA LAND CRUISER', 'inspector' => 'Sh', 'overall' => 'Good', 'defects' => 'Cooling gfsf'],
];

$hasReports = count($reports) > 0;
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1180px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Pre-Inspection Reports</h1>
                <p class="mt-2 text-sm text-fleet-muted">Vehicle condition checks before each trip</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" data-print-page class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-fleet-card hover:bg-fleet-surface-muted">
                    <span class="text-base">P</span>
                    <span>Print Register</span>
                </button>
                <button type="button" data-open-pre-inspection-modal class="inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>New Pre-Inspection Report</span>
                </button>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>
        </div>

        <div class="mb-6 max-w-md">
            <label class="relative block">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">Q</span>
                <input id="pre-inspection-search" type="search" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-11 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search by vehicle, inspector, invoice...">
            </label>
        </div>

        <section class="<?= $hasReports ? 'hidden' : 'flex'; ?> min-h-[420px] items-center justify-center">
            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-200 text-fleet-muted">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="16" height="18" x="4" y="4" rx="2"></rect>
                        <path d="M9 2h6a1 1 0 0 1 1 1v2H8V3a1 1 0 0 1 1-1z"></path>
                        <path d="M8 11h.01"></path>
                        <path d="M12 11h4"></path>
                        <path d="M8 16h.01"></path>
                        <path d="M12 16h4"></path>
                    </svg>
                </div>
                <h2 class="mt-5 text-lg font-extrabold text-fleet-ink">No pre-inspection reports found</h2>
                <p class="mt-2 text-sm text-fleet-muted">Create reports to record vehicle checks before trips.</p>
                <button type="button" data-open-pre-inspection-modal class="mt-6 inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>New Pre-Inspection Report</span>
                </button>
            </div>
        </section>

        <section class="<?= $hasReports ? 'block' : 'hidden'; ?> overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1050px] border-collapse text-left text-sm" data-pre-inspection-table>
                    <thead class="bg-fleet-surface-muted text-fleet-ink">
                        <tr>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">#</th>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">Invoice No.</th>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">Date</th>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">Vehicle Reg.</th>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">Make / Model</th>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">Inspector</th>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">Overall</th>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">Defects</th>
                            <th class="border border-fleet-line px-4 py-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $index => $report): ?>
                            <tr class="pre-inspection-row transition hover:bg-fleet-surface-muted/70" data-search="<?= htmlspecialchars(strtolower(implode(' ', $report)), ENT_QUOTES, 'UTF-8'); ?>">
                                <td class="border border-fleet-line px-4 py-4 text-fleet-muted"><?= $index + 1; ?></td>
                                <td class="border border-fleet-line px-4 py-4 font-semibold text-fleet-sidebar" contenteditable="true"><?= htmlspecialchars($report['invoice'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4 text-fleet-ink" contenteditable="true"><?= htmlspecialchars($report['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4 font-extrabold text-fleet-ink" contenteditable="true"><?= htmlspecialchars($report['vehicle'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4 text-fleet-ink" contenteditable="true"><?= htmlspecialchars($report['make_model'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4 text-fleet-ink" contenteditable="true"><?= htmlspecialchars($report['inspector'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4">
                                    <?php if ($report['overall'] === 'Good'): ?>
                                        <span class="rounded-full bg-fleet-badge-red px-3 py-1 text-xs font-bold text-fleet-danger">Good</span>
                                    <?php else: ?>
                                        <span class="rounded-full bg-fleet-badge-red px-3 py-1 text-xs font-bold text-fleet-danger">--</span>
                                    <?php endif; ?>
                                </td>
                                <td class="border border-fleet-line px-4 py-4 text-fleet-muted" contenteditable="true"><?= htmlspecialchars($report['defects'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4">
                                    <div class="flex justify-end gap-4">
                                        <button type="button" class="text-fleet-primary hover:text-fleet-primary-strong" aria-label="Print inspection <?= $index + 1; ?>">Print</button>
                                        <button type="button" class="text-fleet-sidebar hover:text-fleet-primary" aria-label="Edit inspection <?= $index + 1; ?>">Edit</button>
                                        <button type="button" class="text-fleet-danger hover:text-fleet-danger-strong" aria-label="Delete inspection <?= $index + 1; ?>">Del</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div id="pre-inspection-modal" class="fixed inset-0 z-50 hidden items-start justify-center overflow-y-auto bg-black/75 px-4 py-5 sm:items-center" aria-hidden="true">
        <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full max-w-[900px] overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="pre-inspection-modal-title">
            <form class="p-6" action="#" method="post">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 id="pre-inspection-modal-title" class="text-xl font-extrabold text-fleet-ink">New Pre-Inspection Report</h2>
                        <p class="mt-1 text-xs text-fleet-muted">Busitema University - Transport Unit</p>
                    </div>
                    <button type="button" data-close-pre-inspection-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close pre-inspection form">&times;</button>
                </div>

                <div class="form-section-title"><span>1</span>Inspection Details</div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Invoice Number *</span>
                        <input name="invoice_number" type="text" required autofocus class="vehicle-form-control" placeholder="e.g. INV-2024-001">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Date of Inspection *</span>
                        <input name="inspection_date" type="date" required class="vehicle-form-control" value="2026-05-24">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Inspector Name *</span>
                        <input name="inspector_name" type="text" required class="vehicle-form-control" placeholder="Full name">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Inspector Title</span>
                        <input name="inspector_title" type="text" class="vehicle-form-control" placeholder="e.g. Transport Officer">
                    </label>
                </div>

                <div class="form-section-title mt-6"><span>2</span>Vehicle Details</div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Vehicle *</span>
                        <select name="vehicle" required class="vehicle-form-control">
                            <option value="">Select vehicle</option>
                            <option value="UBP 401F">UBP 401F - TOYOTA LAND CRUISER</option>
                            <option value="UAJ 433X">UAJ 433X - Ford ranger</option>
                            <option value="UBR 123C">UBR 123C - TOYOTA Land cruiser</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Mileage at Inspection (km)</span>
                        <input name="mileage" type="number" min="0" class="vehicle-form-control" placeholder="e.g. 45230">
                    </label>
                </div>

                <div class="form-section-title mt-6"><span>3</span>Memo Header</div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">To</span>
                        <input name="memo_to" type="text" class="vehicle-form-control" value="University Secretary">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Thru (1st)</span>
                        <input name="memo_thru_one" type="text" class="vehicle-form-control" value="University Bursar">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Thru (2nd)</span>
                        <input name="memo_thru_two" type="text" class="vehicle-form-control" value="Programme Controller">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">From</span>
                        <input name="memo_from" type="text" class="vehicle-form-control" placeholder="e.g. Ag. AEO (Mech.) Simali Habert">
                    </label>
                    <label class="block md:col-span-2">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Vehicle Description (for subject)</span>
                        <input name="vehicle_description" type="text" class="vehicle-form-control" placeholder="e.g. Mitsubishi double cabin pickup 1200 model 2007">
                    </label>
                </div>

                <div class="form-section-title mt-6"><span>4</span>Inspection Items</div>
                <div class="rounded-lg border border-fleet-line bg-fleet-surface-muted p-4">
                    <p class="mb-4 text-sm font-semibold text-fleet-muted">Item 1</p>
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block md:col-span-2">
                            <span class="mb-2 block text-sm font-semibold text-fleet-ink">Inspection Point</span>
                            <input name="inspection_point[]" type="text" class="vehicle-form-control" placeholder="e.g. Engine">
                        </label>
                        <label class="block md:col-span-2">
                            <span class="mb-2 block text-sm font-semibold text-fleet-ink">Status / Findings</span>
                            <textarea name="inspection_findings[]" class="vehicle-form-control min-h-20 resize-y py-3" placeholder="Describe the condition found..."></textarea>
                        </label>
                        <label class="block md:col-span-2">
                            <span class="mb-2 block text-sm font-semibold text-fleet-ink">Remarks / Action Point</span>
                            <textarea name="inspection_action[]" class="vehicle-form-control min-h-20 resize-y py-3" placeholder="Recommended action..."></textarea>
                        </label>
                    </div>
                </div>
                <button type="button" class="mt-3 h-10 w-full rounded-lg border border-fleet-line bg-fleet-surface text-sm font-semibold text-fleet-sidebar shadow-sm hover:bg-fleet-surface-muted">+ Add Inspection Item</button>

                <div class="form-section-title mt-6"><span>5</span>Closing &amp; CC</div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block md:col-span-2">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Closing Note</span>
                        <textarea name="closing_note" class="vehicle-form-control min-h-20 resize-y py-3">The purpose of this report is to therefore request you authorize repair and maintenance works on this vehicle for full restoration.</textarea>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">CC</span>
                        <input name="cc" type="text" class="vehicle-form-control" value="Senior Estates Officer">
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" data-close-pre-inspection-modal class="h-10 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">Cancel</button>
                    <button type="submit" class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Save Report</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
