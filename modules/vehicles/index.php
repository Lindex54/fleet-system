<?php
// Vehicle registry page backed by the vehicle handler and database.
$activePage = 'vehicles';
require_once __DIR__ . '/../../handlers/vehicle.php';
// Pull both the current vehicle rows and any flash UI state from the handler.
extract(vehicleFetchPageData());

$vehicleFilterDepartments = [];
$vehicleFilterModels = [];
$vehicleFilterRegistrations = [];
$vehicleFilterTypes = [];
$vehicleFilterStatuses = [];

foreach ($vehicles as $vehicle) {
    $departmentValue = trim((string) ($vehicle['department'] ?? ''));
    $modelValue = trim((string) ($vehicle['model'] ?? ''));
    $typeValue = trim((string) ($vehicle['type'] ?? ''));
    $statusValue = trim((string) ($vehicle['status'] ?? ''));
    $registrationValue = trim((string) ($vehicle['reg'] ?? ''));

    if ($registrationValue !== '' && $registrationValue !== '-') {
        $vehicleFilterRegistrations[strtolower($registrationValue)] = $registrationValue;
    }

    if ($departmentValue !== '' && $departmentValue !== '-') {
        $vehicleFilterDepartments[strtolower($departmentValue)] = $departmentValue;
    }

    if ($modelValue !== '' && $modelValue !== '-') {
        $vehicleFilterModels[strtolower($modelValue)] = $modelValue;
    }

    if ($typeValue !== '' && $typeValue !== '-') {
        $vehicleFilterTypes[strtolower($typeValue)] = $typeValue;
    }

    if ($statusValue !== '' && $statusValue !== '-') {
        $vehicleFilterStatuses[strtolower($statusValue)] = $statusValue;
    }
}

natcasesort($vehicleFilterDepartments);
natcasesort($vehicleFilterModels);
natcasesort($vehicleFilterRegistrations);
natcasesort($vehicleFilterTypes);
natcasesort($vehicleFilterStatuses);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Fleet Vehicles</h1>
                <p class="mt-2 text-sm text-fleet-muted">University motor vehicle fleet registry</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" data-print-vehicles class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-fleet-card hover:bg-fleet-surface-muted">
                    <span class="text-base">P</span>
                    <span>Print</span>
                </button>
                <button type="button" data-open-vehicle-modal class="inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>Add Vehicle</span>
                </button>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>
        </div>

        <?php if (!empty($vehicleNotification)): ?>
            <?php $isSuccessNotice = ($vehicleNotification['type'] ?? '') === 'success'; ?>
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
                                    <?= htmlspecialchars($vehicleNotification['title'] ?? 'Vehicle update', ENT_QUOTES, 'UTF-8'); ?>
                                </h2>
                                <p class="mt-1 text-sm leading-6 text-fleet-ink">
                                    <?= htmlspecialchars($vehicleNotification['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
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

        <section class="mb-6 rounded-2xl border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Registration Number</span>
                    <select id="vehicle-filter-registration" class="vehicle-form-control">
                        <option value="">All registration numbers</option>
                        <?php foreach ($vehicleFilterRegistrations as $vehicleFilterRegistration): ?>
                            <option value="<?= htmlspecialchars($vehicleFilterRegistration, ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($vehicleFilterRegistration, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Model</span>
                    <select id="vehicle-filter-model" class="vehicle-form-control">
                        <option value="">All models</option>
                        <?php foreach ($vehicleFilterModels as $vehicleFilterModel): ?>
                            <option value="<?= htmlspecialchars($vehicleFilterModel, ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($vehicleFilterModel, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Department</span>
                    <select id="vehicle-filter-department" class="vehicle-form-control">
                        <option value="">All departments</option>
                        <?php foreach ($vehicleFilterDepartments as $vehicleFilterDepartment): ?>
                            <option value="<?= htmlspecialchars($vehicleFilterDepartment, ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($vehicleFilterDepartment, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Vehicle Type</span>
                    <select id="vehicle-filter-type" class="vehicle-form-control">
                        <option value="">All types</option>
                        <?php foreach ($vehicleFilterTypes as $vehicleFilterType): ?>
                            <option value="<?= htmlspecialchars($vehicleFilterType, ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($vehicleFilterType, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Status</span>
                    <select id="vehicle-filter-status" class="vehicle-form-control">
                        <option value="">All statuses</option>
                        <?php foreach ($vehicleFilterStatuses as $vehicleFilterStatus): ?>
                            <option value="<?= htmlspecialchars($vehicleFilterStatus, ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($vehicleFilterStatus, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Print Category</span>
                    <select id="vehicle-print-group" class="vehicle-form-control">
                        <option value="department">Group by department</option>
                        <option value="model">Group by model</option>
                        <option value="vehicleType">Group by vehicle type</option>
                        <option value="statusLabel">Group by status</option>
                    </select>
                </label>

                <div class="flex items-end gap-3 md:col-span-2">
                    <button type="button" id="vehicle-filter-apply" class="inline-flex h-10 items-center rounded-lg bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">
                        Apply Filters
                    </button>
                    <button type="button" id="vehicle-filter-reset" class="inline-flex h-10 items-center rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">
                        Reset Filters
                    </button>
                    <p class="text-sm text-fleet-muted" data-vehicle-filter-summary>
                        Showing all vehicles.
                    </p>
                </div>
            </div>
        </section>

        <section class="<?= $hasVehicles ? 'hidden' : 'flex'; ?> min-h-[420px] items-center justify-center">
            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-200 text-fleet-muted">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9L18.7 8c-.4-.6-1-.9-1.7-.9H7c-.7 0-1.3.3-1.7.9l-1.8 3.1C2.7 11.3 2 12.1 2 13v3c0 .6.4 1 1 1h2"></path>
                        <circle cx="7" cy="17" r="2"></circle>
                        <circle cx="17" cy="17" r="2"></circle>
                        <path d="M5 11h14"></path>
                    </svg>
                </div>
                <h2 class="mt-5 text-lg font-extrabold text-fleet-ink">No vehicles found</h2>
                <p class="mt-2 text-sm text-fleet-muted">Add vehicles to build the university fleet registry.</p>
                <button type="button" data-open-vehicle-modal class="mt-6 inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>Add Vehicle</span>
                </button>
            </div>
        </section>

        <section data-print-root class="<?= $hasVehicles ? 'block' : 'hidden'; ?> overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="border-b border-fleet-line-soft px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-extrabold text-fleet-ink">Vehicle Register</h2>
                        <p class="mt-1 text-sm text-fleet-muted">Search and review the filtered vehicle list below.</p>
                    </div>
                    <label class="relative block w-full sm:max-w-md">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">Q</span>
                        <input id="vehicle-search" type="search" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-11 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search vehicles...">
                    </label>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1180px] text-left text-sm" data-vehicle-table>
                    <thead class="bg-fleet-surface-muted text-fleet-muted">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Reg. No.</th>
                            <th class="px-5 py-4 font-semibold">Make / Model</th>
                            <th class="px-5 py-4 font-semibold">Year</th>
                            <th class="px-5 py-4 font-semibold">Type</th>
                            <th class="px-5 py-4 font-semibold">Department</th>
                            <th class="px-5 py-4 font-semibold">Mileage (km)</th>
                            <th class="px-5 py-4 font-semibold">Insurance Expiry</th>
                            <th class="px-5 py-4 font-semibold">Repairs Done</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-fleet-line-soft">
                        <?php foreach ($vehicles as $vehicle): ?>
                            <tr
                                class="vehicle-row hover:bg-fleet-surface-muted/70"
                                data-search="<?= htmlspecialchars(strtolower(implode(' ', $vehicle)), ENT_QUOTES, 'UTF-8'); ?>"
                                data-vehicle-id="<?= htmlspecialchars((string) $vehicle['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-registration-number="<?= htmlspecialchars($vehicle['reg'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-make="<?= htmlspecialchars($vehicle['make'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-model="<?= htmlspecialchars($vehicle['model'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-year="<?= htmlspecialchars($vehicle['year'] === '-' ? '' : (string) $vehicle['year'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-vehicle-type="<?= htmlspecialchars($vehicle['type_value'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-fuel-type="<?= htmlspecialchars($vehicle['fuel_type'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-department="<?= htmlspecialchars($vehicle['department'] === '-' ? '' : $vehicle['department'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-current-mileage="<?= htmlspecialchars($vehicle['mileage'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-insurance-expiry="<?= htmlspecialchars($vehicle['insurance_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-status="<?= htmlspecialchars($vehicle['status_value'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-status-label="<?= htmlspecialchars($vehicle['status'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-repairs-done="<?= htmlspecialchars($vehicle['repairs_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-vehicle-image="<?= htmlspecialchars($vehicle['vehicle_image'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-vehicle-image-url="<?= htmlspecialchars($vehicle['vehicle_image_url'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-vehicle-image-name="<?= htmlspecialchars($vehicle['vehicle_image_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-vehicle-image-is-image="<?= $vehicle['vehicle_image_is_image'] ? 'true' : 'false'; ?>"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <?php if ($vehicle['vehicle_image_url'] !== '' && $vehicle['vehicle_image_is_image']): ?>
                                            <img src="<?= htmlspecialchars($vehicle['vehicle_image_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($vehicle['reg'], ENT_QUOTES, 'UTF-8'); ?>" class="h-10 w-10 rounded-full object-cover ring-2 ring-fleet-primary-soft">
                                        <?php else: ?>
                                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-fleet-primary-soft text-sm font-extrabold text-fleet-primary">
                                                <?= htmlspecialchars(strtoupper(substr($vehicle['make'] !== '' ? $vehicle['make'] : $vehicle['reg'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        <?php endif; ?>
                                        <div>
                                            <p class="font-extrabold text-fleet-ink"><?= htmlspecialchars($vehicle['reg'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="text-xs text-fleet-muted"><?= htmlspecialchars(trim($vehicle['make'] . ' ' . $vehicle['model']), ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-fleet-ink">
                                    <span class="block"><?= htmlspecialchars($vehicle['make'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="mt-2 block"><?= htmlspecialchars($vehicle['model'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($vehicle['year'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4">
                                    <span class="rounded-lg bg-slate-200 px-3 py-1 text-xs font-medium text-slate-600"><?= htmlspecialchars($vehicle['type'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($vehicle['department'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($vehicle['mileage'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 text-fleet-muted"><?= htmlspecialchars($vehicle['insurance'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="max-w-[18rem] px-5 py-4 text-fleet-muted"><?= htmlspecialchars($vehicle['repairs'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4">
                                    <?php if ($vehicle['status'] === 'Active'): ?>
                                        <span class="rounded-lg border border-green-200 bg-fleet-success-soft px-3 py-1 text-xs font-semibold text-fleet-success">Active</span>
                                    <?php elseif ($vehicle['status'] === 'Grounded'): ?>
                                        <span class="rounded-lg border border-red-200 bg-red-50 px-3 py-1 text-xs font-semibold text-red-600">Grounded</span>
                                    <?php elseif ($vehicle['status'] === 'Disposed'): ?>
                                        <span class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Disposed</span>
                                    <?php else: ?>
                                        <span class="rounded-lg border border-orange-200 bg-fleet-warning-soft px-3 py-1 text-xs font-semibold text-fleet-warning-strong">Maintenance</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-3">
                                        <button type="button" data-open-vehicle-view class="text-fleet-ink hover:text-fleet-primary" aria-label="View <?= htmlspecialchars($vehicle['reg'], ENT_QUOTES, 'UTF-8'); ?>">View</button>
                                        <button type="button" data-open-vehicle-edit class="text-fleet-sidebar hover:text-fleet-primary" aria-label="Edit <?= htmlspecialchars($vehicle['reg'], ENT_QUOTES, 'UTF-8'); ?>">Edit</button>
                                        <form
                                            action="<?= htmlspecialchars($vehicleFormAction, ENT_QUOTES, 'UTF-8'); ?>"
                                            method="post"
                                            data-delete-name="<?= htmlspecialchars($vehicle['reg'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-delete-detail="<?= htmlspecialchars(trim($vehicle['make'] . ' ' . $vehicle['model'] . ' - ' . $vehicle['department']), ENT_QUOTES, 'UTF-8'); ?>"
                                        >
                                            <input type="hidden" name="vehicle_action" value="delete">
                                            <input type="hidden" name="vehicle_id" value="<?= htmlspecialchars((string) $vehicle['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" data-open-vehicle-delete class="text-fleet-danger hover:text-fleet-danger-strong" aria-label="Delete <?= htmlspecialchars($vehicle['reg'], ENT_QUOTES, 'UTF-8'); ?>">Del</button>
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
        id="vehicle-modal"
        class="fixed inset-0 z-50 <?= $shouldOpenVehicleModal ? 'flex' : 'hidden'; ?> items-center justify-center bg-black/75 px-4 py-6"
        aria-hidden="<?= $shouldOpenVehicleModal ? 'false' : 'true'; ?>"
        data-open-on-load="<?= $shouldOpenVehicleModal ? 'true' : 'false'; ?>"
    >
        <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full max-w-2xl overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="vehicle-modal-title">
            <!-- Failed submissions reopen this modal and refill the fields from flash form data. jQuery adds safe frontend validation and AJAX support here. -->
            <?php $vehicleImagePath = $vehicleFormData['vehicle_image'] ?? ''; ?>
            <form class="p-6" action="<?= htmlspecialchars($vehicleFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="multipart/form-data" data-fleet-ajax="true" data-vehicle-form>
                <input type="hidden" name="vehicle_action" value="<?= $vehicleFormMode === 'update' ? 'update' : 'create'; ?>" data-vehicle-action-field>
                <input type="hidden" name="vehicle_id" value="<?= htmlspecialchars($vehicleFormData['vehicle_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-vehicle-id-field>
                <input type="hidden" name="existing_vehicle_image" value="<?= htmlspecialchars($vehicleImagePath, ENT_QUOTES, 'UTF-8'); ?>" data-vehicle-image-path-field>
                <div class="mb-5 flex items-center justify-between gap-4">
                    <h2 id="vehicle-modal-title" class="text-xl font-extrabold text-fleet-ink" data-vehicle-modal-title><?= $vehicleFormMode === 'update' ? 'Edit Vehicle' : 'Add New Vehicle'; ?></h2>
                    <button type="button" data-close-vehicle-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close add vehicle form">&times;</button>
                </div>
                <div data-fleet-feedback-host></div>

                <div class="grid gap-5 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Registration Number *</span>
                        <input name="registration_number" type="text" required autofocus class="vehicle-form-control" placeholder="e.g. UAX 123A" value="<?= htmlspecialchars($vehicleFormData['registration_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Make *</span>
                        <input name="make" type="text" required class="vehicle-form-control" placeholder="e.g. Toyota" value="<?= htmlspecialchars($vehicleFormData['make'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Model *</span>
                        <input name="model" type="text" required class="vehicle-form-control" placeholder="e.g. Land Cruiser" value="<?= htmlspecialchars($vehicleFormData['model'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Year</span>
                        <input name="year" type="number" min="1980" max="2035" class="vehicle-form-control" placeholder="e.g. 2020" value="<?= htmlspecialchars($vehicleFormData['year'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Vehicle Type</span>
                        <select name="vehicle_type" class="vehicle-form-control">
                            <option value="sedan" <?= (($vehicleFormData['vehicle_type'] ?? 'sedan') === 'sedan') ? 'selected' : ''; ?>>sedan</option>
                            <option value="suv" <?= (($vehicleFormData['vehicle_type'] ?? '') === 'suv') ? 'selected' : ''; ?>>suv</option>
                            <option value="pickup" <?= (($vehicleFormData['vehicle_type'] ?? '') === 'pickup') ? 'selected' : ''; ?>>pickup</option>
                            <option value="truck" <?= (($vehicleFormData['vehicle_type'] ?? '') === 'truck') ? 'selected' : ''; ?>>truck</option>
                            <option value="van" <?= (($vehicleFormData['vehicle_type'] ?? '') === 'van') ? 'selected' : ''; ?>>van</option>
                            <option value="bus" <?= (($vehicleFormData['vehicle_type'] ?? '') === 'bus') ? 'selected' : ''; ?>>bus</option>
                            <option value="motorcycle" <?= (($vehicleFormData['vehicle_type'] ?? '') === 'motorcycle') ? 'selected' : ''; ?>>motorcycle</option>
                            <option value="other" <?= (($vehicleFormData['vehicle_type'] ?? '') === 'other') ? 'selected' : ''; ?>>other</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Fuel Type</span>
                        <select name="fuel_type" class="vehicle-form-control">
                            <option value="diesel" <?= (($vehicleFormData['fuel_type'] ?? 'diesel') === 'diesel') ? 'selected' : ''; ?>>diesel</option>
                            <option value="petrol" <?= (($vehicleFormData['fuel_type'] ?? '') === 'petrol') ? 'selected' : ''; ?>>petrol</option>
                            <option value="hybrid" <?= (($vehicleFormData['fuel_type'] ?? '') === 'hybrid') ? 'selected' : ''; ?>>hybrid</option>
                            <option value="electric" <?= (($vehicleFormData['fuel_type'] ?? '') === 'electric') ? 'selected' : ''; ?>>electric</option>
                            <option value="other" <?= (($vehicleFormData['fuel_type'] ?? '') === 'other') ? 'selected' : ''; ?>>other</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Department</span>
                        <input name="department" type="text" class="vehicle-form-control" placeholder="e.g. Transport" value="<?= htmlspecialchars($vehicleFormData['department'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Current Mileage (km)</span>
                        <input name="current_mileage" type="number" min="0" class="vehicle-form-control" value="<?= htmlspecialchars($vehicleFormData['current_mileage'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Insurance Expiry Date</span>
                        <input name="insurance_expiry" type="date" class="vehicle-form-control" value="<?= htmlspecialchars($vehicleFormData['insurance_expiry'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block md:col-span-2">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Vehicle Image</span>
                        <input name="vehicle_image" type="file" accept=".jpg,.jpeg,.png,.webp" class="vehicle-form-control file:mr-3 file:rounded-lg file:border-0 file:bg-fleet-primary-soft file:px-3 file:py-2 file:text-sm file:font-semibold file:text-fleet-primary">
                        <p class="mt-2 text-xs text-fleet-muted">Upload a clear vehicle photo in JPG, PNG, or WEBP format.</p>
                        <div class="<?= $vehicleImagePath !== '' ? 'block' : 'hidden'; ?> mt-3 rounded-lg border border-fleet-line bg-fleet-surface-muted p-3" data-vehicle-image-preview>
                            <div class="flex items-start gap-3">
                                <img
                                    src="<?= htmlspecialchars(vehicleBuildUploadUrl($vehicleImagePath), ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="Vehicle image"
                                    class="<?= vehicleUploadIsImage($vehicleImagePath) ? 'block' : 'hidden'; ?> h-20 w-20 rounded-xl object-cover ring-2 ring-fleet-primary-soft"
                                    data-vehicle-image-preview-tag
                                >
                                <div class="min-w-0">
                                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Current Image</p>
                                    <a href="<?= htmlspecialchars(vehicleBuildUploadUrl($vehicleImagePath), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="mt-1 inline-flex text-sm font-semibold text-fleet-primary hover:underline" data-vehicle-image-link>
                                        <?= htmlspecialchars(vehicleUploadDisplayName($vehicleImagePath), ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </label>

                    <label class="block md:col-span-1">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Status</span>
                        <select name="status" class="vehicle-form-control">
                            <option value="active" <?= (($vehicleFormData['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="maintenance" <?= (($vehicleFormData['status'] ?? '') === 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="grounded" <?= (($vehicleFormData['status'] ?? '') === 'grounded') ? 'selected' : ''; ?>>Grounded</option>
                            <option value="disposed" <?= (($vehicleFormData['status'] ?? '') === 'disposed') ? 'selected' : ''; ?>>Disposed</option>
                        </select>
                    </label>

                    <label class="<?= $vehicleFormMode === 'update' ? 'block' : 'hidden'; ?> md:col-span-2" data-vehicle-repairs-field>
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Repairs Done</span>
                        <textarea name="repairs_done" class="vehicle-form-control min-h-24 resize-y py-3" placeholder="Summarize the repair work completed on this vehicle."><?= htmlspecialchars($vehicleFormData['repairs_done'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" data-close-vehicle-modal class="h-10 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">Cancel</button>
                    <button type="submit" class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active" data-vehicle-submit-button data-loading-text="Saving Vehicle..."><?= $vehicleFormMode === 'update' ? 'Save Changes' : 'Add Vehicle'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/view-modal.php'; ?>

    <div id="vehicle-delete-modal" class="logbook-delete-overlay" aria-hidden="true">
        <div class="logbook-delete-card" role="dialog" aria-modal="true" aria-labelledby="vehicle-delete-modal-title">
            <div class="logbook-delete-header">
                <div class="flex items-center gap-4">
                    <div class="logbook-delete-icon">!</div>
                    <div>
                        <p class="logbook-delete-eyebrow">Delete Confirmation</p>
                        <h2 id="vehicle-delete-modal-title" class="logbook-delete-title">Remove vehicle?</h2>
                    </div>
                </div>
            </div>
            <div class="logbook-delete-body">
                <p class="logbook-delete-copy">
                    You are about to permanently remove this vehicle from the fleet register.
                </p>
                <div class="mt-4 rounded-lg border border-fleet-line bg-fleet-surface-muted px-4 py-3">
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Selected Vehicle</p>
                    <p class="mt-1 text-base font-extrabold text-fleet-ink" data-vehicle-delete-name>This vehicle</p>
                    <p class="mt-1 text-sm text-fleet-muted" data-vehicle-delete-detail>Registration and basic details will appear here.</p>
                </div>
                <p class="mt-4 text-sm text-fleet-muted">This action cannot be undone.</p>
                <div class="logbook-delete-actions">
                    <button type="button" data-cancel-vehicle-delete class="logbook-delete-button logbook-delete-button-secondary">
                        Keep Vehicle
                    </button>
                    <button type="button" data-confirm-vehicle-delete class="logbook-delete-button logbook-delete-button-danger">
                        Delete Vehicle
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
