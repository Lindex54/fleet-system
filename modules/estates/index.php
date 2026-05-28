<?php
// Estates & Works page backed by the estate handler and database.
$activePage = 'estates';
require_once __DIR__ . '/../../handlers/estates.php';
extract(estateFetchPageData());
require_once __DIR__ . '/helpers.php';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1536px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="hidden h-14 w-14 items-center justify-center rounded-2xl bg-slate-200 text-fleet-sidebar sm:flex">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 21h18"></path>
                        <path d="M5 21V7l8-4v18"></path>
                        <path d="M19 21V11l-6-4"></path>
                        <path d="M9 9h1"></path>
                        <path d="M9 13h1"></path>
                        <path d="M9 17h1"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Estates &amp; Works</h1>
                    <p class="mt-2 text-sm text-fleet-muted">Busitema University - Infrastructure Project Tracker</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" data-print-page class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm transition hover:bg-slate-50">
                    <span>Print</span>
                </button>
                <button type="button" data-open-estate-new-modal class="inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card transition hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>New Project</span>
                </button>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>
        </div>

        <?php if (!empty($estateNotification)): ?>
            <?php $isSuccessNotice = ($estateNotification['type'] ?? '') === 'success'; ?>
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
                                <h2 class="text-sm font-extrabold uppercase tracking-[0.18em]"><?= htmlspecialchars($estateNotification['title'] ?? 'Estate update', ENT_QUOTES, 'UTF-8'); ?></h2>
                                <p class="mt-1 text-sm leading-6 text-fleet-ink"><?= htmlspecialchars($estateNotification['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <button type="button" data-dismiss-flash class="pointer-events-auto inline-flex h-9 w-9 items-center justify-center rounded-full border text-base font-bold transition <?= $isSuccessNotice ? 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' : 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'; ?>" aria-label="Dismiss notification">x</button>
                        </div>
                        <div class="mt-3 h-1.5 overflow-hidden rounded-full <?= $isSuccessNotice ? 'bg-green-100' : 'bg-red-100'; ?>"><div data-flash-progress class="h-full w-full origin-left rounded-full <?= $isSuccessNotice ? 'bg-green-600' : 'bg-red-600'; ?>"></div></div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="mb-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="interactive-card rounded-xl border border-blue-200 bg-blue-50 p-6 shadow-fleet-card"><p class="text-2xl font-extrabold leading-6 text-fleet-primary"><?= (int) $estateSummary['total_projects']; ?></p><p class="mt-2 text-sm text-fleet-muted">Total Projects</p></article>
            <article class="interactive-card rounded-xl border border-yellow-300 bg-yellow-50 p-6 shadow-fleet-card"><p class="text-2xl font-extrabold leading-6 text-fleet-warning-strong"><?= (int) $estateSummary['in_progress']; ?></p><p class="mt-2 text-sm text-fleet-muted">In Progress</p></article>
            <article class="interactive-card rounded-xl border border-green-200 bg-fleet-success-soft p-6 shadow-fleet-card"><p class="text-2xl font-extrabold leading-6 text-fleet-success"><?= (int) $estateSummary['completed']; ?></p><p class="mt-2 text-sm text-fleet-muted">Completed</p></article>
            <article class="interactive-card rounded-xl border border-red-200 bg-fleet-danger-soft p-6 shadow-fleet-card"><p class="text-2xl font-extrabold leading-6 text-fleet-danger"><?= (int) $estateSummary['overdue']; ?> / <?= (int) $estateSummary['on_hold']; ?></p><p class="mt-2 text-sm text-fleet-muted">Overdue / On Hold</p></article>
        </section>

        <section class="mb-8 rounded-xl border border-purple-200 bg-purple-50 p-6 shadow-fleet-card">
            <div class="grid gap-6 md:grid-cols-3">
                <div><p class="text-sm text-fleet-muted">Total Budget</p><p class="text-xl font-extrabold text-purple-700"><?= estateFormatMoney((float) $estateSummary['total_budget']); ?></p></div>
                <div><p class="text-sm text-fleet-muted">Total Spent</p><p class="text-xl font-extrabold text-purple-700"><?= estateFormatMoney((float) $estateSummary['total_spent']); ?></p></div>
                <div><p class="text-sm text-fleet-muted">Remaining</p><p class="text-xl font-extrabold text-fleet-success"><?= estateFormatMoney(max((float) $estateSummary['total_budget'] - (float) $estateSummary['total_spent'], 0)); ?></p></div>
            </div>
        </section>

        <section class="mb-6 grid gap-3 xl:grid-cols-[1fr_210px_210px]">
            <label class="relative block">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">Q</span>
                <input id="estate-project-search" type="search" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-12 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search projects, location, contractor...">
            </label>
            <select id="estate-status-filter" class="vehicle-form-control">
                <option value="all">All Statuses</option>
                <option value="In Progress">In Progress</option>
                <option value="Approved">Approved</option>
                <option value="On Hold">On Hold</option>
                <option value="Completed">Completed</option>
                <option value="Planned">Planned</option>
                <option value="Cancelled">Cancelled</option>
            </select>
            <select id="estate-category-filter" class="vehicle-form-control">
                <option value="all">All Categories</option>
                <?php foreach (array_unique(array_map(static fn(array $project): string => $project['category'], $projects)) as $category): ?>
                    <option value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
        </section>

        <p class="mb-6 text-sm text-fleet-muted">Showing <?= count($projects); ?> of <?= count($projects); ?> projects</p>

        <section class="<?= $hasProjects ? 'grid' : 'hidden'; ?> gap-5 md:grid-cols-2 xl:grid-cols-4" data-estate-project-list>
            <?php foreach ($projects as $project): ?>
                <article
                    class="estate-project-card group interactive-card relative overflow-hidden rounded-xl border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card"
                    data-project-id="<?= htmlspecialchars((string) $project['id'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-status="<?= htmlspecialchars($project['status'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-category="<?= htmlspecialchars($project['category'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-name="<?= htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-code="<?= htmlspecialchars($project['code'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-location="<?= htmlspecialchars($project['location'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-contractor="<?= htmlspecialchars($project['contractor'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-contractor-contact="<?= htmlspecialchars($project['contractor_contact'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-start="<?= htmlspecialchars($project['start'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-start-raw="<?= htmlspecialchars($project['start_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-deadline="<?= htmlspecialchars($project['deadline'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-deadline-raw="<?= htmlspecialchars($project['deadline_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-funding="<?= htmlspecialchars($project['funding'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-priority="<?= htmlspecialchars($project['priority'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-progress="<?= (int) $project['progress']; ?>"
                    data-budget="<?= htmlspecialchars($project['budget'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-budget-raw="<?= htmlspecialchars($project['budget_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-spent="<?= htmlspecialchars($project['spent'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-spent-raw="<?= htmlspecialchars($project['spent_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-remaining="<?= htmlspecialchars($project['remaining'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-description="<?= htmlspecialchars($project['description'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-search="<?= htmlspecialchars($project['search'], ENT_QUOTES, 'UTF-8'); ?>"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-start gap-3">
                                <span class="text-fleet-warning-strong"><?= estateProjectIcon($project['icon']); ?></span>
                                <div class="min-w-0">
                                    <h2 class="truncate text-base font-extrabold text-fleet-ink" title="<?= htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                    <p class="text-sm text-fleet-muted"><?= htmlspecialchars($project['code'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-lg border px-3 py-1 text-sm font-semibold <?= $statusClasses[$project['status']] ?? 'border-slate-300 bg-slate-100 text-slate-700'; ?>"><?= htmlspecialchars($project['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>

                    <div class="mt-5 space-y-2 text-sm text-fleet-muted">
                        <p><?= htmlspecialchars($project['location'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><?= htmlspecialchars($project['contractor'], ENT_QUOTES, 'UTF-8'); ?> <span class="text-fleet-danger"><?= htmlspecialchars($project['deadline'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                    </div>

                    <div class="mt-5">
                        <div class="mb-2 flex items-center justify-between text-sm"><span class="text-fleet-muted">Progress</span><span class="font-extrabold text-fleet-ink"><?= (int) $project['progress']; ?>%</span></div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-200"><div class="h-full rounded-full bg-fleet-sidebar" style="width: <?= (int) $project['progress']; ?>%"></div></div>
                    </div>

                    <div class="mt-4">
                        <div class="mb-2 flex items-center justify-between text-sm"><span class="text-fleet-muted">Budget Used</span><span class="font-extrabold text-fleet-ink"><?= (int) $project['budget_used']; ?>%</span></div>
                        <div class="flex items-center justify-between gap-3 text-sm text-fleet-muted"><span><?= htmlspecialchars($project['spent'], ENT_QUOTES, 'UTF-8'); ?></span><span>of <?= htmlspecialchars($project['budget'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                    </div>

                    <div class="mt-6 flex items-center justify-between">
                        <span class="rounded-lg border px-3 py-1 text-sm font-semibold <?= $priorityClasses[$project['priority']] ?? 'border-slate-300 bg-slate-50 text-slate-700'; ?>"><?= htmlspecialchars($project['priority'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <div class="flex translate-y-1 items-center gap-2 opacity-0 transition duration-150 group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:translate-y-0 group-focus-within:opacity-100">
                            <button type="button" data-open-estate-view-modal class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-fleet-ink transition hover:bg-blue-50 hover:text-fleet-primary" aria-label="View project">V</button>
                            <button type="button" data-open-estate-edit-modal class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-fleet-ink transition hover:bg-blue-50 hover:text-fleet-primary" aria-label="Edit project">E</button>
                            <form action="<?= htmlspecialchars($estateFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post">
                                <input type="hidden" name="estate_action" value="delete">
                                <input type="hidden" name="project_id" value="<?= htmlspecialchars((string) $project['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" data-open-estate-delete class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-fleet-danger transition hover:bg-red-50" aria-label="Delete project">D</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="<?= $hasProjects ? 'hidden' : 'flex'; ?> min-h-[320px] items-center justify-center rounded-xl border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="text-center">
                <h2 class="text-xl font-extrabold text-fleet-ink">No estate projects found</h2>
                <p class="mt-2 text-sm text-fleet-muted">Create your first project to start tracking infrastructure work.</p>
            </div>
        </section>
    </div>

    <div id="estate-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
        <div class="dashboard-scroll max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl">
            <div class="p-7">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-extrabold text-fleet-ink" data-estate-view-name>Project Name</h2>
                        <p class="mt-1 text-sm text-fleet-muted" data-estate-view-code>Code</p>
                    </div>
                    <button type="button" data-close-estate-view-modal class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted transition hover:bg-slate-100 hover:text-fleet-ink" aria-label="Close project details">&times;</button>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div><p class="text-sm text-fleet-muted">Status</p><p class="mt-1" data-estate-view-status></p></div>
                    <div><p class="text-sm text-fleet-muted">Priority</p><p class="mt-1" data-estate-view-priority></p></div>
                    <div><p class="text-sm text-fleet-muted">Category</p><p class="mt-1 font-semibold text-fleet-ink" data-estate-view-category></p></div>
                    <div><p class="text-sm text-fleet-muted">Location</p><p class="mt-1 font-semibold text-fleet-ink" data-estate-view-location></p></div>
                    <div><p class="text-sm text-fleet-muted">Contractor</p><p class="mt-1 font-semibold text-fleet-ink" data-estate-view-contractor></p></div>
                    <div><p class="text-sm text-fleet-muted">Funding</p><p class="mt-1 font-semibold text-fleet-ink" data-estate-view-funding></p></div>
                    <div><p class="text-sm text-fleet-muted">Start Date</p><p class="mt-1 font-semibold text-fleet-ink" data-estate-view-start></p></div>
                    <div><p class="text-sm text-fleet-muted">Deadline</p><p class="mt-1 font-semibold text-fleet-ink" data-estate-view-deadline></p></div>
                </div>
                <div class="mt-6">
                    <div class="mb-2 flex items-center justify-between"><span class="text-sm text-fleet-muted">Progress</span><span class="font-extrabold text-fleet-ink"><span data-estate-view-progress>0</span>%</span></div>
                    <div class="h-3 overflow-hidden rounded-full bg-slate-200"><div class="h-full rounded-full bg-fleet-sidebar" data-estate-view-progress-bar style="width:0%"></div></div>
                </div>
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <div><p class="text-sm text-fleet-muted">Budget</p><p class="mt-1 font-extrabold text-fleet-ink" data-estate-view-budget>UGX 0</p></div>
                    <div><p class="text-sm text-fleet-muted">Spent</p><p class="mt-1 font-extrabold text-fleet-ink" data-estate-view-spent>UGX 0</p></div>
                    <div><p class="text-sm text-fleet-muted">Remaining</p><p class="mt-1 font-extrabold text-fleet-success" data-estate-view-remaining>UGX 0</p></div>
                </div>
                <div class="mt-6">
                    <p class="mb-2 text-sm font-semibold text-fleet-muted">Description</p>
                    <p class="text-base leading-7 text-fleet-ink" data-estate-view-description>Project description.</p>
                </div>
            </div>
        </div>
    </div>

    <?php
    $isEstateUpdateMode = $estateFormMode === 'update';
    $estateModalId = $isEstateUpdateMode ? 'estate-edit-modal' : 'estate-new-modal';
    $estateModalOpenClass = $shouldOpenEstateModal ? 'flex' : 'hidden';
    ?>
    <div id="estate-edit-modal" class="fixed inset-0 z-50 <?= $isEstateUpdateMode ? $estateModalOpenClass : 'hidden'; ?> items-center justify-center bg-black/75 px-4 py-6" aria-hidden="<?= $isEstateUpdateMode && $shouldOpenEstateModal ? 'false' : 'true'; ?>" data-open-on-load="<?= $isEstateUpdateMode && $shouldOpenEstateModal ? 'true' : 'false'; ?>">
        <div class="dashboard-scroll max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl">
            <form class="p-7" action="<?= htmlspecialchars($estateFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post">
                <input type="hidden" name="estate_action" value="update">
                <input type="hidden" name="project_id" value="<?= htmlspecialchars($estateFormData['project_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <h2 class="text-xl font-extrabold text-fleet-ink">Edit Project</h2>
                    <button type="button" data-close-estate-edit-modal class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted transition hover:bg-slate-100 hover:text-fleet-ink" aria-label="Close edit project form">&times;</button>
                </div>
                <?php include __DIR__ . '/partials/edit-form.php'; ?>
                <div class="mt-7 flex justify-end gap-3">
                    <button type="button" data-close-estate-edit-modal class="inline-flex h-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface px-5 text-sm font-semibold text-fleet-ink shadow-sm transition hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-fleet-card transition hover:bg-fleet-sidebar-active">Update Project</button>
                </div>
            </form>
        </div>
    </div>

    <div id="estate-new-modal" class="fixed inset-0 z-50 <?= !$isEstateUpdateMode ? $estateModalOpenClass : 'hidden'; ?> items-center justify-center bg-black/75 px-4 py-6" aria-hidden="<?= !$isEstateUpdateMode && $shouldOpenEstateModal ? 'false' : 'true'; ?>" data-open-on-load="<?= !$isEstateUpdateMode && $shouldOpenEstateModal ? 'true' : 'false'; ?>">
        <div class="dashboard-scroll max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl">
            <form class="p-7" action="<?= htmlspecialchars($estateFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post">
                <input type="hidden" name="estate_action" value="create">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <h2 class="text-xl font-extrabold text-fleet-ink">New Estates Project</h2>
                    <button type="button" data-close-estate-new-modal class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted transition hover:bg-slate-100 hover:text-fleet-ink" aria-label="Close new project form">&times;</button>
                </div>
                <?php include __DIR__ . '/partials/new-form.php'; ?>
                <div class="mt-7 flex justify-end gap-3">
                    <button type="button" data-close-estate-new-modal class="inline-flex h-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface px-5 text-sm font-semibold text-fleet-ink shadow-sm transition hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-fleet-card transition hover:bg-fleet-sidebar-active">Create Project</button>
                </div>
            </form>
        </div>
    </div>

    <div id="estate-delete-modal" class="logbook-delete-overlay" aria-hidden="true">
        <div class="logbook-delete-card" role="dialog" aria-modal="true" aria-labelledby="estate-delete-modal-title">
            <div class="logbook-delete-header">
                <div class="flex items-center gap-4">
                    <div class="logbook-delete-icon">!</div>
                    <div>
                        <p class="logbook-delete-eyebrow">Delete Confirmation</p>
                        <h2 id="estate-delete-modal-title" class="logbook-delete-title">Remove estate project?</h2>
                    </div>
                </div>
            </div>
            <div class="logbook-delete-body">
                <p class="logbook-delete-copy">This estate project will be removed from the system. This action cannot be undone.</p>
                <div class="logbook-delete-actions">
                    <button type="button" data-cancel-estate-delete class="logbook-delete-button logbook-delete-button-secondary">Keep Project</button>
                    <button type="button" data-confirm-estate-delete class="logbook-delete-button logbook-delete-button-danger">Delete Project</button>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
