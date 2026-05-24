<?php
// Static frontend page for Busitema University infrastructure project tracking.
$activePage = 'estates';
require_once __DIR__ . '/../../includes/data.php';
extract(fleetData('estates'));
require_once __DIR__ . '/helpers.php';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1536px] px-4 py-8 sm:px-6 lg:px-8">
        <!-- Page header: title, context text, print action, and modal trigger for creating projects. -->
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
                    <p class="mt-2 text-sm text-fleet-muted">Busitema University &mdash; Infrastructure Project Tracker</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" data-print-page class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm transition hover:bg-slate-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 9V2h12v7"></path>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <path d="M6 14h12v8H6z"></path>
                    </svg>
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

        <!-- Summary cards: high-level project counts that will later be calculated from backend records. -->
        <section class="mb-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="interactive-card rounded-xl border border-blue-200 bg-blue-50 p-6 shadow-fleet-card">
                <div class="flex items-center gap-5">
                    <svg class="h-7 w-7 text-fleet-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 7h5l2 2h11v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <path d="M3 7V5a2 2 0 0 1 2-2h4l2 2h4"></path>
                    </svg>
                    <div>
                        <p class="text-2xl font-extrabold leading-6 text-fleet-primary">5</p>
                        <p class="mt-2 text-sm text-fleet-muted">Total Projects</p>
                    </div>
                </div>
            </article>
            <article class="interactive-card rounded-xl border border-yellow-300 bg-yellow-50 p-6 shadow-fleet-card">
                <div class="flex items-center gap-5">
                    <svg class="h-7 w-7 text-fleet-warning-strong" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 6v6l4 2"></path>
                    </svg>
                    <div>
                        <p class="text-2xl font-extrabold leading-6 text-fleet-warning-strong">1</p>
                        <p class="mt-2 text-sm text-fleet-muted">In Progress</p>
                    </div>
                </div>
            </article>
            <article class="interactive-card rounded-xl border border-green-200 bg-fleet-success-soft p-6 shadow-fleet-card">
                <div class="flex items-center gap-5">
                    <svg class="h-7 w-7 text-fleet-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M22 11.1V12a10 10 0 1 1-5.9-9.1"></path>
                        <path d="m22 4-10 10.01-3-3"></path>
                    </svg>
                    <div>
                        <p class="text-2xl font-extrabold leading-6 text-fleet-success">1</p>
                        <p class="mt-2 text-sm text-fleet-muted">Completed</p>
                    </div>
                </div>
            </article>
            <article class="interactive-card rounded-xl border border-red-200 bg-fleet-danger-soft p-6 shadow-fleet-card">
                <div class="flex items-center gap-5">
                    <svg class="h-7 w-7 text-fleet-danger" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m21.7 18-8.6-15a1.2 1.2 0 0 0-2.2 0L2.3 18A1.2 1.2 0 0 0 3.4 20h17.2a1.2 1.2 0 0 0 1.1-2Z"></path>
                        <path d="M12 9v4"></path>
                        <path d="M12 17h.01"></path>
                    </svg>
                    <div>
                        <p class="text-2xl font-extrabold leading-6 text-fleet-danger">4 / 1</p>
                        <p class="mt-2 text-sm text-fleet-muted">Overdue / On Hold</p>
                    </div>
                </div>
            </article>
        </section>

        <!-- Budget summary strip: currently static, later should be derived from total budget/spent values. -->
        <section class="mb-8 rounded-xl border border-purple-200 bg-purple-50 p-6 shadow-fleet-card">
            <div class="grid gap-6 md:grid-cols-3">
                <div class="flex items-center gap-4">
                    <svg class="h-5 w-5 text-purple-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="20" height="12" x="2" y="6" rx="2"></rect>
                        <circle cx="12" cy="12" r="2"></circle>
                    </svg>
                    <div>
                        <p class="text-sm text-fleet-muted">Total Budget</p>
                        <p class="text-xl font-extrabold text-purple-700">UGX 2,090,000,000</p>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-fleet-muted">Total Spent</p>
                    <p class="text-xl font-extrabold text-purple-700">UGX 688,000,000</p>
                </div>
                <div>
                    <p class="text-sm text-fleet-muted">Remaining</p>
                    <p class="text-xl font-extrabold text-fleet-success">UGX 1,402,000,000</p>
                </div>
            </div>
        </section>

        <!-- Search and filter controls; JavaScript reads these IDs to filter project cards client-side. -->
        <section class="mb-6 grid gap-3 xl:grid-cols-[1fr_210px_210px_auto]">
            <label class="relative block">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                </span>
                <input id="estate-project-search" type="search" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-12 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search projects, location, contractor...">
            </label>
            <select id="estate-status-filter" class="vehicle-form-control">
                <option value="all">All Statuses</option>
                <option value="In Progress">In Progress</option>
                <option value="Approved">Approved</option>
                <option value="On Hold">On Hold</option>
                <option value="Completed">Completed</option>
                <option value="Planned">Planned</option>
            </select>
            <select id="estate-category-filter" class="vehicle-form-control">
                <option value="all">All Categories</option>
                <option value="Construction">Construction</option>
                <option value="Road Works">Road Works</option>
                <option value="Electrical">Electrical</option>
            </select>
            <div class="flex overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-sm">
                <button type="button" class="inline-flex h-11 w-12 items-center justify-center bg-fleet-sidebar text-white" aria-label="Grid view">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="7" height="7" x="3" y="3" rx="1"></rect>
                        <rect width="7" height="7" x="14" y="3" rx="1"></rect>
                        <rect width="7" height="7" x="14" y="14" rx="1"></rect>
                        <rect width="7" height="7" x="3" y="14" rx="1"></rect>
                    </svg>
                </button>
                <button type="button" class="inline-flex h-11 w-12 items-center justify-center text-fleet-ink hover:bg-slate-50" aria-label="List view">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M8 6h13"></path>
                        <path d="M8 12h13"></path>
                        <path d="M8 18h13"></path>
                        <path d="M3 6h.01"></path>
                        <path d="M3 12h.01"></path>
                        <path d="M3 18h.01"></path>
                    </svg>
                </button>
            </div>
        </section>

        <p class="mb-6 text-sm text-fleet-muted">Showing 5 of 5 projects</p>

        <!-- Project cards: each card exposes its project fields as data-* attributes for the modals. -->
        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4" data-estate-project-list>
            <?php foreach ($projects as $project): ?>
                <article class="estate-project-card group interactive-card relative overflow-hidden rounded-xl border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card"
                    data-status="<?= htmlspecialchars($project['status'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-category="<?= htmlspecialchars($project['category'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-name="<?= htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-code="<?= htmlspecialchars($project['code'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-location="<?= htmlspecialchars($project['location'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-contractor="<?= htmlspecialchars($project['contractor'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-contractor-contact="<?= htmlspecialchars($project['contractor_contact'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-start="<?= htmlspecialchars($project['start'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-deadline="<?= htmlspecialchars($project['deadline'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-funding="<?= htmlspecialchars($project['funding'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-priority="<?= htmlspecialchars($project['priority'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-progress="<?= (int) $project['progress']; ?>"
                    data-budget="<?= htmlspecialchars($project['budget'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-spent="<?= htmlspecialchars($project['spent'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-remaining="<?= htmlspecialchars($project['remaining'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-description="<?= htmlspecialchars($project['description'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-search="<?= htmlspecialchars(strtolower(implode(' ', $project)), ENT_QUOTES, 'UTF-8'); ?>">
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
                        <p class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span class="truncate"><?= htmlspecialchars($project['location'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </p>
                        <p class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span class="truncate"><?= htmlspecialchars($project['contractor'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <svg class="h-4 w-4 shrink-0 text-fleet-danger" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M8 2v4"></path>
                                <path d="M16 2v4"></path>
                                <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                <path d="M3 10h18"></path>
                            </svg>
                            <span class="shrink-0 text-fleet-danger"><?= htmlspecialchars($project['deadline'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </p>
                    </div>

                    <div class="mt-5">
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="text-fleet-muted">Progress</span>
                            <span class="font-extrabold text-fleet-ink"><?= (int) $project['progress']; ?>%</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full rounded-full bg-fleet-sidebar" style="width: <?= (int) $project['progress']; ?>%"></div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="inline-flex items-center gap-2 text-fleet-muted">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="20" height="12" x="2" y="6" rx="2"></rect>
                                    <circle cx="12" cy="12" r="2"></circle>
                                </svg>
                                Budget Used
                            </span>
                            <span class="font-extrabold text-fleet-ink"><?= (int) $project['budget_used']; ?>%</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-sm text-fleet-muted">
                            <span><?= htmlspecialchars($project['spent'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span>of <?= htmlspecialchars($project['budget'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between">
                        <span class="rounded-lg border px-3 py-1 text-sm font-semibold <?= $priorityClasses[$project['priority']] ?? 'border-slate-300 bg-slate-50 text-slate-700'; ?>"><?= htmlspecialchars($project['priority'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <!-- Hover actions stay hidden until the user hovers or tabs into the project card. -->
                        <div class="flex translate-y-1 items-center gap-2 opacity-0 transition duration-150 group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:translate-y-0 group-focus-within:opacity-100">
                            <button type="button" data-open-estate-view-modal class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-fleet-ink transition hover:bg-blue-50 hover:text-fleet-primary" aria-label="View project">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                            <button type="button" data-open-estate-edit-modal class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-fleet-ink transition hover:bg-blue-50 hover:text-fleet-primary" aria-label="Edit project">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                </svg>
                            </button>
                            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-fleet-danger transition hover:bg-red-50 hover:text-fleet-danger-strong" aria-label="Delete project">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 6h18"></path>
                                    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                    <path d="M10 11v6"></path>
                                    <path d="M14 11v6"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </div>

    <!-- View modal: populated from the clicked card's data-* attributes in assets/js/app.js. -->
    <div id="estate-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
        <div class="dashboard-scroll max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl">
            <div class="p-7">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-extrabold text-fleet-ink" data-estate-view-name>Project Details</h2>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <span data-estate-view-status class="rounded-lg border px-3 py-1 text-sm font-semibold">Status</span>
                            <span data-estate-view-priority class="rounded-lg border px-3 py-1 text-sm font-semibold">Priority</span>
                            <span data-estate-view-code class="rounded-lg border border-fleet-line bg-slate-50 px-3 py-1 text-sm font-semibold text-fleet-ink">BU-EST-000</span>
                            <span data-estate-view-category class="rounded-lg border border-fleet-line bg-slate-50 px-3 py-1 text-sm font-semibold text-fleet-ink">Category</span>
                        </div>
                    </div>
                    <button type="button" data-close-estate-view-modal class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted transition hover:bg-slate-100 hover:text-fleet-ink" aria-label="Close project details">&times;</button>
                </div>

                <section class="rounded-xl bg-slate-50 p-5">
                    <p class="mb-5 flex items-center gap-2 text-base text-fleet-muted">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <span data-estate-view-location>Project location</span>
                    </p>
                    <div class="grid gap-5 text-sm sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <p class="text-fleet-muted">Manager</p>
                            <p class="mt-1 font-semibold text-fleet-ink" data-estate-view-contractor>Manager name</p>
                        </div>
                        <div>
                            <p class="text-fleet-muted">Start</p>
                            <p class="mt-1 font-semibold text-fleet-ink" data-estate-view-start>Start date</p>
                        </div>
                        <div>
                            <p class="text-fleet-muted">Expected End</p>
                            <p class="mt-1 font-semibold text-fleet-ink" data-estate-view-deadline>Expected end</p>
                        </div>
                        <div>
                            <p class="text-fleet-muted">Funding</p>
                            <p class="mt-1 font-semibold text-fleet-ink" data-estate-view-funding>Funding source</p>
                        </div>
                    </div>
                </section>

                <section class="mt-6">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="font-semibold text-fleet-muted">Overall Progress</p>
                        <p class="text-lg font-extrabold text-fleet-ink"><span data-estate-view-progress>0</span>%</p>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-slate-300">
                        <div data-estate-view-progress-bar class="h-full rounded-full bg-fleet-sidebar" style="width: 0%"></div>
                    </div>
                </section>

                <section class="mt-6 grid gap-5 rounded-xl border border-fleet-line p-5 text-center sm:grid-cols-3">
                    <div>
                        <p class="text-sm text-fleet-muted">Budget</p>
                        <p class="mt-1 font-extrabold text-fleet-ink" data-estate-view-budget>UGX 0</p>
                    </div>
                    <div>
                        <p class="text-sm text-fleet-muted">Spent</p>
                        <p class="mt-1 font-extrabold text-fleet-ink" data-estate-view-spent>UGX 0</p>
                    </div>
                    <div>
                        <p class="text-sm text-fleet-muted">Remaining</p>
                        <p class="mt-1 font-extrabold text-fleet-success" data-estate-view-remaining>UGX 0</p>
                    </div>
                </section>

                <section class="mt-6">
                    <p class="mb-2 text-sm font-semibold text-fleet-muted">Description</p>
                    <p class="text-base leading-7 text-fleet-ink" data-estate-view-description>Project description.</p>
                </section>

                <section class="mt-6 overflow-hidden rounded-xl border border-fleet-sidebar">
                    <div class="grid grid-cols-2 bg-slate-200 p-1">
                        <button type="button" class="rounded-lg bg-fleet-surface py-2 text-sm font-extrabold text-fleet-ink shadow-sm">Milestones</button>
                        <button type="button" class="rounded-lg py-2 text-sm font-extrabold text-fleet-muted">Site Updates</button>
                    </div>
                    <div class="min-h-40 p-5">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-lg text-fleet-muted">0 / 0 completed</p>
                            <button type="button" class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm transition hover:bg-slate-50">
                                <span class="text-xl leading-none">+</span>
                                <span>Add Milestone</span>
                            </button>
                        </div>
                        <p class="mt-12 text-center text-base text-fleet-muted">No milestones added yet.</p>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Edit modal: shares the project data model with the cards, but presents values as editable controls. -->
    <div id="estate-edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
        <div class="dashboard-scroll max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl">
            <form class="p-7">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <h2 class="text-xl font-extrabold text-fleet-ink">Edit Project</h2>
                    <button type="button" data-close-estate-edit-modal class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted transition hover:bg-slate-100 hover:text-fleet-ink" aria-label="Close edit project form">&times;</button>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="estate-edit-title" class="mb-2 block text-sm font-semibold text-fleet-ink">Project Title *</label>
                        <input id="estate-edit-title" type="text" class="vehicle-form-control" data-estate-edit-name>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="estate-edit-code" class="mb-2 block text-sm font-semibold text-fleet-ink">Project Code</label>
                            <input id="estate-edit-code" type="text" class="vehicle-form-control" data-estate-edit-code>
                        </div>
                        <div>
                            <label for="estate-edit-category" class="mb-2 block text-sm font-semibold text-fleet-ink">Category</label>
                            <select id="estate-edit-category" class="vehicle-form-control" data-estate-edit-category>
                                <option value="Construction">construction</option>
                                <option value="Road Works">road</option>
                                <option value="Electrical">electrical</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="estate-edit-location" class="mb-2 block text-sm font-semibold text-fleet-ink">Location / Area</label>
                            <input id="estate-edit-location" type="text" class="vehicle-form-control" data-estate-edit-location>
                        </div>
                        <div>
                            <label for="estate-edit-status" class="mb-2 block text-sm font-semibold text-fleet-ink">Status</label>
                            <select id="estate-edit-status" class="vehicle-form-control" data-estate-edit-status>
                                <option value="Approved">approved</option>
                                <option value="In Progress">in progress</option>
                                <option value="On Hold">on hold</option>
                                <option value="Completed">completed</option>
                                <option value="Planned">planned</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="estate-edit-priority" class="mb-2 block text-sm font-semibold text-fleet-ink">Priority</label>
                            <select id="estate-edit-priority" class="vehicle-form-control" data-estate-edit-priority>
                                <option value="High">high</option>
                                <option value="Medium">medium</option>
                                <option value="Low">low</option>
                            </select>
                        </div>
                        <div>
                            <label for="estate-edit-start" class="mb-2 block text-sm font-semibold text-fleet-ink">Start Date</label>
                            <input id="estate-edit-start" type="text" class="vehicle-form-control" data-estate-edit-start>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="estate-edit-end" class="mb-2 block text-sm font-semibold text-fleet-ink">Expected End Date</label>
                            <input id="estate-edit-end" type="text" class="vehicle-form-control" data-estate-edit-deadline>
                        </div>
                        <div>
                            <label for="estate-edit-budget" class="mb-2 block text-sm font-semibold text-fleet-ink">Budget (UGX)</label>
                            <input id="estate-edit-budget" type="text" class="vehicle-form-control" data-estate-edit-budget>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="estate-edit-spent" class="mb-2 block text-sm font-semibold text-fleet-ink">Spent So Far (UGX)</label>
                            <input id="estate-edit-spent" type="text" class="vehicle-form-control" data-estate-edit-spent>
                        </div>
                        <div>
                            <label for="estate-edit-progress" class="mb-2 block text-sm font-semibold text-fleet-ink">Progress % (<span data-estate-edit-progress-label>0</span>%)</label>
                            <input id="estate-edit-progress" type="range" min="0" max="100" class="h-11 w-full accent-fleet-primary" data-estate-edit-progress>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="estate-edit-contractor" class="mb-2 block text-sm font-semibold text-fleet-ink">Contractor Name</label>
                            <input id="estate-edit-contractor" type="text" class="vehicle-form-control" data-estate-edit-contractor>
                        </div>
                        <div>
                            <label for="estate-edit-contractor-contact" class="mb-2 block text-sm font-semibold text-fleet-ink">Contractor Contact</label>
                            <input id="estate-edit-contractor-contact" type="text" class="vehicle-form-control" data-estate-edit-contractor-contact>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="estate-edit-manager" class="mb-2 block text-sm font-semibold text-fleet-ink">Project Manager</label>
                            <input id="estate-edit-manager" type="text" class="vehicle-form-control" data-estate-edit-manager>
                        </div>
                        <div>
                            <label for="estate-edit-funding" class="mb-2 block text-sm font-semibold text-fleet-ink">Funding Source</label>
                            <input id="estate-edit-funding" type="text" class="vehicle-form-control" data-estate-edit-funding>
                        </div>
                    </div>

                    <div>
                        <label for="estate-edit-description" class="mb-2 block text-sm font-semibold text-fleet-ink">Description</label>
                        <textarea id="estate-edit-description" rows="3" class="vehicle-form-control min-h-24 resize-y py-3" data-estate-edit-description></textarea>
                    </div>

                    <div>
                        <label for="estate-edit-notes" class="mb-2 block text-sm font-semibold text-fleet-ink">Additional Notes</label>
                        <textarea id="estate-edit-notes" rows="3" class="vehicle-form-control min-h-20 resize-y py-3" placeholder="Any additional notes..."></textarea>
                    </div>
                </div>

                <div class="mt-7 flex justify-end gap-3">
                    <button type="button" data-close-estate-edit-modal class="inline-flex h-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface px-5 text-sm font-semibold text-fleet-ink shadow-sm transition hover:bg-slate-50">Cancel</button>
                    <button type="button" class="inline-flex h-10 items-center justify-center rounded-lg bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-fleet-card transition hover:bg-fleet-sidebar-active">Update Project</button>
                </div>
            </form>
        </div>
    </div>

    <!-- New project modal: blank/default version of the project form used for creating a fresh record. -->
    <div id="estate-new-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
        <div class="dashboard-scroll max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl">
            <form class="p-7">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <h2 class="text-xl font-extrabold text-fleet-ink">New Estates Project</h2>
                    <button type="button" data-close-estate-new-modal class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted transition hover:bg-slate-100 hover:text-fleet-ink" aria-label="Close new project form">&times;</button>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="estate-new-title" class="mb-2 block text-sm font-semibold text-fleet-ink">Project Title *</label>
                        <input id="estate-new-title" type="text" class="vehicle-form-control" placeholder="e.g. Library Extension Block">
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="estate-new-code" class="mb-2 block text-sm font-semibold text-fleet-ink">Project Code</label>
                            <input id="estate-new-code" type="text" class="vehicle-form-control" placeholder="e.g. BU-EST-001">
                        </div>
                        <div>
                            <label for="estate-new-category" class="mb-2 block text-sm font-semibold text-fleet-ink">Category</label>
                            <select id="estate-new-category" class="vehicle-form-control">
                                <option>building</option>
                                <option>road</option>
                                <option>electrical</option>
                                <option>water and sanitation</option>
                                <option>renovation</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="estate-new-location" class="mb-2 block text-sm font-semibold text-fleet-ink">Location / Area</label>
                            <input id="estate-new-location" type="text" class="vehicle-form-control" placeholder="e.g. Main Campus - Block B">
                        </div>
                        <div>
                            <label for="estate-new-status" class="mb-2 block text-sm font-semibold text-fleet-ink">Status</label>
                            <select id="estate-new-status" class="vehicle-form-control">
                                <option>planned</option>
                                <option>approved</option>
                                <option>in progress</option>
                                <option>on hold</option>
                                <option>completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="estate-new-priority" class="mb-2 block text-sm font-semibold text-fleet-ink">Priority</label>
                            <select id="estate-new-priority" class="vehicle-form-control">
                                <option>medium</option>
                                <option>high</option>
                                <option>low</option>
                            </select>
                        </div>
                        <div>
                            <label for="estate-new-start" class="mb-2 block text-sm font-semibold text-fleet-ink">Start Date</label>
                            <input id="estate-new-start" type="text" class="vehicle-form-control" placeholder="dd/mm/yyyy">
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="estate-new-end" class="mb-2 block text-sm font-semibold text-fleet-ink">Expected End Date</label>
                            <input id="estate-new-end" type="text" class="vehicle-form-control" placeholder="dd/mm/yyyy">
                        </div>
                        <div>
                            <label for="estate-new-budget" class="mb-2 block text-sm font-semibold text-fleet-ink">Budget (UGX)</label>
                            <input id="estate-new-budget" type="text" class="vehicle-form-control" value="0">
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="estate-new-spent" class="mb-2 block text-sm font-semibold text-fleet-ink">Spent So Far (UGX)</label>
                            <input id="estate-new-spent" type="text" class="vehicle-form-control" value="0">
                        </div>
                        <div>
                            <label for="estate-new-progress" class="mb-2 block text-sm font-semibold text-fleet-ink">Progress % (<span data-estate-new-progress-label>0</span>%)</label>
                            <input id="estate-new-progress" type="range" min="0" max="100" value="0" class="h-11 w-full accent-fleet-primary" data-estate-new-progress>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="estate-new-contractor" class="mb-2 block text-sm font-semibold text-fleet-ink">Contractor Name</label>
                            <input id="estate-new-contractor" type="text" class="vehicle-form-control">
                        </div>
                        <div>
                            <label for="estate-new-contractor-contact" class="mb-2 block text-sm font-semibold text-fleet-ink">Contractor Contact</label>
                            <input id="estate-new-contractor-contact" type="text" class="vehicle-form-control">
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="estate-new-manager" class="mb-2 block text-sm font-semibold text-fleet-ink">Project Manager</label>
                            <input id="estate-new-manager" type="text" class="vehicle-form-control" placeholder="Supervising officer">
                        </div>
                        <div>
                            <label for="estate-new-funding" class="mb-2 block text-sm font-semibold text-fleet-ink">Funding Source</label>
                            <input id="estate-new-funding" type="text" class="vehicle-form-control" placeholder="e.g. Government Grant">
                        </div>
                    </div>

                    <div>
                        <label for="estate-new-description" class="mb-2 block text-sm font-semibold text-fleet-ink">Description</label>
                        <textarea id="estate-new-description" rows="3" class="vehicle-form-control min-h-24 resize-y py-3" placeholder="Describe the scope of work..."></textarea>
                    </div>

                    <div>
                        <label for="estate-new-notes" class="mb-2 block text-sm font-semibold text-fleet-ink">Additional Notes</label>
                        <textarea id="estate-new-notes" rows="3" class="vehicle-form-control min-h-20 resize-y py-3" placeholder="Any additional notes..."></textarea>
                    </div>
                </div>

                <div class="mt-7 flex justify-end gap-3">
                    <button type="button" data-close-estate-new-modal class="inline-flex h-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface px-5 text-sm font-semibold text-fleet-ink shadow-sm transition hover:bg-slate-50">Cancel</button>
                    <button type="button" class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-400 px-5 text-sm font-semibold text-white shadow-fleet-card transition hover:bg-fleet-sidebar">Create Project</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
