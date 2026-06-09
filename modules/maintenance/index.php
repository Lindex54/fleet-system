<?php
// Maintenance records page backed by the maintenance handler and database.
$activePage = 'maintenance';
require_once __DIR__ . '/../../handlers/maintenance.php';
// Load live maintenance rows, totals, dropdown options, and any flash UI state from the handler.
extract(maintenanceFetchPageData());
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
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
                <button type="button" data-open-maintenance-modal class="inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>New Record</span>
                </button>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>
        </div>

        <?php if (!empty($maintenanceNotification)): ?>
            <?php $isSuccessNotice = ($maintenanceNotification['type'] ?? '') === 'success'; ?>
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
                                    <?= htmlspecialchars($maintenanceNotification['title'] ?? 'Maintenance update', ENT_QUOTES, 'UTF-8'); ?>
                                </h2>
                                <p class="mt-1 text-sm leading-6 text-fleet-ink">
                                    <?= htmlspecialchars($maintenanceNotification['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
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

        <section class="<?= $hasRecords ? 'hidden' : 'flex'; ?> min-h-[420px] items-center justify-center">
            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-200 text-fleet-muted">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.7 2.7-3-3z"></path>
                    </svg>
                </div>
                <h2 class="mt-5 text-lg font-extrabold text-fleet-ink">No maintenance records found</h2>
                <p class="mt-2 text-sm text-fleet-muted">Create records to track repairs, service history, and costs.</p>
                <button type="button" data-open-maintenance-modal class="mt-6 inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>New Record</span>
                </button>
            </div>
        </section>

        <section data-print-root class="<?= $hasRecords ? 'block' : 'hidden'; ?> overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="border-b border-fleet-line-soft px-4 py-4 sm:px-5">
                <div class="grid gap-3 lg:grid-cols-[1fr_180px]">
                    <label class="relative block">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">Q</span>
                        <input id="maintenance-search" type="search" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-11 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search maintenance records...">
                    </label>
                    <select id="maintenance-status" class="vehicle-form-control h-11">
                        <option value="all">All Status</option>
                        <option value="reported">Reported</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <section class="flex items-center justify-between border-b border-fleet-line-soft bg-fleet-surface px-5 py-4 text-sm shadow-sm">
                <span class="text-fleet-muted"><?= count($records); ?> record(s)</span>
                <span class="font-extrabold text-fleet-ink">Total Cost: UGX <?= number_format((float) $totalCost); ?></span>
            </section>

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
                            <tr
                                class="maintenance-row hover:bg-fleet-surface-muted/70"
                                data-status="<?= htmlspecialchars($record['status_value'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-search="<?= htmlspecialchars(strtolower(implode(' ', [$record['date'], $record['vehicle'], $record['type'], $record['description'], $record['provider'], $record['status']])), ENT_QUOTES, 'UTF-8'); ?>"
                                data-record-id="<?= htmlspecialchars((string) $record['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-vehicle="<?= htmlspecialchars($record['vehicle'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-date="<?= htmlspecialchars($record['date'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-provider="<?= htmlspecialchars($record['provider'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-cost="<?= htmlspecialchars($record['cost'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-vehicle-id="<?= htmlspecialchars((string) $record['vehicle_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-service-provider-id="<?= htmlspecialchars((string) ($record['service_provider_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                data-type="<?= htmlspecialchars($record['type_value'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-date-reported="<?= htmlspecialchars($record['date_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-date-completed="<?= htmlspecialchars($record['date_completed_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-description="<?= htmlspecialchars($record['description_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-parts-replaced="<?= htmlspecialchars($record['parts_replaced_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-total-cost="<?= htmlspecialchars($record['cost_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-mileage-at-service="<?= htmlspecialchars($record['mileage_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-invoice-number="<?= htmlspecialchars($record['invoice_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-status-value="<?= htmlspecialchars($record['status_value'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-status-label="<?= htmlspecialchars($record['status'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-remarks="<?= htmlspecialchars($record['remarks_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($record['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 font-extrabold text-fleet-ink"><?= htmlspecialchars($record['vehicle'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 text-fleet-muted"><?= htmlspecialchars($record['type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 text-fleet-muted"><?= htmlspecialchars($record['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 text-fleet-muted"><?= htmlspecialchars($record['provider'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 font-extrabold text-fleet-ink"><?= htmlspecialchars($record['cost'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4">
                                    <?php if ($record['status'] === 'Completed'): ?>
                                        <span class="rounded-lg border border-green-200 bg-fleet-success-soft px-3 py-1 text-xs font-semibold text-fleet-success">Completed</span>
                                    <?php elseif ($record['status'] === 'In Progress'): ?>
                                        <span class="rounded-lg border border-orange-200 bg-fleet-warning-soft px-3 py-1 text-xs font-semibold text-fleet-warning-strong">In Progress</span>
                                    <?php elseif ($record['status'] === 'Cancelled'): ?>
                                        <span class="rounded-lg border border-red-200 bg-red-50 px-3 py-1 text-xs font-semibold text-fleet-danger">Cancelled</span>
                                    <?php else: ?>
                                        <span class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-fleet-primary">Reported</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-3">
                                        <button type="button" data-view-maintenance-entry class="text-fleet-ink hover:text-fleet-primary" aria-label="View maintenance record">View</button>
                                        <button type="button" data-edit-maintenance-entry class="text-fleet-sidebar hover:text-fleet-primary" aria-label="Edit maintenance record">Edit</button>
                                        <form
                                            action="<?= htmlspecialchars($maintenanceFormAction, ENT_QUOTES, 'UTF-8'); ?>"
                                            method="post"
                                            data-delete-maintenance-form
                                            data-delete-name="<?= htmlspecialchars($record['vehicle'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-delete-detail="<?= htmlspecialchars(trim($record['date'] . ' - ' . $record['type'] . ' - ' . $record['cost']), ENT_QUOTES, 'UTF-8'); ?>"
                                        >
                                            <!-- Delete uses a dedicated POST form so the action stays explicit and safe. -->
                                            <input type="hidden" name="maintenance_action" value="delete">
                                            <input type="hidden" name="record_id" value="<?= htmlspecialchars((string) $record['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" data-open-maintenance-delete class="text-fleet-danger hover:text-fleet-danger-strong" aria-label="Delete maintenance record">Del</button>
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
        id="maintenance-modal"
        class="fixed inset-0 z-50 <?= $shouldOpenMaintenanceModal ? 'flex' : 'hidden'; ?> items-center justify-center bg-black/75 px-4 py-6"
        aria-hidden="<?= $shouldOpenMaintenanceModal ? 'false' : 'true'; ?>"
        data-open-on-load="<?= $shouldOpenMaintenanceModal ? 'true' : 'false'; ?>"
    >
        <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full max-w-2xl overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="maintenance-modal-title">
            <!-- Failed submissions reopen this modal and keep entered values in place. jQuery adds safe validation and AJAX feedback here. -->
            <form class="p-6" action="<?= htmlspecialchars($maintenanceFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" data-maintenance-form data-fleet-ajax="true">
                <input type="hidden" name="maintenance_action" value="<?= $maintenanceFormMode === 'update' ? 'update' : 'create'; ?>" data-maintenance-action-field>
                <input type="hidden" name="record_id" value="<?= htmlspecialchars($maintenanceFormData['record_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-maintenance-record-id-field>

                <div class="mb-5 flex items-center justify-between gap-4">
                    <h2 id="maintenance-modal-title" class="text-xl font-extrabold text-fleet-ink" data-maintenance-modal-title><?= $maintenanceFormMode === 'update' ? 'Edit Maintenance Record' : 'New Maintenance Record'; ?></h2>
                    <button type="button" data-close-maintenance-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close maintenance record form">&times;</button>
                </div>
                <div data-fleet-feedback-host></div>

                <div class="grid gap-5 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Vehicle *</span>
                        <select name="vehicle" required autofocus class="vehicle-form-control">
                            <option value="">Select vehicle</option>
                            <?php foreach ($maintenanceVehicleOptions as $vehicleOption): ?>
                                <option value="<?= htmlspecialchars((string) $vehicleOption['id'], ENT_QUOTES, 'UTF-8'); ?>" <?= (($maintenanceFormData['vehicle'] ?? '') === (string) $vehicleOption['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($vehicleOption['registration_no'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Maintenance Type *</span>
                        <select name="maintenance_type" required class="vehicle-form-control">
                            <option value="repair" <?= (($maintenanceFormData['maintenance_type'] ?? 'repair') === 'repair') ? 'selected' : ''; ?>>repair</option>
                            <option value="routine_service" <?= (($maintenanceFormData['maintenance_type'] ?? '') === 'routine_service') ? 'selected' : ''; ?>>routine service</option>
                            <option value="inspection" <?= (($maintenanceFormData['maintenance_type'] ?? '') === 'inspection') ? 'selected' : ''; ?>>inspection</option>
                            <option value="brake_service" <?= (($maintenanceFormData['maintenance_type'] ?? '') === 'brake_service') ? 'selected' : ''; ?>>brake service</option>
                            <option value="other" <?= (($maintenanceFormData['maintenance_type'] ?? '') === 'other') ? 'selected' : ''; ?>>other</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Date Reported *</span>
                        <input name="date_reported" type="date" required class="vehicle-form-control" value="<?= htmlspecialchars($maintenanceFormData['date_reported'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Date Completed</span>
                        <input name="date_completed" type="date" class="vehicle-form-control" value="<?= htmlspecialchars($maintenanceFormData['date_completed'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block md:col-span-2">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Description *</span>
                        <textarea name="description" required class="vehicle-form-control min-h-16 resize-y py-3" placeholder="Describe the issue and work done"><?= htmlspecialchars($maintenanceFormData['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Service Provider</span>
                        <select name="service_provider" class="vehicle-form-control">
                            <option value="">Select provider</option>
                            <?php foreach ($maintenanceProviderOptions as $providerOption): ?>
                                <option value="<?= htmlspecialchars((string) $providerOption['id'], ENT_QUOTES, 'UTF-8'); ?>" <?= (($maintenanceFormData['service_provider'] ?? '') === (string) $providerOption['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($providerOption['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Parts Replaced</span>
                        <input name="parts_replaced" type="text" class="vehicle-form-control" placeholder="e.g. brake pads, oil filter" value="<?= htmlspecialchars($maintenanceFormData['parts_replaced'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Total Cost (UGX)</span>
                        <input name="total_cost" type="number" min="0" step="0.01" class="vehicle-form-control" value="<?= htmlspecialchars($maintenanceFormData['total_cost'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Mileage at Service</span>
                        <input name="mileage_at_service" type="number" min="0" class="vehicle-form-control" value="<?= htmlspecialchars($maintenanceFormData['mileage_at_service'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Invoice Number</span>
                        <input name="invoice_number" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($maintenanceFormData['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Status</span>
                        <select name="status" class="vehicle-form-control">
                            <option value="reported" <?= (($maintenanceFormData['status'] ?? 'reported') === 'reported') ? 'selected' : ''; ?>>Reported</option>
                            <option value="in_progress" <?= (($maintenanceFormData['status'] ?? '') === 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?= (($maintenanceFormData['status'] ?? '') === 'completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?= (($maintenanceFormData['status'] ?? '') === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </label>

                    <label class="block md:col-span-2">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Remarks</span>
                        <input name="remarks" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($maintenanceFormData['remarks'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" data-close-maintenance-modal class="h-10 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">Cancel</button>
                    <button type="submit" class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active" data-maintenance-submit-button data-loading-text="Saving Maintenance..."><?= $maintenanceFormMode === 'update' ? 'Save Changes' : 'Create Record'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/view-modal.php'; ?>

    <div id="maintenance-delete-modal" class="logbook-delete-overlay" aria-hidden="true">
        <div class="logbook-delete-card" role="dialog" aria-modal="true" aria-labelledby="maintenance-delete-modal-title">
            <div class="logbook-delete-header">
                <div class="flex items-center gap-4">
                    <div class="logbook-delete-icon">!</div>
                    <div>
                        <p class="logbook-delete-eyebrow">Delete Confirmation</p>
                        <h2 id="maintenance-delete-modal-title" class="logbook-delete-title">Remove maintenance record?</h2>
                    </div>
                </div>
            </div>
            <div class="logbook-delete-body">
                <p class="logbook-delete-copy">You are about to permanently remove the selected maintenance record for this vehicle.</p>
                <div class="mt-4 rounded-lg border border-fleet-line bg-fleet-surface-muted px-4 py-3">
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Selected Record</p>
                    <p class="mt-1 text-base font-extrabold text-fleet-ink" data-maintenance-delete-name>Selected vehicle</p>
                    <p class="mt-1 text-sm text-fleet-muted" data-maintenance-delete-detail>Maintenance date, type, provider, and cost will appear here.</p>
                </div>
                <p class="mt-4 text-sm text-fleet-muted">This action cannot be undone.</p>
                <div class="logbook-delete-actions">
                    <button type="button" data-cancel-maintenance-delete class="logbook-delete-button logbook-delete-button-secondary">
                        Keep Record
                    </button>
                    <button type="button" data-confirm-maintenance-delete class="logbook-delete-button logbook-delete-button-danger">
                        Delete Record
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
