<?php
// Driver management page backed by the driver handler and database.
$activePage = 'drivers';
require_once __DIR__ . '/../../handlers/driver.php';
// Load live drivers, assignment options, and any flash UI state from the handler.
extract(driverFetchPageData());
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
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
                <button type="button" data-open-driver-modal class="inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>Add Driver</span>
                </button>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>
        </div>

        <?php if (!empty($driverNotification)): ?>
            <?php $isSuccessNotice = ($driverNotification['type'] ?? '') === 'success'; ?>
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
                                    <?= htmlspecialchars($driverNotification['title'] ?? 'Driver update', ENT_QUOTES, 'UTF-8'); ?>
                                </h2>
                                <p class="mt-1 text-sm leading-6 text-fleet-ink">
                                    <?= htmlspecialchars($driverNotification['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
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
                <button type="button" data-open-driver-modal class="mt-6 inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>Add Driver</span>
                </button>
            </div>
        </section>

        <section class="<?= $hasDrivers ? 'block' : 'hidden'; ?> overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="border-b border-fleet-line-soft px-4 py-4 sm:px-5">
                <!-- Search appears only when there are actual driver rows to filter. -->
                <label class="relative block max-w-md">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">Q</span>
                    <input id="driver-search" type="search" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-11 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search drivers...">
                </label>
            </div>

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
                            <tr
                                class="driver-row hover:bg-fleet-surface-muted/70"
                                data-search="<?= htmlspecialchars(strtolower(implode(' ', array_filter([$driver['name'], $driver['email'], $driver['phone'], $driver['license'], $driver['assigned'], $driver['department']] ))), ENT_QUOTES, 'UTF-8'); ?>"
                                data-driver-id="<?= htmlspecialchars((string) $driver['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-full-name="<?= htmlspecialchars($driver['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-employee-id="<?= htmlspecialchars($driver['employee_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-phone="<?= htmlspecialchars($driver['phone'] === '-' ? '' : $driver['phone'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-email="<?= htmlspecialchars($driver['email'] === '-' ? '' : $driver['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-license-number="<?= htmlspecialchars($driver['license'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-license-classes="<?= htmlspecialchars($driver['license_classes'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-license-expiry="<?= htmlspecialchars($driver['license_expiry'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-department="<?= htmlspecialchars($driver['department'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-assigned-vehicle-id="<?= htmlspecialchars((string) ($driver['assigned_vehicle_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                data-status="<?= htmlspecialchars($driver['status_value'], ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-fleet-primary-soft text-sm font-extrabold text-fleet-primary">
                                            <?= htmlspecialchars(strtoupper(substr($driver['name'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <div>
                                            <p class="font-extrabold text-fleet-ink"><?= htmlspecialchars($driver['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="text-xs text-fleet-muted"><?= htmlspecialchars($driver['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($driver['phone'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 font-semibold text-fleet-ink"><?= htmlspecialchars($driver['license'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($driver['assigned'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4">
                                    <?php if ($driver['status'] === 'Active'): ?>
                                        <span class="rounded-lg border border-green-200 bg-fleet-success-soft px-3 py-1 text-xs font-semibold text-fleet-success">Active</span>
                                    <?php elseif ($driver['status'] === 'Suspended'): ?>
                                        <span class="rounded-lg border border-orange-200 bg-fleet-warning-soft px-3 py-1 text-xs font-semibold text-fleet-warning-strong">Suspended</span>
                                    <?php else: ?>
                                        <span class="rounded-lg border border-fleet-line bg-slate-100 px-3 py-1 text-xs font-semibold text-fleet-muted">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-3">
                                        <button type="button" data-edit-driver-entry class="text-fleet-sidebar hover:text-fleet-primary" aria-label="Edit <?= htmlspecialchars($driver['name'], ENT_QUOTES, 'UTF-8'); ?>">Edit</button>
                                        <form action="<?= htmlspecialchars($driverFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" data-delete-driver-form>
                                            <!-- Delete uses a dedicated POST form so the action stays explicit and safe. -->
                                            <input type="hidden" name="driver_action" value="delete">
                                            <input type="hidden" name="driver_id" value="<?= htmlspecialchars((string) $driver['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" data-open-driver-delete class="text-fleet-danger hover:text-fleet-danger-strong" aria-label="Delete <?= htmlspecialchars($driver['name'], ENT_QUOTES, 'UTF-8'); ?>">Del</button>
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
        id="driver-modal"
        class="fixed inset-0 z-50 <?= $shouldOpenDriverModal ? 'flex' : 'hidden'; ?> items-center justify-center bg-black/75 px-4 py-6"
        aria-hidden="<?= $shouldOpenDriverModal ? 'false' : 'true'; ?>"
        data-open-on-load="<?= $shouldOpenDriverModal ? 'true' : 'false'; ?>"
    >
        <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full max-w-[520px] overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="driver-modal-title">
            <!-- Failed submissions reopen this modal and keep entered values in place. -->
            <form class="p-6" action="<?= htmlspecialchars($driverFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" data-driver-form>
                <input type="hidden" name="driver_action" value="<?= $driverFormMode === 'update' ? 'update' : 'create'; ?>" data-driver-action-field>
                <input type="hidden" name="driver_id" value="<?= htmlspecialchars($driverFormData['driver_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-driver-id-field>

                <div class="mb-5 flex items-center justify-between gap-4">
                    <h2 id="driver-modal-title" class="text-xl font-extrabold text-fleet-ink" data-driver-modal-title><?= $driverFormMode === 'update' ? 'Edit Driver' : 'Add New Driver'; ?></h2>
                    <button type="button" data-close-driver-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close driver form">&times;</button>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Full Name *</span>
                        <input name="full_name" type="text" required autofocus class="vehicle-form-control" placeholder="John Doe" value="<?= htmlspecialchars($driverFormData['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Employee ID</span>
                        <input name="employee_id" type="text" class="vehicle-form-control" placeholder="EMP-001" value="<?= htmlspecialchars($driverFormData['employee_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Phone</span>
                        <input name="phone" type="tel" class="vehicle-form-control" placeholder="+256 700 000000" value="<?= htmlspecialchars($driverFormData['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Email</span>
                        <input name="email" type="email" class="vehicle-form-control" value="<?= htmlspecialchars($driverFormData['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">License Number *</span>
                        <input name="license_number" type="text" required class="vehicle-form-control" value="<?= htmlspecialchars($driverFormData['license_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">License Class(es)</span>
                        <input name="license_classes" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($driverFormData['license_classes'] ?? 'B', ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="mt-2 block text-xs text-fleet-muted">Separate multiple classes with commas</span>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">License Expiry</span>
                        <input name="license_expiry" type="date" class="vehicle-form-control" value="<?= htmlspecialchars($driverFormData['license_expiry'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Department</span>
                        <input name="department" type="text" class="vehicle-form-control" placeholder="Transport" value="<?= htmlspecialchars($driverFormData['department'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block md:col-span-2">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Assigned Vehicle</span>
                        <select name="assigned_vehicle" class="vehicle-form-control" data-driver-vehicle-select>
                            <option value="unassigned" <?= (($driverFormData['assigned_vehicle'] ?? 'unassigned') === 'unassigned') ? 'selected' : ''; ?>>Unassigned</option>
                            <?php foreach ($driverVehicleOptions as $vehicleOption): ?>
                                <?php
                                $assignedDriverId = $vehicleOption['assigned_driver_id'] !== null ? (int) $vehicleOption['assigned_driver_id'] : null;
                                $assignedLabel = $assignedDriverId !== null && !empty($vehicleOption['assigned_driver_name'])
                                    ? ' - Assigned to ' . $vehicleOption['assigned_driver_name']
                                    : '';
                                ?>
                                <option
                                    value="<?= htmlspecialchars((string) $vehicleOption['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-assigned-driver-id="<?= htmlspecialchars((string) ($assignedDriverId ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                    <?= (($driverFormData['assigned_vehicle'] ?? '') === (string) $vehicleOption['id']) ? 'selected' : ''; ?>
                                >
                                    <?= htmlspecialchars($vehicleOption['registration_no'] . $assignedLabel, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="mt-2 block text-xs text-fleet-muted">Only vehicles available in the database can be assigned.</span>
                    </label>

                    <label class="block md:col-span-1">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Status</span>
                        <select name="status" class="vehicle-form-control">
                            <option value="active" <?= (($driverFormData['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?= (($driverFormData['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="suspended" <?= (($driverFormData['status'] ?? '') === 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" data-close-driver-modal class="h-10 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">Cancel</button>
                    <button type="submit" class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active" data-driver-submit-button><?= $driverFormMode === 'update' ? 'Save Changes' : 'Add Driver'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <div id="driver-delete-modal" class="logbook-delete-overlay" aria-hidden="true">
        <div class="logbook-delete-card" role="dialog" aria-modal="true" aria-labelledby="driver-delete-modal-title">
            <div class="logbook-delete-header">
                <div class="flex items-center gap-4">
                    <div class="logbook-delete-icon">!</div>
                    <div>
                        <p class="logbook-delete-eyebrow">Delete Confirmation</p>
                        <h2 id="driver-delete-modal-title" class="logbook-delete-title">Remove driver?</h2>
                    </div>
                </div>
            </div>
            <div class="logbook-delete-body">
                <!-- This custom confirmation modal replaces the browser popup for a cleaner delete flow. -->
                <p class="logbook-delete-copy">
                    This driver record will be removed from the system. This action cannot be undone.
                </p>
                <div class="logbook-delete-actions">
                    <button type="button" data-cancel-driver-delete class="logbook-delete-button logbook-delete-button-secondary">
                        Keep Driver
                    </button>
                    <button type="button" data-confirm-driver-delete class="logbook-delete-button logbook-delete-button-danger">
                        Delete Driver
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
