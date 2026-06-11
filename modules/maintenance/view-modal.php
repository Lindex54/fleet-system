<div id="maintenance-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
    <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" style="max-width: 760px;" role="dialog" aria-modal="true" aria-labelledby="maintenance-view-modal-title">
        <div class="p-6" data-maintenance-detail-sheet>
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-primary">Maintenance Record</p>
                    <h2 id="maintenance-view-modal-title" class="mt-2 text-2xl font-extrabold text-fleet-ink" data-maintenance-view-title>Maintenance details</h2>
                    <p class="mt-1 text-sm text-fleet-muted" data-maintenance-view-subtitle>Full record information for the selected maintenance entry.</p>
                </div>
                <button type="button" data-close-maintenance-view-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close maintenance details">&times;</button>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <div class="rounded-2xl border border-fleet-line bg-white p-5"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Overview</p><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Vehicle</dt><dd class="font-semibold text-fleet-ink" data-maintenance-view-vehicle>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Type</dt><dd class="font-semibold text-fleet-ink" data-maintenance-view-type>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Status</dt><dd class="font-semibold text-fleet-ink" data-maintenance-view-status>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Provider</dt><dd class="font-semibold text-fleet-ink" data-maintenance-view-provider>-</dd></div></dl></div>
                <div class="rounded-2xl border border-fleet-line bg-white p-5"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Dates and Cost</p><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Date Reported</dt><dd class="font-semibold text-fleet-ink" data-maintenance-view-date-reported>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Date Completed</dt><dd class="font-semibold text-fleet-ink" data-maintenance-view-date-completed>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Mileage at Service</dt><dd class="font-semibold text-fleet-ink" data-maintenance-view-mileage>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Total Cost</dt><dd class="font-semibold text-fleet-ink" data-maintenance-view-cost>-</dd></div></dl></div>
                <div class="rounded-2xl border border-fleet-line bg-white p-5 md:col-span-2"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Description</p><p class="mt-4 text-sm leading-6 text-fleet-ink" data-maintenance-view-description>-</p></div>
                <div class="rounded-2xl border border-fleet-line bg-white p-5"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Parts Replaced</p><p class="mt-4 text-sm leading-6 text-fleet-ink" data-maintenance-view-parts>-</p></div>
                <div class="rounded-2xl border border-fleet-line bg-white p-5"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Invoice / Remarks</p><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Invoice Number</dt><dd class="font-semibold text-fleet-ink" data-maintenance-view-invoice>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Remarks</dt><dd class="font-semibold text-fleet-ink" data-maintenance-view-remarks>-</dd></div></dl></div>
            </div>
            <div class="mt-6 flex justify-end gap-3 border-t border-fleet-line-soft pt-5">
                <button type="button" data-print-maintenance-view class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted"><span>P</span><span>Print Record Details</span></button>
                <button type="button" data-close-maintenance-view-modal class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Close</button>
            </div>
        </div>
    </div>
</div>
