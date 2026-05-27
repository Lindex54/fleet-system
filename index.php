<?php
// Public front page for normal visitors.
// The operational dashboard is kept separate for authorized system users.
$activePage = 'home';
include __DIR__ . '/includes/header.php';

$loginUrl = ($basePath ?: '') . '/login.php';
$dashboardUrl = ($basePath ?: '') . '/modules/dashboard/index.php';
?>
<main class="min-h-screen bg-fleet-canvas">
    <header class="border-b border-fleet-line bg-fleet-surface">
        <div class="mx-auto flex max-w-[1320px] flex-col gap-4 px-4 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <a href="<?= htmlspecialchars(($basePath ?: '') . '/index.php', ENT_QUOTES, 'UTF-8'); ?>" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-fleet-sidebar text-sm font-extrabold text-white">BU</span>
                <span>
                    <span class="block text-base font-extrabold text-fleet-ink">BUESMIS</span>
                    <span class="block text-xs text-fleet-muted">Busitema University Estates &amp; Fleet System</span>
                </span>
            </a>

            <nav class="flex flex-wrap items-center gap-3 text-sm font-semibold">
                <a href="#services" class="text-fleet-muted hover:text-fleet-primary">Services</a>
                <a href="#workflow" class="text-fleet-muted hover:text-fleet-primary">Workflow</a>
                <a href="#access" class="text-fleet-muted hover:text-fleet-primary">Access</a>
                <a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 items-center rounded-lg border border-fleet-line bg-fleet-surface px-4 text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">Staff Login</a>
            </nav>
        </div>
    </header>

    <section class="mx-auto grid max-w-[1320px] gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[1fr_1fr] lg:px-8">
        <div class="flex min-h-[420px] flex-col justify-center">
            <p class="mb-3 text-sm font-extrabold uppercase tracking-wide text-fleet-primary">University Operations Platform</p>
            <h1 class="text-4xl font-extrabold tracking-normal text-fleet-ink">Manage fleet movement, maintenance, inspections, and estates work in one system.</h1>
            <p class="mt-5 max-w-3xl text-base leading-7 text-fleet-muted">
                BUESMIS helps Busitema University organize vehicle records, driver assignments, official logbook entries, repair history, inspection reports, communications, and estates project tracking.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-11 items-center rounded-lg bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">Staff Login</a>
                <a href="#services" class="inline-flex h-11 items-center rounded-lg border border-fleet-line bg-fleet-surface px-5 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">View System Areas</a>
            </div>
        </div>

        <div class="flex items-center">
            <div class="w-full overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
                <div class="border-b border-fleet-line bg-fleet-surface-muted px-5 py-4">
                    <p class="text-sm font-extrabold text-fleet-sidebar">System Overview</p>
                    <p class="mt-1 text-xs text-fleet-muted">Public information only. Operational data is restricted.</p>
                </div>
                <div class="grid gap-4 p-5 sm:grid-cols-2">
                    <article class="rounded-lg border border-blue-200 bg-blue-50 p-5">
                        <p class="text-sm font-extrabold text-fleet-primary">Fleet Registry</p>
                        <p class="mt-2 text-sm text-fleet-muted">Central records for university vehicles, departments, and availability.</p>
                    </article>
                    <article class="rounded-lg border border-green-200 bg-fleet-success-soft p-5">
                        <p class="text-sm font-extrabold text-fleet-success">Logbook</p>
                        <p class="mt-2 text-sm text-fleet-muted">Official vehicle movement, mileage, and fuel tracking.</p>
                    </article>
                    <article class="rounded-lg border border-orange-200 bg-fleet-warning-soft p-5">
                        <p class="text-sm font-extrabold text-fleet-warning-strong">Maintenance</p>
                        <p class="mt-2 text-sm text-fleet-muted">Service requests, repair costs, invoices, and provider history.</p>
                    </article>
                    <article class="rounded-lg border border-purple-200 bg-purple-50 p-5">
                        <p class="text-sm font-extrabold text-purple-700">Estates Works</p>
                        <p class="mt-2 text-sm text-fleet-muted">Infrastructure projects, budgets, contractors, and progress updates.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="border-y border-fleet-line bg-fleet-surface">
        <div class="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 max-w-3xl">
                <h2 class="text-2xl font-extrabold text-fleet-ink">What The System Supports</h2>
                <p class="mt-2 text-sm leading-6 text-fleet-muted">The platform is built around the daily records transport and estates teams need to keep operations traceable and reportable.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <article class="rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-sm">
                    <h3 class="text-base font-extrabold text-fleet-ink">Vehicles &amp; Drivers</h3>
                    <p class="mt-3 text-sm leading-6 text-fleet-muted">Register vehicles, assign drivers, track departments, and keep license details organized.</p>
                </article>
                <article class="rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-sm">
                    <h3 class="text-base font-extrabold text-fleet-ink">Trips &amp; Fuel</h3>
                    <p class="mt-3 text-sm leading-6 text-fleet-muted">Record official trips, destinations, odometer readings, fuel litres, and costs.</p>
                </article>
                <article class="rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-sm">
                    <h3 class="text-base font-extrabold text-fleet-ink">Inspections</h3>
                    <p class="mt-3 text-sm leading-6 text-fleet-muted">Capture pre-inspection findings and post-inspection repair/payment details.</p>
                </article>
                <article class="rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-sm">
                    <h3 class="text-base font-extrabold text-fleet-ink">Reports</h3>
                    <p class="mt-3 text-sm leading-6 text-fleet-muted">Generate summaries for maintenance costs, fuel use, trips, and estates progress.</p>
                </article>
            </div>
        </div>
    </section>

    <section id="workflow" class="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-3">
            <article class="rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-fleet-primary-soft text-sm font-extrabold text-fleet-primary">1</span>
                <h2 class="mt-4 text-lg font-extrabold text-fleet-ink">Record</h2>
                <p class="mt-2 text-sm leading-6 text-fleet-muted">Officers enter vehicles, drivers, trips, maintenance requests, inspections, and project updates.</p>
            </article>
            <article class="rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-fleet-warning-soft text-sm font-extrabold text-fleet-warning-strong">2</span>
                <h2 class="mt-4 text-lg font-extrabold text-fleet-ink">Track</h2>
                <p class="mt-2 text-sm leading-6 text-fleet-muted">The system links records to vehicles, departments, drivers, service providers, and contractors.</p>
            </article>
            <article class="rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-fleet-success-soft text-sm font-extrabold text-fleet-success">3</span>
                <h2 class="mt-4 text-lg font-extrabold text-fleet-ink">Report</h2>
                <p class="mt-2 text-sm leading-6 text-fleet-muted">Authorized users review dashboard totals, histories, alerts, and operational summaries.</p>
            </article>
        </div>
    </section>

    <section id="access" class="bg-fleet-sidebar text-white">
        <div class="mx-auto grid max-w-[1320px] gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[1fr_1fr] lg:px-8">
            <div>
                <h2 class="text-2xl font-extrabold">Dashboard Access Is Restricted</h2>
                <p class="mt-3 text-sm leading-6 text-fleet-sidebar-text">
                    Normal visitors can view this public page. The dashboard and operational modules are intended for approved university staff with assigned system roles.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 lg:justify-end">
                <a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-11 items-center rounded-lg bg-white px-5 text-sm font-semibold text-fleet-sidebar shadow-sm hover:bg-slate-100">Go To Login</a>
                <a href="<?= htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-11 items-center rounded-lg border border-white/10 bg-fleet-sidebar-active px-5 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-soft">Staff Dashboard</a>
            </div>
        </div>
    </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
