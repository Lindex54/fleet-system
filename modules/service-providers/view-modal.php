<div id="provider-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
    <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" style="max-width: 760px;" role="dialog" aria-modal="true" aria-labelledby="provider-view-modal-title">
        <div class="p-6" data-provider-detail-sheet>
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-primary">Provider Details</p>
                    <h2 id="provider-view-modal-title" class="mt-2 text-2xl font-extrabold text-fleet-ink" data-provider-view-name>Service provider</h2>
                    <p class="mt-1 text-sm text-fleet-muted" data-provider-view-subtitle>Full service provider profile for the selected record.</p>
                </div>
                <button type="button" data-close-provider-view-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close provider details">&times;</button>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <div class="rounded-2xl border border-fleet-line bg-white p-5"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Company</p><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Name</dt><dd class="font-semibold text-fleet-ink" data-provider-view-company>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Specialization</dt><dd class="font-semibold text-fleet-ink" data-provider-view-specialty>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Town</dt><dd class="font-semibold text-fleet-ink" data-provider-view-town>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Status</dt><dd class="font-semibold text-fleet-ink" data-provider-view-status>-</dd></div></dl></div>
                <div class="rounded-2xl border border-fleet-line bg-white p-5"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Contacts</p><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Contact Person</dt><dd class="font-semibold text-fleet-ink" data-provider-view-contact-person>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Phone</dt><dd class="font-semibold text-fleet-ink" data-provider-view-phone>-</dd></div><div class="flex justify-between gap-4"><dt class="text-fleet-muted">Email</dt><dd class="break-all text-right font-semibold text-fleet-ink" data-provider-view-email>-</dd></div></dl></div>
            </div>
            <div class="mt-6 flex justify-end gap-3 border-t border-fleet-line-soft pt-5">
                <button type="button" data-print-provider-view class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted"><span>P</span><span>Print Provider Details</span></button>
                <button type="button" data-close-provider-view-modal class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Close</button>
            </div>
        </div>
    </div>
</div>
