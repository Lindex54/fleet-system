<?php
// Post-inspection reports page backed by the inspection handler and database.
$activePage = 'post-inspection';
require_once __DIR__ . '/../../handlers/inspection.php';
// Load live post-inspection rows, dropdown options, and any flash UI state from the handler.
extract(postInspectionFetchPageData());
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1180px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Post-Inspection Reports</h1>
                <p class="mt-2 text-sm text-fleet-muted">Works done, invoice details and payment authorisation requests</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" data-print-page class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-fleet-card hover:bg-fleet-surface-muted">
                    <span class="text-base">P</span>
                    <span>Print Register</span>
                </button>
                <button type="button" data-open-post-inspection-modal class="inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>New Post-Inspection Report</span>
                </button>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>
        </div>

        <?php if (!empty($postInspectionNotification)): ?>
            <?php $isSuccessNotice = ($postInspectionNotification['type'] ?? '') === 'success'; ?>
            <!-- Prominent popup toast so success/error feedback is immediately noticeable. -->
            <section
                data-flash-notice
                data-flash-type="<?= $isSuccessNotice ? 'success' : 'error'; ?>"
                class="pointer-events-none fixed left-1/2 top-8 z-[70] hidden w-[min(92vw,34rem)] -translate-x-1/2 overflow-hidden rounded-2xl border bg-white shadow-2xl transition duration-500 <?= $isSuccessNotice ? 'border-green-200 text-green-900' : 'border-red-200 text-red-900'; ?>"
            >
                <div class="absolute inset-x-0 top-0 h-1.5 <?= $isSuccessNotice ? 'bg-green-500' : 'bg-red-500'; ?>"></div>
                <div class="flex items-center gap-4 px-5 py-4 sm:px-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-sm font-extrabold shadow-lg <?= $isSuccessNotice ? 'bg-green-600 text-white shadow-green-200' : 'bg-red-600 text-white shadow-red-200'; ?>">
                        <?= $isSuccessNotice ? 'OK' : '!'; ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-sm font-extrabold uppercase tracking-[0.18em]">
                                    <?= htmlspecialchars($postInspectionNotification['title'] ?? 'Post-inspection update', ENT_QUOTES, 'UTF-8'); ?>
                                </h2>
                                <p class="mt-1 text-sm leading-6 text-fleet-ink">
                                    <?= htmlspecialchars($postInspectionNotification['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                            <button
                                type="button"
                                data-dismiss-flash
                                class="pointer-events-auto inline-flex h-9 w-9 items-center justify-center rounded-full border text-base font-bold transition <?= $isSuccessNotice ? 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' : 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'; ?>"
                                aria-label="Dismiss notification"
                            >
                                x
                            </button>
                        </div>
                        <div class="mt-3 h-1.5 overflow-hidden rounded-full <?= $isSuccessNotice ? 'bg-green-100' : 'bg-red-100'; ?>">
                            <div
                                data-flash-progress
                                class="h-full w-full origin-left rounded-full <?= $isSuccessNotice ? 'bg-green-600' : 'bg-red-600'; ?>"
                            ></div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="<?= $hasReports ? 'hidden' : 'flex'; ?> min-h-[420px] items-center justify-center">
            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-200 text-fleet-muted">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="16" height="18" x="4" y="4" rx="2"></rect>
                        <path d="M9 2h6a1 1 0 0 1 1 1v2H8V3a1 1 0 0 1 1-1z"></path>
                        <path d="m9 14 2 2 4-5"></path>
                    </svg>
                </div>
                <h2 class="mt-5 text-lg font-extrabold text-fleet-ink">No post-inspection reports found</h2>
                <p class="mt-2 text-sm text-fleet-muted">Create reports after repair works and invoice review.</p>
                <button type="button" data-open-post-inspection-modal class="mt-6 inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>New Post-Inspection Report</span>
                </button>
            </div>
        </section>

        <section class="<?= $hasReports ? 'block' : 'hidden'; ?> overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="border-b border-fleet-line-soft px-4 py-4 sm:px-5">
                <label class="relative block max-w-md">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">Q</span>
                    <input id="post-inspection-search" type="search" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-11 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search by vehicle, inspector, invoice...">
                </label>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1050px] border-collapse text-left text-sm" data-post-inspection-table>
                    <thead class="bg-fleet-surface-muted text-fleet-ink">
                        <tr>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">#</th>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">Invoice No.</th>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">Date</th>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">Vehicle Reg.</th>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">Make / Model</th>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">Inspector</th>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">Overall</th>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">Post Inv. No.</th>
                            <th class="border border-fleet-line px-4 py-4 font-semibold">Repair Cost (UGX)</th>
                            <th class="border border-fleet-line px-4 py-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $index => $report): ?>
                            <tr
                                class="post-inspection-row transition hover:bg-fleet-surface-muted/70"
                                data-search="<?= htmlspecialchars(strtolower(implode(' ', array_map(static fn ($value): string => (string) $value, [$report['invoice'], $report['date'], $report['vehicle'], $report['make_model'], $report['inspector'], $report['overall'], $report['post_invoice'], $report['repair_cost']]))), ENT_QUOTES, 'UTF-8'); ?>"
                                data-report-id="<?= htmlspecialchars((string) $report['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-vehicle-id="<?= htmlspecialchars((string) $report['vehicle_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-service-provider-id="<?= htmlspecialchars((string) ($report['service_provider_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                data-invoice-number="<?= htmlspecialchars($report['invoice_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-post-invoice-number="<?= htmlspecialchars($report['post_invoice_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-inspection-date="<?= htmlspecialchars($report['date_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-inspector-name="<?= htmlspecialchars($report['inspector_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-inspector-title="<?= htmlspecialchars($report['inspector_title_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-mileage="<?= htmlspecialchars($report['mileage_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-overall-status="<?= htmlspecialchars($report['overall_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-works-done="<?= htmlspecialchars($report['works_done_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-repair-cost="<?= htmlspecialchars($report['repair_cost_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-recommendation="<?= htmlspecialchars($report['recommendation_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-system-checks="<?= htmlspecialchars($report['system_checks_json'], ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <td class="border border-fleet-line px-4 py-4 text-fleet-muted"><?= $index + 1; ?></td>
                                <td class="border border-fleet-line px-4 py-4 font-semibold text-fleet-sidebar"><?= htmlspecialchars($report['invoice'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($report['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4 font-extrabold text-fleet-ink"><?= htmlspecialchars($report['vehicle'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($report['make_model'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($report['inspector'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4">
                                    <?php if ($report['overall'] === 'Good'): ?>
                                        <span class="rounded-full bg-fleet-success-soft px-3 py-1 text-xs font-bold text-fleet-success">Good</span>
                                    <?php elseif ($report['overall'] === 'Fair'): ?>
                                        <span class="rounded-full bg-fleet-warning-soft px-3 py-1 text-xs font-bold text-fleet-warning-strong">Fair</span>
                                    <?php elseif ($report['overall'] === 'Faulty'): ?>
                                        <span class="rounded-full bg-fleet-badge-red px-3 py-1 text-xs font-bold text-fleet-danger">Faulty</span>
                                    <?php elseif ($report['overall'] === 'Completed'): ?>
                                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-fleet-primary">Completed</span>
                                    <?php else: ?>
                                        <span class="rounded-full bg-fleet-badge-red px-3 py-1 text-xs font-bold text-fleet-danger">--</span>
                                    <?php endif; ?>
                                </td>
                                <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($report['post_invoice'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4 font-extrabold text-fleet-ink"><?= $report['repair_cost'] > 0 ? number_format((float) $report['repair_cost']) : '&mdash;'; ?></td>
                                <td class="border border-fleet-line px-4 py-4">
                                    <div class="flex justify-end gap-4">
                                        <button type="button" class="text-fleet-primary hover:text-fleet-primary-strong" aria-label="Print post inspection <?= $index + 1; ?>">Print</button>
                                        <button type="button" data-edit-post-inspection-entry class="text-fleet-sidebar hover:text-fleet-primary" aria-label="Edit post inspection report">Edit</button>
                                        <form
                                            action="<?= htmlspecialchars($postInspectionFormAction, ENT_QUOTES, 'UTF-8'); ?>"
                                            method="post"
                                            data-delete-name="<?= htmlspecialchars($report['vehicle'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-delete-detail="<?= htmlspecialchars(trim($report['invoice'] . ' - ' . $report['date'] . ' - UGX ' . number_format((float) $report['repair_cost'])), ENT_QUOTES, 'UTF-8'); ?>"
                                        >
                                            <!-- Delete uses a dedicated POST form so the action stays explicit and safe. -->
                                            <input type="hidden" name="inspection_scope" value="post">
                                            <input type="hidden" name="inspection_action" value="delete">
                                            <input type="hidden" name="report_id" value="<?= htmlspecialchars((string) $report['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" data-open-post-inspection-delete class="text-fleet-danger hover:text-fleet-danger-strong" aria-label="Delete post inspection report">Del</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-fleet-surface-muted font-extrabold text-fleet-ink">
                        <tr>
                            <td class="border border-fleet-line px-4 py-3" colspan="8">TOTAL REPAIR COSTS</td>
                            <td class="border border-fleet-line px-4 py-3">UGX <?= number_format((float) $totalRepairCost); ?></td>
                            <td class="border border-fleet-line px-4 py-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>

    <div
        id="post-inspection-modal"
        class="fixed inset-0 z-50 <?= $shouldOpenPostInspectionModal ? 'flex' : 'hidden'; ?> items-start justify-center overflow-y-auto bg-black/75 px-4 py-5 sm:items-center"
        aria-hidden="<?= $shouldOpenPostInspectionModal ? 'false' : 'true'; ?>"
        data-open-on-load="<?= $shouldOpenPostInspectionModal ? 'true' : 'false'; ?>"
    >
        <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full max-w-[900px] overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="post-inspection-modal-title">
            <!-- Failed submissions reopen this modal and keep entered values in place. -->
            <form class="p-6" action="<?= htmlspecialchars($postInspectionFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" data-post-inspection-form>
                <input type="hidden" name="inspection_scope" value="post">
                <input type="hidden" name="inspection_action" value="<?= $postInspectionFormMode === 'update' ? 'update' : 'create'; ?>" data-post-inspection-action-field>
                <input type="hidden" name="report_id" value="<?= htmlspecialchars($postInspectionFormData['report_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-post-inspection-report-id-field>

                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 id="post-inspection-modal-title" class="text-xl font-extrabold text-fleet-ink" data-post-inspection-modal-title><?= $postInspectionFormMode === 'update' ? 'Edit Post-Inspection Report' : 'New Post-Inspection Report'; ?></h2>
                        <p class="mt-1 text-xs text-fleet-muted">Busitema University - Transport Unit</p>
                    </div>
                    <button type="button" data-close-post-inspection-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close post-inspection form">&times;</button>
                </div>

                <div class="form-section-title"><span>1</span>Inspection Details</div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Invoice Number *</span>
                        <input name="invoice_number" type="text" required autofocus class="vehicle-form-control" placeholder="e.g. INV-2024-001" value="<?= htmlspecialchars($postInspectionFormData['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Date of Inspection *</span>
                        <input name="inspection_date" type="date" required class="vehicle-form-control" value="<?= htmlspecialchars($postInspectionFormData['inspection_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Inspector Name *</span>
                        <input name="inspector_name" type="text" required class="vehicle-form-control" placeholder="Full name" value="<?= htmlspecialchars($postInspectionFormData['inspector_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Inspector Title</span>
                        <input name="inspector_title" type="text" class="vehicle-form-control" placeholder="e.g. Transport Officer" value="<?= htmlspecialchars($postInspectionFormData['inspector_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>

                <div class="form-section-title mt-6"><span>2</span>Vehicle Details</div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Vehicle *</span>
                        <select name="vehicle" required class="vehicle-form-control">
                            <option value="">Select vehicle</option>
                            <?php foreach ($postInspectionVehicleOptions as $vehicleOption): ?>
                                <option value="<?= htmlspecialchars((string) $vehicleOption['id'], ENT_QUOTES, 'UTF-8'); ?>" <?= (($postInspectionFormData['vehicle'] ?? '') === (string) $vehicleOption['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($vehicleOption['registration_no'] . ' - ' . trim($vehicleOption['make'] . ' ' . $vehicleOption['model']), ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Mileage at Inspection (km)</span>
                        <input name="mileage" type="number" min="0" class="vehicle-form-control" placeholder="e.g. 45230" value="<?= htmlspecialchars($postInspectionFormData['mileage'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>

                <div class="form-section-title mt-6"><span>3</span>Vehicle Systems Checklist</div>
                <div class="overflow-hidden rounded-lg border border-fleet-line">
                    <div class="grid grid-cols-[1fr_2fr_1.5fr] bg-fleet-surface-muted px-4 py-3 text-sm font-extrabold text-fleet-sidebar">
                        <span>System</span>
                        <span>Condition</span>
                        <span>Remarks</span>
                    </div>
                    <?php
                    $postedSystemRows = postInspectionBuildSystemRowsFromFormData($postInspectionFormData);
                    foreach ($postInspectionSystems as $systemIndex => $system):
                        $systemRow = $postedSystemRows[$systemIndex] ?? ['system_name' => $system, 'condition_status' => '', 'remarks' => ''];
                    ?>
                        <div class="grid grid-cols-[1fr_2fr_1.5fr] items-center gap-3 border-t border-fleet-line px-4 py-3 text-sm">
                            <span class="font-medium text-fleet-sidebar"><?= htmlspecialchars($system, ENT_QUOTES, 'UTF-8'); ?></span>
                            <input type="hidden" name="system_name[]" value="<?= htmlspecialchars($system, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="flex flex-wrap gap-2">
                                <label class="inspection-pill"><input type="radio" name="system_status[<?= $systemIndex; ?>]" value="good" <?= ($systemRow['condition_status'] ?? '') === 'good' ? 'checked' : ''; ?>> Good</label>
                                <label class="inspection-pill"><input type="radio" name="system_status[<?= $systemIndex; ?>]" value="fair" <?= ($systemRow['condition_status'] ?? '') === 'fair' ? 'checked' : ''; ?>> Fair</label>
                                <label class="inspection-pill"><input type="radio" name="system_status[<?= $systemIndex; ?>]" value="faulty" <?= ($systemRow['condition_status'] ?? '') === 'faulty' ? 'checked' : ''; ?>> Faulty</label>
                            </div>
                            <input name="system_remarks[]" type="text" class="vehicle-form-control" placeholder="Optional note" value="<?= htmlspecialchars($systemRow['remarks'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-section-title mt-6"><span>4</span>Works Done</div>
                <label class="block">
                    <textarea name="works_done" class="vehicle-form-control min-h-24 resize-y py-3" placeholder="Describe all works and repairs carried out on the vehicle..."><?= htmlspecialchars($postInspectionFormData['works_done'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </label>

                <div class="form-section-title mt-6"><span>5</span>Invoice &amp; Payment Details</div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block md:col-span-2">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Post-Inspection Invoice No.</span>
                        <input name="post_invoice" type="text" class="vehicle-form-control" placeholder="e.g. PINV-2024-001" value="<?= htmlspecialchars($postInspectionFormData['post_invoice'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Amount Spent (UGX)</span>
                        <input name="amount_spent" type="number" min="0" step="0.01" class="vehicle-form-control" value="<?= htmlspecialchars($postInspectionFormData['amount_spent'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Service Provider</span>
                        <select name="service_provider" class="vehicle-form-control">
                            <option value="">Select provider</option>
                            <?php foreach ($postInspectionProviderOptions as $providerOption): ?>
                                <option value="<?= htmlspecialchars((string) $providerOption['id'], ENT_QUOTES, 'UTF-8'); ?>" <?= (($postInspectionFormData['service_provider'] ?? '') === (string) $providerOption['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($providerOption['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Overall Status</span>
                        <select name="overall_status" class="vehicle-form-control">
                            <option value="">Select status</option>
                            <option value="good" <?= (($postInspectionFormData['overall_status'] ?? '') === 'good') ? 'selected' : ''; ?>>Good</option>
                            <option value="fair" <?= (($postInspectionFormData['overall_status'] ?? '') === 'fair') ? 'selected' : ''; ?>>Fair</option>
                            <option value="faulty" <?= (($postInspectionFormData['overall_status'] ?? '') === 'faulty') ? 'selected' : ''; ?>>Faulty</option>
                            <option value="completed" <?= (($postInspectionFormData['overall_status'] ?? '') === 'completed') ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </label>
                </div>

                <div class="form-section-title mt-6"><span>6</span>Recommendation</div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block md:col-span-2">
                        <textarea name="recommendation" class="vehicle-form-control min-h-20 resize-y py-3"><?= htmlspecialchars($postInspectionFormData['recommendation'] ?? 'This is to request you authorise payment to the above service provider...', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" data-close-post-inspection-modal class="h-10 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">Cancel</button>
                    <button type="submit" data-post-inspection-submit-button class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active"><?= $postInspectionFormMode === 'update' ? 'Save Changes' : 'Save Report'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <div id="post-inspection-delete-modal" class="logbook-delete-overlay" aria-hidden="true">
        <div class="logbook-delete-card" role="dialog" aria-modal="true" aria-labelledby="post-inspection-delete-modal-title">
            <div class="logbook-delete-header">
                <div class="flex items-center gap-4">
                    <div class="logbook-delete-icon">!</div>
                    <div>
                        <p class="logbook-delete-eyebrow">Delete Confirmation</p>
                        <h2 id="post-inspection-delete-modal-title" class="logbook-delete-title">Remove post-inspection report?</h2>
                    </div>
                </div>
            </div>
            <div class="logbook-delete-body">
                <p class="logbook-delete-copy">You are about to permanently remove this post-inspection report from the official records.</p>
                <div class="mt-4 rounded-lg border border-fleet-line bg-fleet-surface-muted px-4 py-3">
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Selected Report</p>
                    <p class="mt-1 text-base font-extrabold text-fleet-ink" data-post-inspection-delete-name>This report</p>
                    <p class="mt-1 text-sm text-fleet-muted" data-post-inspection-delete-detail>Vehicle, invoice, date, and repair cost will appear here.</p>
                </div>
                <p class="mt-4 text-sm text-fleet-muted">This action cannot be undone.</p>
                <div class="logbook-delete-actions">
                    <button type="button" data-cancel-post-inspection-delete class="logbook-delete-button logbook-delete-button-secondary">
                        Keep Report
                    </button>
                    <button type="button" data-confirm-post-inspection-delete class="logbook-delete-button logbook-delete-button-danger">
                        Delete Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
