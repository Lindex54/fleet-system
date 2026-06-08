<div id="driver-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
    <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full max-w-[900px] overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="driver-view-modal-title">
        <div class="p-6" data-driver-detail-sheet>
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-primary">Driver Profile</p>
                    <h2 id="driver-view-modal-title" class="mt-2 text-2xl font-extrabold text-fleet-ink" data-driver-view-name>Driver details</h2>
                    <p class="mt-1 text-sm text-fleet-muted" data-driver-view-subtitle>Full record information for the selected driver.</p>
                </div>
                <button type="button" data-close-driver-view-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close driver details">&times;</button>
            </div>

            <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
                <section class="rounded-2xl border border-fleet-line bg-fleet-surface-muted p-5">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-3xl bg-fleet-primary-soft text-3xl font-extrabold text-fleet-primary" data-driver-view-photo-fallback>
                            <span data-driver-view-initial>D</span>
                        </div>
                        <img src="" alt="Driver profile" class="hidden h-28 w-28 rounded-3xl object-cover ring-2 ring-fleet-primary-soft" data-driver-view-photo>
                        <p class="mt-4 text-xl font-extrabold text-fleet-ink" data-driver-view-full-name>Driver Name</p>
                        <p class="mt-1 text-sm text-fleet-muted" data-driver-view-department>Department</p>
                        <span class="mt-4 inline-flex rounded-lg border px-3 py-1 text-xs font-semibold" data-driver-view-status>Active</span>
                    </div>

                    <div class="mt-6 space-y-3 text-sm">
                        <div class="rounded-xl border border-fleet-line bg-white px-4 py-3">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Assigned Vehicle</p>
                            <p class="mt-1 font-semibold text-fleet-ink" data-driver-view-assigned-vehicle>-</p>
                        </div>
                        <div class="rounded-xl border border-fleet-line bg-white px-4 py-3">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Other Vehicles</p>
                            <p class="mt-1 font-semibold text-fleet-ink" data-driver-view-other-vehicles>-</p>
                        </div>
                        <div class="rounded-xl border border-fleet-line bg-white px-4 py-3">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Uploads</p>
                            <div class="mt-2 space-y-2">
                                <a href="#" target="_blank" rel="noopener noreferrer" class="hidden text-sm font-semibold text-fleet-primary hover:underline" data-driver-view-driver-photo-link>Driver Photo</a>
                                <a href="#" target="_blank" rel="noopener noreferrer" class="hidden text-sm font-semibold text-fleet-primary hover:underline" data-driver-view-national-id-photo-link>National ID Photo</a>
                                <a href="#" target="_blank" rel="noopener noreferrer" class="hidden text-sm font-semibold text-fleet-primary hover:underline" data-driver-view-license-scan-link>Driving License Scan</a>
                                <p class="text-sm text-fleet-muted" data-driver-view-no-uploads>No uploaded files for this driver.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="space-y-5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-fleet-line bg-white p-5">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Personal Information</p>
                            <dl class="mt-4 space-y-3 text-sm">
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-fleet-muted">Employee ID</dt>
                                    <dd class="text-right font-semibold text-fleet-ink" data-driver-view-employee-id>-</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-fleet-muted">Gender</dt>
                                    <dd class="text-right font-semibold text-fleet-ink" data-driver-view-gender>-</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-fleet-muted">National ID / NIN</dt>
                                    <dd class="text-right font-semibold text-fleet-ink" data-driver-view-national-id-number>-</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-fleet-muted">Department</dt>
                                    <dd class="text-right font-semibold text-fleet-ink" data-driver-view-department-detail>-</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-2xl border border-fleet-line bg-white p-5">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Contact Information</p>
                            <dl class="mt-4 space-y-3 text-sm">
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-fleet-muted">Phone</dt>
                                    <dd class="text-right font-semibold text-fleet-ink" data-driver-view-phone>-</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-fleet-muted">Email</dt>
                                    <dd class="break-all text-right font-semibold text-fleet-ink" data-driver-view-email>-</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-fleet-muted">Status</dt>
                                    <dd class="text-right font-semibold text-fleet-ink" data-driver-view-status-label>-</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-fleet-line bg-white p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">License Details</p>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div class="rounded-xl border border-fleet-line bg-fleet-surface-muted px-4 py-3">
                                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">License Number</p>
                                <p class="mt-1 text-base font-extrabold text-fleet-ink" data-driver-view-license-number>-</p>
                            </div>
                            <div class="rounded-xl border border-fleet-line bg-fleet-surface-muted px-4 py-3">
                                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">License Class(es)</p>
                                <p class="mt-1 text-base font-extrabold text-fleet-ink" data-driver-view-license-classes>-</p>
                            </div>
                            <div class="rounded-xl border border-fleet-line bg-fleet-surface-muted px-4 py-3">
                                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Issue Date</p>
                                <p class="mt-1 text-base font-extrabold text-fleet-ink" data-driver-view-license-issue-date>-</p>
                            </div>
                            <div class="rounded-xl border border-fleet-line bg-fleet-surface-muted px-4 py-3">
                                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Expiry Date</p>
                                <p class="mt-1 text-base font-extrabold text-fleet-ink" data-driver-view-license-expiry>-</p>
                            </div>
                            <div class="rounded-xl border border-fleet-line bg-fleet-surface-muted px-4 py-3">
                                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Permit Days Left</p>
                                <p class="mt-1 text-base font-extrabold text-fleet-ink" data-driver-view-license-days-left>-</p>
                            </div>
                            <div class="rounded-xl border border-fleet-line bg-fleet-surface-muted px-4 py-3 md:col-span-2">
                                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Issuing Authority</p>
                                <p class="mt-1 text-base font-extrabold text-fleet-ink" data-driver-view-license-issuing-authority>-</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-3 border-t border-fleet-line-soft pt-5">
                <button type="button" data-print-driver-view class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">
                    <span>P</span>
                    <span>Print Driver Details</span>
                </button>
                <button type="button" data-close-driver-view-modal class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Close</button>
            </div>
        </div>
    </div>
</div>
