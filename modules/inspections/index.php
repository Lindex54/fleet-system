<?php
// Pre-inspection reports page backed by the inspection handler and database.
$activePage = 'pre-inspection';
require_once __DIR__ . '/../../handlers/inspection.php';
// Load live pre-inspection rows, dropdown options, and any flash UI state from the handler.
extract(inspectionFetchPageData());
$preInspectionReferencePreview = inspectionBuildInvoiceNumberPreview((string) ($preInspectionFormData['inspection_date'] ?? date('Y-m-d')));
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
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

        <?php if (!empty($preInspectionNotification)): ?>
            <?php $isSuccessNotice = ($preInspectionNotification['type'] ?? '') === 'success'; ?>
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
                                    <?= htmlspecialchars($preInspectionNotification['title'] ?? 'Pre-inspection update', ENT_QUOTES, 'UTF-8'); ?>
                                </h2>
                                <p class="mt-1 text-sm leading-6 text-fleet-ink">
                                    <?= htmlspecialchars($preInspectionNotification['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
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

        <section data-print-root class="<?= $hasReports ? 'block' : 'hidden'; ?> overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="border-b border-fleet-line-soft px-4 py-4 sm:px-5">
                <label class="relative block max-w-md">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">Q</span>
                    <input id="pre-inspection-search" type="search" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-11 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search by vehicle, inspector, invoice...">
                </label>
            </div>

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
                            <tr
                                class="pre-inspection-row transition hover:bg-fleet-surface-muted/70"
                                data-search="<?= htmlspecialchars(strtolower(implode(' ', [$report['invoice'], $report['date'], $report['vehicle'], $report['make_model'], $report['inspector'], $report['overall'], $report['defects']])), ENT_QUOTES, 'UTF-8'); ?>"
                                data-report-id="<?= htmlspecialchars((string) $report['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-vehicle-id="<?= htmlspecialchars((string) $report['vehicle_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-vehicle="<?= htmlspecialchars($report['vehicle'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-make-model="<?= htmlspecialchars($report['make_model'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-inspector="<?= htmlspecialchars($report['inspector'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-overall="<?= htmlspecialchars($report['overall'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-invoice-number="<?= htmlspecialchars($report['invoice_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-inspection-date="<?= htmlspecialchars($report['date_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-inspector-name="<?= htmlspecialchars($report['inspector_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-inspector-title="<?= htmlspecialchars($report['inspector_title_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-mileage="<?= htmlspecialchars($report['mileage_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-overall-status="<?= htmlspecialchars($report['overall_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-defects="<?= htmlspecialchars($report['defects_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-memo-to="<?= htmlspecialchars($report['memo_to_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-memo-thru-one="<?= htmlspecialchars($report['memo_thru_one_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-memo-thru-two="<?= htmlspecialchars($report['memo_thru_two_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-memo-from="<?= htmlspecialchars($report['memo_from_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-vehicle-description="<?= htmlspecialchars($report['vehicle_description_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-closing-note="<?= htmlspecialchars($report['closing_note_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-cc="<?= htmlspecialchars($report['cc_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-items="<?= htmlspecialchars($report['items_json'], ENT_QUOTES, 'UTF-8'); ?>"
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
                                    <?php elseif ($report['overall'] === 'Faulty' || $report['overall'] === 'Needs Repair'): ?>
                                        <span class="rounded-full bg-fleet-badge-red px-3 py-1 text-xs font-bold text-fleet-danger"><?= htmlspecialchars($report['overall'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php else: ?>
                                        <span class="rounded-full bg-fleet-badge-red px-3 py-1 text-xs font-bold text-fleet-danger">--</span>
                                    <?php endif; ?>
                                </td>
                                <td class="border border-fleet-line px-4 py-4 text-fleet-muted"><?= htmlspecialchars($report['defects'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4">
                                    <div class="flex justify-end gap-4">
                                        <button type="button" data-view-pre-inspection-entry class="text-fleet-ink hover:text-fleet-primary" aria-label="View inspection <?= $index + 1; ?>">View</button>
                                        <button type="button" data-edit-pre-inspection-entry class="text-fleet-sidebar hover:text-fleet-primary" aria-label="Edit pre-inspection report">Edit</button>
                                        <form
                                            action="<?= htmlspecialchars($preInspectionFormAction, ENT_QUOTES, 'UTF-8'); ?>"
                                            method="post"
                                            data-delete-name="<?= htmlspecialchars($report['vehicle'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-delete-detail="<?= htmlspecialchars(trim($report['invoice'] . ' - ' . $report['date'] . ' - ' . $report['inspector']), ENT_QUOTES, 'UTF-8'); ?>"
                                        >
                                            <!-- Delete uses a dedicated POST form so the action stays explicit and safe. -->
                                            <input type="hidden" name="inspection_action" value="delete">
                                            <input type="hidden" name="report_id" value="<?= htmlspecialchars((string) $report['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" data-open-pre-inspection-delete class="text-fleet-danger hover:text-fleet-danger-strong" aria-label="Delete pre-inspection report">Del</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div
        id="pre-inspection-modal"
        class="fixed inset-0 z-50 <?= $shouldOpenPreInspectionModal ? 'flex' : 'hidden'; ?> items-start justify-center overflow-y-auto bg-black/75 px-4 py-5 sm:items-center"
        aria-hidden="<?= $shouldOpenPreInspectionModal ? 'false' : 'true'; ?>"
        data-open-on-load="<?= $shouldOpenPreInspectionModal ? 'true' : 'false'; ?>"
    >
        <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full max-w-[900px] overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="pre-inspection-modal-title">
            <!-- Failed submissions reopen this modal and keep entered values in place. jQuery adds safe validation and AJAX feedback here. -->
            <form class="p-6" action="<?= htmlspecialchars($preInspectionFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" data-pre-inspection-form data-fleet-ajax="true">
                <input type="hidden" name="inspection_action" value="<?= $preInspectionFormMode === 'update' ? 'update' : 'create'; ?>" data-pre-inspection-action-field>
                <input type="hidden" name="report_id" value="<?= htmlspecialchars($preInspectionFormData['report_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-pre-inspection-report-id-field>

                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 id="pre-inspection-modal-title" class="text-xl font-extrabold text-fleet-ink" data-pre-inspection-modal-title><?= $preInspectionFormMode === 'update' ? 'Edit Pre-Inspection Report' : 'New Pre-Inspection Report'; ?></h2>
                        <p class="mt-1 text-xs text-fleet-muted">Busitema University - Transport Unit</p>
                    </div>
                    <button type="button" data-close-pre-inspection-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close pre-inspection form">&times;</button>
                </div>
                <div data-fleet-feedback-host></div>

                <div class="form-section-title"><span>1</span>Inspection Details</div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Pre-Invoice Number</span>
                        <input
                            type="text"
                            readonly
                            tabindex="-1"
                            class="vehicle-form-control cursor-not-allowed bg-fleet-surface-muted font-semibold text-fleet-sidebar"
                            value="<?= htmlspecialchars($preInspectionFormData['invoice_number'] ?? $preInspectionReferencePreview, ENT_QUOTES, 'UTF-8'); ?>"
                            data-pre-inspection-reference-preview
                        >
                        <span class="mt-2 block text-xs text-fleet-muted">Generated automatically as <span class="font-semibold">BUEMIS_YYYYMMDD_001</span>, <span class="font-semibold">_002</span>, and so on for the same date.</span>
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Date of Inspection *</span>
                        <input name="inspection_date" type="date" required autofocus class="vehicle-form-control" value="<?= htmlspecialchars($preInspectionFormData['inspection_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Inspector Name *</span>
                        <input name="inspector_name" type="text" required class="vehicle-form-control" placeholder="Full name" value="<?= htmlspecialchars($preInspectionFormData['inspector_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Inspector Title</span>
                        <input name="inspector_title" type="text" class="vehicle-form-control" placeholder="e.g. Transport Officer" value="<?= htmlspecialchars($preInspectionFormData['inspector_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>

                <div class="form-section-title mt-6"><span>2</span>Vehicle Details</div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Vehicle *</span>
                        <select name="vehicle" required class="vehicle-form-control" data-pre-inspection-vehicle-select>
                            <option value="">Select vehicle</option>
                            <?php foreach ($preInspectionVehicleOptions as $vehicleOption): ?>
                                <option
                                    value="<?= htmlspecialchars((string) $vehicleOption['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-current-mileage="<?= htmlspecialchars((string) ($vehicleOption['current_mileage'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                    <?= (($preInspectionFormData['vehicle'] ?? '') === (string) $vehicleOption['id']) ? 'selected' : ''; ?>
                                >
                                    <?= htmlspecialchars($vehicleOption['registration_no'] . ' - ' . trim($vehicleOption['make'] . ' ' . $vehicleOption['model']), ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Mileage at Inspection (km)</span>
                        <input name="mileage" type="number" min="0" class="vehicle-form-control cursor-not-allowed bg-fleet-surface-muted" placeholder="e.g. 45230" value="<?= htmlspecialchars($preInspectionFormData['mileage'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-pre-inspection-mileage-field readonly>
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Overall Status</span>
                        <select name="overall_status" class="vehicle-form-control">
                            <option value="">Select status</option>
                            <option value="good" <?= (($preInspectionFormData['overall_status'] ?? '') === 'good') ? 'selected' : ''; ?>>Good</option>
                            <option value="fair" <?= (($preInspectionFormData['overall_status'] ?? '') === 'fair') ? 'selected' : ''; ?>>Fair</option>
                            <option value="faulty" <?= (($preInspectionFormData['overall_status'] ?? '') === 'faulty') ? 'selected' : ''; ?>>Faulty</option>
                            <option value="needs_repair" <?= (($preInspectionFormData['overall_status'] ?? '') === 'needs_repair') ? 'selected' : ''; ?>>Needs Repair</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Defects Summary</span>
                        <input name="defects" type="text" class="vehicle-form-control" placeholder="e.g. Cooling leak, worn brake pads" value="<?= htmlspecialchars($preInspectionFormData['defects'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>

                <div class="form-section-title mt-6"><span>3</span>Memo Header</div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">To</span>
                        <input name="memo_to" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($preInspectionFormData['memo_to'] ?? 'University Secretary', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Thru (1st)</span>
                        <input name="memo_thru_one" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($preInspectionFormData['memo_thru_one'] ?? 'University Bursar', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Thru (2nd)</span>
                        <input name="memo_thru_two" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($preInspectionFormData['memo_thru_two'] ?? 'Programme Controller', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">From</span>
                        <input name="memo_from" type="text" class="vehicle-form-control" placeholder="e.g. Ag. AEO (Mech.) Simali Habert" value="<?= htmlspecialchars($preInspectionFormData['memo_from'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="block md:col-span-2">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Vehicle Description (for subject)</span>
                        <input name="vehicle_description" type="text" class="vehicle-form-control" placeholder="e.g. Mitsubishi double cabin pickup model 2007" value="<?= htmlspecialchars($preInspectionFormData['vehicle_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>

                <div class="form-section-title mt-6"><span>4</span>Inspection Items</div>
                <div data-pre-inspection-items class="space-y-4">
                    <?php foreach ($preInspectionItemRows as $itemIndex => $itemRow): ?>
                        <div class="rounded-lg border border-fleet-line bg-fleet-surface-muted p-4" data-pre-inspection-item-row>
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-fleet-muted">Item <?= $itemIndex + 1; ?></p>
                                <button type="button" data-remove-pre-inspection-item class="text-xs font-semibold text-fleet-danger hover:text-fleet-danger-strong">
                                    Remove
                                </button>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Inspection Point</span>
                                    <input name="inspection_point[]" type="text" class="vehicle-form-control" placeholder="e.g. Engine" value="<?= htmlspecialchars($itemRow['inspection_point'], ENT_QUOTES, 'UTF-8'); ?>">
                                </label>
                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Status / Findings</span>
                                    <textarea name="inspection_findings[]" class="vehicle-form-control min-h-20 resize-y py-3" placeholder="Describe the condition found..."><?= htmlspecialchars($itemRow['inspection_findings'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </label>
                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Remarks / Action Point</span>
                                    <textarea name="inspection_action[]" class="vehicle-form-control min-h-20 resize-y py-3" placeholder="Recommended action..."><?= htmlspecialchars($itemRow['inspection_action'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" data-add-pre-inspection-item class="mt-3 h-10 w-full rounded-lg border border-fleet-line bg-fleet-surface text-sm font-semibold text-fleet-sidebar shadow-sm hover:bg-fleet-surface-muted">+ Add Inspection Item</button>

                <template id="pre-inspection-item-template">
                    <div class="rounded-lg border border-fleet-line bg-fleet-surface-muted p-4" data-pre-inspection-item-row>
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold text-fleet-muted" data-pre-inspection-item-label>Item</p>
                            <button type="button" data-remove-pre-inspection-item class="text-xs font-semibold text-fleet-danger hover:text-fleet-danger-strong">
                                Remove
                            </button>
                        </div>
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
                </template>

                <div class="form-section-title mt-6"><span>5</span>Closing &amp; CC</div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block md:col-span-2">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Closing Note</span>
                        <textarea name="closing_note" class="vehicle-form-control min-h-20 resize-y py-3"><?= htmlspecialchars($preInspectionFormData['closing_note'] ?? 'The purpose of this report is to therefore request you authorize repair and maintenance works on this vehicle for full restoration.', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">CC</span>
                        <input name="cc" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($preInspectionFormData['cc'] ?? 'Senior Estates Officer', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" data-close-pre-inspection-modal class="h-10 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">Cancel</button>
                    <button type="submit" data-pre-inspection-submit-button data-loading-text="Saving Inspection..." class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active"><?= $preInspectionFormMode === 'update' ? 'Save Changes' : 'Save Report'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/view-modal.php'; ?>

    <div id="pre-inspection-delete-modal" class="logbook-delete-overlay" aria-hidden="true">
        <div class="logbook-delete-card" role="dialog" aria-modal="true" aria-labelledby="pre-inspection-delete-modal-title">
            <div class="logbook-delete-header">
                <div class="flex items-center gap-4">
                    <div class="logbook-delete-icon">!</div>
                    <div>
                        <p class="logbook-delete-eyebrow">Delete Confirmation</p>
                        <h2 id="pre-inspection-delete-modal-title" class="logbook-delete-title">Remove pre-inspection report?</h2>
                    </div>
                </div>
            </div>
            <div class="logbook-delete-body">
                <p class="logbook-delete-copy">You are about to permanently remove this pre-inspection report from the official records.</p>
                <div class="mt-4 rounded-lg border border-fleet-line bg-fleet-surface-muted px-4 py-3">
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Selected Report</p>
                    <p class="mt-1 text-base font-extrabold text-fleet-ink" data-pre-inspection-delete-name>This report</p>
                    <p class="mt-1 text-sm text-fleet-muted" data-pre-inspection-delete-detail>Vehicle, invoice, date, and inspector will appear here.</p>
                </div>
                <p class="mt-4 text-sm text-fleet-muted">This action cannot be undone.</p>
                <div class="logbook-delete-actions">
                    <button type="button" data-cancel-pre-inspection-delete class="logbook-delete-button logbook-delete-button-secondary">
                        Keep Report
                    </button>
                    <button type="button" data-confirm-pre-inspection-delete class="logbook-delete-button logbook-delete-button-danger">
                        Delete Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
