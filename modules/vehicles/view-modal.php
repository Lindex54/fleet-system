<div id="vehicle-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
    <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full max-w-[860px] overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="vehicle-view-modal-title">
        <div class="p-6" data-vehicle-detail-sheet>
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-primary">Vehicle Details</p>
                    <h2 id="vehicle-view-modal-title" class="mt-2 text-2xl font-extrabold text-fleet-ink" data-vehicle-view-name>Vehicle details</h2>
                    <p class="mt-1 text-sm text-fleet-muted" data-vehicle-view-subtitle>Full fleet register details for the selected vehicle.</p>
                </div>
                <button type="button" data-close-vehicle-view-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close vehicle details">&times;</button>
            </div>
            <div class="mb-5 flex justify-center">
                <div class="hidden w-full max-w-[320px] overflow-hidden rounded-2xl border border-fleet-line bg-fleet-surface-muted p-3 shadow-sm" data-vehicle-view-image-wrap>
                    <img src="" alt="Vehicle image" class="h-44 w-full rounded-xl object-contain bg-white" data-vehicle-view-image>
                </div>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <div class="rounded-2xl border border-fleet-line bg-white p-5">
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Identity</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-fleet-muted">Registration</dt><dd class="font-semibold text-fleet-ink" data-vehicle-view-registration>-</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-fleet-muted">Make</dt><dd class="font-semibold text-fleet-ink" data-vehicle-view-make>-</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-fleet-muted">Model</dt><dd class="font-semibold text-fleet-ink" data-vehicle-view-model>-</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-fleet-muted">Year</dt><dd class="font-semibold text-fleet-ink" data-vehicle-view-year>-</dd></div>
                    </dl>
                </div>
                <div class="rounded-2xl border border-fleet-line bg-white p-5">
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Operational Details</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-fleet-muted">Type</dt><dd class="font-semibold text-fleet-ink" data-vehicle-view-type>-</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-fleet-muted">Fuel</dt><dd class="font-semibold text-fleet-ink" data-vehicle-view-fuel>-</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-fleet-muted">Department</dt><dd class="font-semibold text-fleet-ink" data-vehicle-view-department>-</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-fleet-muted">Status</dt><dd class="font-semibold text-fleet-ink" data-vehicle-view-status>-</dd></div>
                    </dl>
                </div>
                <div class="rounded-2xl border border-fleet-line bg-white p-5">
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Compliance</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-fleet-muted">Current Mileage</dt><dd class="font-semibold text-fleet-ink" data-vehicle-view-mileage>-</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-fleet-muted">Insurance Expiry</dt><dd class="font-semibold text-fleet-ink" data-vehicle-view-insurance>-</dd></div>
                    </dl>
                </div>
                <div class="rounded-2xl border border-fleet-line bg-white p-5">
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Repairs Done</p>
                    <p class="mt-4 text-sm leading-6 text-fleet-ink" data-vehicle-view-repairs>No repairs recorded.</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3 border-t border-fleet-line-soft pt-5">
                <button type="button" data-print-vehicle-view class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted"><span>P</span><span>Print Vehicle Details</span></button>
                <button type="button" data-close-vehicle-view-modal class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Close</button>
            </div>
        </div>
    </div>
</div>
