<?php
// Static frontend page for fleet analytics and reporting.
$activePage = 'reports';
require_once __DIR__ . '/../../includes/data.php';
extract(fleetData('reports'));
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1180px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Fleet Reports</h1>
                <p class="mt-2 text-sm text-fleet-muted">Cost analysis and fleet utilization overview</p>
            </div>
            <select class="vehicle-form-control h-10 w-40">
                <option>All Time</option>
                <option>This Month</option>
                <option>This Quarter</option>
                <option>This Year</option>
            </select>
        </div>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <?php foreach ($summaryCards as $card): ?>
                <?php
                $toneClasses = match ($card['tone']) {
                    'amber' => 'bg-fleet-warning-soft text-fleet-warning-strong',
                    'green' => 'bg-fleet-success-soft text-fleet-success',
                    default => 'bg-fleet-primary-soft text-fleet-primary',
                };
                ?>
                <article class="interactive-card flex min-h-20 items-center gap-4 rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-extrabold <?= $toneClasses; ?>"><?= htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <div>
                        <p class="text-xs text-fleet-muted"><?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="mt-1 text-xl font-extrabold text-fleet-ink"><?= htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-2">
            <article class="interactive-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <h2 class="text-base font-extrabold text-fleet-ink">Maintenance Cost by Vehicle</h2>
                <div class="mt-8 flex h-64 items-center">
                    <div class="mr-3 w-20 text-right text-xs text-fleet-muted">UAJ 433X</div>
                    <div class="relative flex h-56 flex-1 flex-col justify-end border-b border-l border-fleet-line">
                        <div class="absolute inset-x-0 bottom-0 grid h-full grid-cols-4 text-xs text-fleet-muted">
                            <span class="border-r border-dashed border-fleet-line"></span>
                            <span class="border-r border-dashed border-fleet-line"></span>
                            <span class="border-r border-dashed border-fleet-line"></span>
                            <span></span>
                        </div>
                        <div class="report-chart-group relative z-10 mb-6 ml-0 h-32 w-[70%] rounded-r bg-fleet-sidebar">
                            <div class="report-tooltip right-4 top-1/2 -translate-y-1/2">
                                <p class="font-semibold text-fleet-ink">UAJ 433X</p>
                                <p class="mt-2 text-fleet-sidebar">value : UGX 4,200,000</p>
                            </div>
                        </div>
                        <div class="absolute -bottom-7 inset-x-0 grid grid-cols-4 text-center text-sm text-fleet-muted">
                            <span>0.0M</span>
                            <span>1.5M</span>
                            <span>3.0M</span>
                            <span>6.0M</span>
                        </div>
                    </div>
                </div>
            </article>

            <article class="interactive-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <h2 class="text-base font-extrabold text-fleet-ink">Cost by Maintenance Type</h2>
                <div class="mt-8 flex h-64 items-center justify-center">
                    <div class="relative flex items-center gap-3">
                        <span class="text-sm text-fleet-sidebar">repair</span>
                        <div class="h-px w-9 bg-fleet-sidebar"></div>
                        <div class="report-chart-group relative h-44 w-44 rounded-full bg-fleet-sidebar">
                            <div class="absolute inset-9 rounded-full bg-fleet-surface"></div>
                            <div class="report-tooltip left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-center">
                                <p class="font-semibold text-fleet-ink">repair</p>
                                <p class="mt-2 text-fleet-sidebar">UGX 4,200,000</p>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </section>

        <section class="interactive-card mt-6 rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
            <h2 class="text-base font-extrabold text-fleet-ink">Trips by Vehicle</h2>
            <div class="mt-8 h-64">
                <div class="relative h-56 border-b border-l border-fleet-line">
                    <div class="absolute inset-0 grid grid-rows-4 text-xs text-fleet-muted">
                        <span class="border-t border-dashed border-fleet-line"></span>
                        <span class="border-t border-dashed border-fleet-line"></span>
                        <span class="border-t border-dashed border-fleet-line"></span>
                        <span class="border-t border-dashed border-fleet-line"></span>
                    </div>
                    <div class="report-chart-group absolute bottom-0 left-[12%] h-full w-[80%] rounded-t bg-fleet-warning">
                        <div class="report-tooltip left-1/2 top-6 -translate-x-1/2 text-center">
                            <p class="font-semibold text-fleet-ink">UAJ 433X</p>
                            <p class="mt-2 text-fleet-sidebar">2 trips</p>
                        </div>
                    </div>
                    <div class="absolute -bottom-7 left-[50%] -translate-x-1/2 text-xs text-fleet-muted">UAJ 433X</div>
                    <div class="absolute -left-6 top-0 text-sm text-fleet-muted">2</div>
                    <div class="absolute -left-8 top-1/4 text-sm text-fleet-muted">1.5</div>
                    <div class="absolute -left-6 top-1/2 text-sm text-fleet-muted">1</div>
                    <div class="absolute -left-8 top-3/4 text-sm text-fleet-muted">0.5</div>
                    <div class="absolute -left-6 bottom-0 text-sm text-fleet-muted">0</div>
                </div>
            </div>
        </section>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
