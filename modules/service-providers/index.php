<?php
// Service providers page backed by the provider handler and database.
$activePage = 'providers';
require_once __DIR__ . '/../../handlers/provider.php';
// Load live provider cards and any flash UI state from the handler.
extract(providerFetchPageData());
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Service Providers</h1>
                <p class="mt-2 text-sm text-fleet-muted">Garages, mechanics, and service partners</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" data-open-provider-modal class="inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>Add Provider</span>
                </button>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>
        </div>

        <?php if (!empty($providerNotification)): ?>
            <?php $isSuccessNotice = ($providerNotification['type'] ?? '') === 'success'; ?>
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
                                    <?= htmlspecialchars($providerNotification['title'] ?? 'Provider update', ENT_QUOTES, 'UTF-8'); ?>
                                </h2>
                                <p class="mt-1 text-sm leading-6 text-fleet-ink">
                                    <?= htmlspecialchars($providerNotification['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
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

        <section class="<?= $hasProviders ? 'hidden' : 'flex'; ?> min-h-[360px] items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-200 text-fleet-muted">
                    <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 21h18"></path>
                        <path d="M5 21V7l8-4v18"></path>
                        <path d="M19 21V11l-6-4"></path>
                        <path d="M9 9h1"></path>
                        <path d="M9 13h1"></path>
                        <path d="M9 17h1"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-xl font-extrabold text-fleet-ink">No service providers</h2>
                <p class="mt-2 text-base text-fleet-muted">Add service providers and garages.</p>
                <button type="button" data-open-provider-modal class="mt-7 inline-flex h-11 items-center gap-2 rounded-lg bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>Add Provider</span>
                </button>
            </div>
        </section>

        <section class="<?= $hasProviders ? 'grid' : 'hidden'; ?> gap-4 md:grid-cols-2 xl:grid-cols-3" data-provider-list>
            <div class="mb-8 max-w-md md:col-span-2 xl:col-span-3">
                <label class="relative block">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">Q</span>
                    <input id="provider-search" type="search" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-11 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search by name, town or contact...">
                </label>
            </div>

            <?php foreach ($providers as $provider): ?>
                <article
                    class="provider-card interactive-card rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card"
                    data-search="<?= htmlspecialchars(strtolower(implode(' ', [$provider['name'], $provider['town'], $provider['contact_person'], $provider['contact'], $provider['email'], $provider['specialty'], $provider['status']])), ENT_QUOTES, 'UTF-8'); ?>"
                    data-provider-id="<?= htmlspecialchars((string) $provider['id'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-name="<?= htmlspecialchars($provider['name_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-town="<?= htmlspecialchars($provider['town_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-contact-person="<?= htmlspecialchars($provider['contact_person_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-phone="<?= htmlspecialchars($provider['phone_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-email="<?= htmlspecialchars($provider['email_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-specialty="<?= htmlspecialchars($provider['specialty_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-status="<?= htmlspecialchars($provider['status_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-extrabold text-fleet-ink"><?= htmlspecialchars($provider['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            <p class="mt-1 text-sm text-fleet-muted"><?= htmlspecialchars($provider['specialty'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <?php if ($provider['status'] === 'Active'): ?>
                            <span class="rounded-lg border border-green-200 bg-fleet-success-soft px-3 py-1 text-xs font-semibold text-fleet-success">Active</span>
                        <?php elseif ($provider['status'] === 'Pending'): ?>
                            <span class="rounded-lg border border-orange-200 bg-fleet-warning-soft px-3 py-1 text-xs font-semibold text-fleet-warning-strong">Pending</span>
                        <?php else: ?>
                            <span class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Inactive</span>
                        <?php endif; ?>
                    </div>

                    <div class="mt-5 space-y-3 text-sm">
                        <p class="flex justify-between gap-4"><span class="text-fleet-muted">Town</span><span class="font-semibold text-fleet-ink"><?= htmlspecialchars($provider['town'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                        <p class="flex justify-between gap-4"><span class="text-fleet-muted">Contact Person</span><span class="font-semibold text-fleet-ink"><?= htmlspecialchars($provider['contact_person'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                        <p class="flex justify-between gap-4"><span class="text-fleet-muted">Phone</span><span class="font-semibold text-fleet-ink"><?= htmlspecialchars($provider['contact'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                        <p class="flex justify-between gap-4"><span class="text-fleet-muted">Email</span><span class="font-semibold text-fleet-ink"><?= htmlspecialchars($provider['email'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                    </div>

                    <div class="mt-5 flex justify-end gap-3 border-t border-fleet-line-soft pt-4">
                        <button type="button" data-edit-provider-entry class="text-sm font-semibold text-fleet-sidebar hover:text-fleet-primary">Edit</button>
                        <form action="<?= htmlspecialchars($providerFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post">
                            <!-- Delete uses a dedicated POST form so the action stays explicit and safe. -->
                            <input type="hidden" name="provider_action" value="delete">
                            <input type="hidden" name="provider_id" value="<?= htmlspecialchars((string) $provider['id'], ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" data-open-provider-delete class="text-sm font-semibold text-fleet-danger hover:text-fleet-danger-strong">Delete</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </div>

    <div
        id="provider-modal"
        class="fixed inset-0 z-50 <?= $shouldOpenProviderModal ? 'flex' : 'hidden'; ?> items-center justify-center bg-black/75 px-4 py-6"
        aria-hidden="<?= $shouldOpenProviderModal ? 'false' : 'true'; ?>"
        data-open-on-load="<?= $shouldOpenProviderModal ? 'true' : 'false'; ?>"
    >
        <div class="dashboard-scroll max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl">
            <!-- Failed submissions reopen this modal and keep entered values in place. -->
            <form class="p-7" action="<?= htmlspecialchars($providerFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" data-provider-form>
                <input type="hidden" name="provider_action" value="<?= $providerFormMode === 'update' ? 'update' : 'create'; ?>" data-provider-action-field>
                <input type="hidden" name="provider_id" value="<?= htmlspecialchars($providerFormData['provider_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-provider-id-field>

                <div class="mb-7 flex items-start justify-between gap-4">
                    <h2 class="text-2xl font-extrabold text-fleet-ink" data-provider-modal-title><?= $providerFormMode === 'update' ? 'Edit Service Provider' : 'Add Service Provider'; ?></h2>
                    <button type="button" data-close-provider-modal class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted transition hover:bg-slate-100 hover:text-fleet-ink" aria-label="Close provider form">&times;</button>
                </div>

                <div class="space-y-6">
                    <div>
                        <label for="provider-name" class="mb-2 block text-sm font-semibold text-fleet-ink">Provider Name *</label>
                        <input id="provider-name" name="name" type="text" class="vehicle-form-control" placeholder="Garage/Workshop name" required autofocus value="<?= htmlspecialchars($providerFormData['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="provider-contact-person" class="mb-2 block text-sm font-semibold text-fleet-ink">Contact Person</label>
                            <input id="provider-contact-person" name="contact_person" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($providerFormData['contact_person'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div>
                            <label for="provider-phone" class="mb-2 block text-sm font-semibold text-fleet-ink">Phone</label>
                            <input id="provider-phone" name="phone" type="tel" class="vehicle-form-control" placeholder="+256 700 000000" value="<?= htmlspecialchars($providerFormData['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="provider-email" class="mb-2 block text-sm font-semibold text-fleet-ink">Email</label>
                            <input id="provider-email" name="email" type="email" class="vehicle-form-control" value="<?= htmlspecialchars($providerFormData['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div>
                            <label for="provider-town" class="mb-2 block text-sm font-semibold text-fleet-ink">Town / City</label>
                            <input id="provider-town" name="town" type="text" class="vehicle-form-control" placeholder="e.g. Tororo, Kampala" value="<?= htmlspecialchars($providerFormData['town'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="provider-specialization" class="mb-2 block text-sm font-semibold text-fleet-ink">Specialization</label>
                            <input id="provider-specialization" name="specialty" type="text" class="vehicle-form-control" placeholder="e.g. Toyota service, body works" value="<?= htmlspecialchars($providerFormData['specialty'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div>
                            <label for="provider-status" class="mb-2 block text-sm font-semibold text-fleet-ink">Status</label>
                            <select id="provider-status" name="status" class="vehicle-form-control">
                                <option value="active" <?= (($providerFormData['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="pending" <?= (($providerFormData['status'] ?? '') === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="inactive" <?= (($providerFormData['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" data-close-provider-modal class="inline-flex h-11 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface px-5 text-sm font-semibold text-fleet-ink shadow-sm transition hover:bg-slate-50">Cancel</button>
                    <button type="submit" data-provider-submit-button class="inline-flex h-11 items-center justify-center rounded-lg bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-fleet-card transition hover:bg-fleet-sidebar-active"><?= $providerFormMode === 'update' ? 'Save Changes' : 'Add Provider'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <div id="provider-delete-modal" class="logbook-delete-overlay" aria-hidden="true">
        <div class="logbook-delete-card" role="dialog" aria-modal="true" aria-labelledby="provider-delete-modal-title">
            <div class="logbook-delete-header">
                <div class="flex items-center gap-4">
                    <div class="logbook-delete-icon">!</div>
                    <div>
                        <p class="logbook-delete-eyebrow">Delete Confirmation</p>
                        <h2 id="provider-delete-modal-title" class="logbook-delete-title">Remove service provider?</h2>
                    </div>
                </div>
            </div>
            <div class="logbook-delete-body">
                <!-- This custom confirmation modal replaces the browser popup for a cleaner delete flow. -->
                <p class="logbook-delete-copy">
                    This service provider will be removed from the system. This action cannot be undone.
                </p>
                <div class="logbook-delete-actions">
                    <button type="button" data-cancel-provider-delete class="logbook-delete-button logbook-delete-button-secondary">
                        Keep Provider
                    </button>
                    <button type="button" data-confirm-provider-delete class="logbook-delete-button logbook-delete-button-danger">
                        Delete Provider
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
