<?php
// Static frontend page for garages, mechanics, and service partners.
$activePage = 'providers';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

$providers = [
    ['name' => 'Tororo Auto Garage', 'town' => 'Tororo', 'contact' => '+256 701 220 110', 'email' => 'service@tororoauto.ug', 'specialty' => 'General repairs', 'status' => 'Active'],
    ['name' => 'Toyota Uganda Service Centre', 'town' => 'Kampala', 'contact' => '+256 414 339 000', 'email' => 'fleetservice@toyota.co.ug', 'specialty' => 'Toyota service', 'status' => 'Active'],
    ['name' => 'Mbale Fleet Mechanics', 'town' => 'Mbale', 'contact' => '+256 772 431 980', 'email' => 'info@mbalefleet.ug', 'specialty' => 'Brakes and suspension', 'status' => 'Pending'],
];

$hasProviders = count($providers) > 0;
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

        <div class="mb-8 max-w-md">
            <label class="relative block">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">Q</span>
                <input id="provider-search" type="search" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-11 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search by name, town or contact...">
            </label>
        </div>

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
            <?php foreach ($providers as $provider): ?>
                <article class="provider-card interactive-card rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card" data-search="<?= htmlspecialchars(strtolower(implode(' ', $provider)), ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-extrabold text-fleet-ink"><?= htmlspecialchars($provider['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            <p class="mt-1 text-sm text-fleet-muted"><?= htmlspecialchars($provider['specialty'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <?php if ($provider['status'] === 'Active'): ?>
                            <span class="rounded-lg border border-green-200 bg-fleet-success-soft px-3 py-1 text-xs font-semibold text-fleet-success">Active</span>
                        <?php else: ?>
                            <span class="rounded-lg border border-orange-200 bg-fleet-warning-soft px-3 py-1 text-xs font-semibold text-fleet-warning-strong">Pending</span>
                        <?php endif; ?>
                    </div>

                    <div class="mt-5 space-y-3 text-sm">
                        <p class="flex justify-between gap-4"><span class="text-fleet-muted">Town</span><span class="font-semibold text-fleet-ink"><?= htmlspecialchars($provider['town'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                        <p class="flex justify-between gap-4"><span class="text-fleet-muted">Contact</span><span class="font-semibold text-fleet-ink"><?= htmlspecialchars($provider['contact'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                        <p class="flex justify-between gap-4"><span class="text-fleet-muted">Email</span><span class="font-semibold text-fleet-ink"><?= htmlspecialchars($provider['email'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                    </div>

                    <div class="mt-5 flex justify-end gap-3 border-t border-fleet-line-soft pt-4">
                        <button type="button" class="text-sm font-semibold text-fleet-sidebar hover:text-fleet-primary">Edit</button>
                        <button type="button" class="text-sm font-semibold text-fleet-danger hover:text-fleet-danger-strong">Delete</button>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </div>

    <div id="provider-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
        <div class="dashboard-scroll max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl">
            <form class="p-7">
                <div class="mb-7 flex items-start justify-between gap-4">
                    <h2 class="text-2xl font-extrabold text-fleet-ink">Add Service Provider</h2>
                    <button type="button" data-close-provider-modal class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted transition hover:bg-slate-100 hover:text-fleet-ink" aria-label="Close provider form">&times;</button>
                </div>

                <div class="space-y-6">
                    <div>
                        <label for="provider-name" class="mb-2 block text-sm font-semibold text-fleet-ink">Provider Name *</label>
                        <input id="provider-name" type="text" class="vehicle-form-control" placeholder="Garage/Workshop name">
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="provider-contact-person" class="mb-2 block text-sm font-semibold text-fleet-ink">Contact Person</label>
                            <input id="provider-contact-person" type="text" class="vehicle-form-control">
                        </div>
                        <div>
                            <label for="provider-phone" class="mb-2 block text-sm font-semibold text-fleet-ink">Phone *</label>
                            <input id="provider-phone" type="tel" class="vehicle-form-control" placeholder="+256 700 000000">
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="provider-email" class="mb-2 block text-sm font-semibold text-fleet-ink">Email</label>
                            <input id="provider-email" type="email" class="vehicle-form-control">
                        </div>
                        <div>
                            <label for="provider-tin" class="mb-2 block text-sm font-semibold text-fleet-ink">TIN Number</label>
                            <input id="provider-tin" type="text" class="vehicle-form-control">
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="provider-town" class="mb-2 block text-sm font-semibold text-fleet-ink">Town / City</label>
                            <input id="provider-town" type="text" class="vehicle-form-control" placeholder="e.g. Tororo, Kampala">
                        </div>
                        <div>
                            <label for="provider-address" class="mb-2 block text-sm font-semibold text-fleet-ink">Address</label>
                            <input id="provider-address" type="text" class="vehicle-form-control" placeholder="Street / Plot address">
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="provider-specialization" class="mb-2 block text-sm font-semibold text-fleet-ink">Specialization</label>
                            <select id="provider-specialization" class="vehicle-form-control">
                                <option>general mechanics</option>
                                <option>body works</option>
                                <option>electrical repairs</option>
                                <option>tyres and wheels</option>
                                <option>Toyota service</option>
                            </select>
                        </div>
                        <div>
                            <label for="provider-status" class="mb-2 block text-sm font-semibold text-fleet-ink">Status</label>
                            <select id="provider-status" class="vehicle-form-control">
                                <option>Active</option>
                                <option>Pending</option>
                                <option>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="provider-notes" class="mb-2 block text-sm font-semibold text-fleet-ink">Notes</label>
                        <textarea id="provider-notes" rows="4" class="vehicle-form-control min-h-24 resize-y"></textarea>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" data-close-provider-modal class="inline-flex h-11 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface px-5 text-sm font-semibold text-fleet-ink shadow-sm transition hover:bg-slate-50">Cancel</button>
                    <button type="button" class="inline-flex h-11 items-center justify-center rounded-lg bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-fleet-card transition hover:bg-fleet-sidebar-active">Add Provider</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
