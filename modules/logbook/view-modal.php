<div id="logbook-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
    <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full max-w-[900px] overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="logbook-view-modal-title">
        <div class="p-6" data-logbook-detail-sheet>
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-primary">Log Entry Details</p>
                    <h2 id="logbook-view-modal-title" class="mt-2 text-2xl font-extrabold text-fleet-ink" data-logbook-view-title>Vehicle log entry</h2>
                    <p class="mt-1 text-sm text-fleet-muted" data-logbook-view-subtitle>Complete trip information for the selected log entry.</p>
                </div>
                <button type="button" data-close-logbook-view-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close logbook details">&times;</button>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <div class="rounded-2xl border border-fleet-line bg-white p-5"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Trip Overview</p><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Date</dt><dd class="font-semibold text-fleet-ink" data-logbook-view-date>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Vehicle</dt><dd class="font-semibold text-fleet-ink" data-logbook-view-vehicle>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Driver</dt><dd class="font-semibold text-fleet-ink" data-logbook-view-driver>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Purpose</dt><dd class="font-semibold text-fleet-ink" data-logbook-view-purpose>-</dd></div></dl></div>
                <div class="rounded-2xl border border-fleet-line bg-white p-5"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Route and Distance</p><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-fleet-muted">From</dt><dd class="font-semibold text-fleet-ink" data-logbook-view-from>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">To</dt><dd class="font-semibold text-fleet-ink" data-logbook-view-to>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Distance</dt><dd class="font-semibold text-fleet-ink" data-logbook-view-km>-</dd></div></dl></div>
                <div class="rounded-2xl border border-fleet-line bg-white p-5"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Odometer</p><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Start</dt><dd class="font-semibold text-fleet-ink" data-logbook-view-odo-start>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">End</dt><dd class="font-semibold text-fleet-ink" data-logbook-view-odo-end>-</dd></div></dl></div>
                <div class="rounded-2xl border border-fleet-line bg-white p-5"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Fuel and Remarks</p><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Fuel (L)</dt><dd class="font-semibold text-fleet-ink" data-logbook-view-fuel>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Fuel Cost</dt><dd class="font-semibold text-fleet-ink" data-logbook-view-cost>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Remarks</dt><dd class="font-semibold text-fleet-ink" data-logbook-view-remarks>-</dd></div></dl></div>
            </div>
            <div class="mt-6 flex justify-end gap-3 border-t border-fleet-line-soft pt-5">
                <button type="button" data-print-logbook-view class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted"><span>P</span><span>Print Log Entry</span></button>
                <button type="button" data-close-logbook-view-modal class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Close</button>
            </div>
        </div>
    </div>
</div>
