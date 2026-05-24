<?php
// Static frontend page for Busitema University infrastructure project tracking.
$activePage = 'estates';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

$projects = [
    [
        'icon' => 'crane',
        'name' => 'Library Extension Works',
        'code' => 'BU-EST-001',
        'location' => 'Main Campus - Library Block',
        'contractor' => 'Eng. Sarah Namusoke',
        'deadline' => '31 Dec 2025',
        'status' => 'In Progress',
        'priority' => 'High',
        'progress' => 38,
        'budget_used' => 38,
        'spent' => 'UGX 320,000,000',
        'budget' => 'UGX 850,000,000',
    ],
    [
        'icon' => 'road',
        'name' => 'Access Road Rehabilitation',
        'code' => 'BU-EST-002',
        'location' => 'Engineering Faculty Road',
        'contractor' => 'Eng. David Ochen',
        'deadline' => '30 Sep 2025',
        'status' => 'Approved',
        'priority' => 'High',
        'progress' => 0,
        'budget_used' => 0,
        'spent' => 'UGX 0',
        'budget' => 'UGX 420,000,000',
    ],
    [
        'icon' => 'crane',
        'name' => 'Student Hostel Block D',
        'code' => 'BU-EST-003',
        'location' => 'Student Village - Block D',
        'contractor' => 'Eng. Grace Akello',
        'deadline' => '31 Mar 2025',
        'status' => 'On Hold',
        'priority' => 'Medium',
        'progress' => 55,
        'budget_used' => 43,
        'spent' => 'UGX 280,000,000',
        'budget' => 'UGX 650,000,000',
    ],
    [
        'icon' => 'barrier',
        'name' => 'Campus Perimeter Fence',
        'code' => 'BU-EST-004',
        'location' => 'Northern Boundary - Main Campus',
        'contractor' => 'Mr. James Wafula',
        'deadline' => '30 Nov 2024',
        'status' => 'Completed',
        'priority' => 'Medium',
        'progress' => 100,
        'budget_used' => 93,
        'spent' => 'UGX 88,000,000',
        'budget' => 'UGX 95,000,000',
    ],
    [
        'icon' => 'bolt',
        'name' => 'Electrical Rewiring Phase II',
        'code' => 'BU-EST-005',
        'location' => 'Administration Block',
        'contractor' => 'Kampala Power Services',
        'deadline' => '18 Aug 2026',
        'status' => 'Planned',
        'priority' => 'Low',
        'progress' => 0,
        'budget_used' => 0,
        'spent' => 'UGX 0',
        'budget' => 'UGX 75,000,000',
    ],
];

$statusClasses = [
    'In Progress' => 'border-fleet-warning bg-fleet-warning-soft text-fleet-warning-strong',
    'Approved' => 'border-blue-300 bg-blue-100 text-fleet-primary',
    'On Hold' => 'border-orange-300 bg-orange-100 text-orange-700',
    'Completed' => 'border-green-300 bg-fleet-success-soft text-fleet-success',
    'Planned' => 'border-slate-300 bg-slate-100 text-slate-700',
];

$priorityClasses = [
    'High' => 'border-orange-300 bg-orange-50 text-orange-700',
    'Medium' => 'border-yellow-300 bg-yellow-50 text-yellow-700',
    'Low' => 'border-slate-300 bg-slate-50 text-slate-700',
];

function estateProjectIcon(string $name): string
{
    $attrs = 'class="h-7 w-7 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';
    $paths = [
        'crane' => '<path d="M3 21h18"></path><path d="M6 21V7h8"></path><path d="M6 7 3 4"></path><path d="M14 7l5 5"></path><path d="M19 12v3"></path><path d="M9 21v-8"></path>',
        'road' => '<path d="M6 20 10 4"></path><path d="m14 4 4 16"></path><path d="M12 8v2"></path><path d="M12 14v2"></path>',
        'barrier' => '<path d="M4 20v-6"></path><path d="M20 20v-6"></path><path d="M3 14h18"></path><path d="m5 14 5-6"></path><path d="m12 14 5-6"></path>',
        'bolt' => '<path d="M13 2 3 14h8l-1 8 10-12h-8z"></path>',
    ];

    return '<svg ' . $attrs . '>' . ($paths[$name] ?? $paths['crane']) . '</svg>';
}
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
                <button type="button" class="inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card transition hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>New Project</span>
                </button>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>
        </div>

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
                <option>Construction</option>
                <option>Road Works</option>
                <option>Electrical</option>
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

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4" data-estate-project-list>
            <?php foreach ($projects as $project): ?>
                <article class="estate-project-card group interactive-card relative overflow-hidden rounded-xl border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card" data-status="<?= htmlspecialchars($project['status'], ENT_QUOTES, 'UTF-8'); ?>" data-search="<?= htmlspecialchars(strtolower(implode(' ', $project)), ENT_QUOTES, 'UTF-8'); ?>">
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
                        <div class="flex translate-y-1 items-center gap-2 opacity-0 transition duration-150 group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:translate-y-0 group-focus-within:opacity-100">
                            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-fleet-ink transition hover:bg-blue-50 hover:text-fleet-primary" aria-label="View project">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-fleet-ink transition hover:bg-blue-50 hover:text-fleet-primary" aria-label="Edit project">
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
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
