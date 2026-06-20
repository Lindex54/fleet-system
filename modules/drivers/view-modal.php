<div id="driver-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
    <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full max-w-[980px] overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="driver-view-modal-title">
        <div class="p-6" data-driver-detail-sheet>
            <div class="mb-6 flex items-start justify-between gap-4" data-print-hide-driver-header>
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-primary">Driver Profile</p>
                    <h2 id="driver-view-modal-title" class="mt-2 text-2xl font-extrabold text-fleet-ink" data-driver-view-name>Driver details</h2>
                    <p class="mt-1 text-sm text-fleet-muted">Full record information for the selected driver.</p>
                </div>
                <button type="button" data-close-driver-view-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close driver details">&times;</button>
            </div>

            <div class="mb-5 flex items-start justify-between gap-6 border-b border-fleet-line-soft pb-5">
                <div class="min-w-0 flex-1">
                    <p class="text-xl font-extrabold text-fleet-ink" data-driver-view-full-name>Driver Name</p>
                    <p class="mt-1 text-sm text-fleet-muted" data-driver-view-subtitle>License Number - Assigned Vehicle</p>
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <p class="text-sm text-fleet-muted" data-driver-view-department>Department</p>
                        <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold" data-driver-view-status>Active</span>
                    </div>
                </div>
                <div class="flex h-28 w-24 shrink-0 items-center justify-center overflow-hidden rounded-md border-4 border-white bg-slate-100 shadow-sm ring-2 ring-fleet-primary-soft" data-driver-view-photo-shell>
                    <div class="hidden h-full w-full" data-driver-view-photo-fallback aria-hidden="true"></div>
                    <img src="" alt="Driver profile" class="hidden h-full w-full object-cover" data-driver-view-photo>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-fleet-line bg-white">
                <table class="w-full min-w-[860px] border-collapse text-sm">
                    <tbody>
                        <tr class="bg-slate-100">
                            <th colspan="4" class="border-b border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Driver Details</th>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="w-[18%] border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Employee ID</th>
                            <td class="w-[32%] border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-driver-view-employee-id>-</td>
                            <th class="w-[18%] border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Gender</th>
                            <td class="w-[32%] px-4 py-3 text-fleet-ink" data-driver-view-gender>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">National ID / NIN</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-driver-view-national-id-number>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Department</th>
                            <td class="px-4 py-3 text-fleet-ink" data-driver-view-department-detail>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Phone</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-driver-view-phone>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Email</th>
                            <td class="px-4 py-3 break-all text-fleet-ink" data-driver-view-email>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Assigned Vehicle</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-driver-view-assigned-vehicle>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Other Vehicles</th>
                            <td class="px-4 py-3 text-fleet-ink" data-driver-view-other-vehicles>-</td>
                        </tr>

                        <tr class="bg-slate-100">
                            <th colspan="4" class="border-y border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">License Details</th>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">License Number</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-driver-view-license-number>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">License Class(es)</th>
                            <td class="px-4 py-3 text-fleet-ink" data-driver-view-license-classes>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Issue Date</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-driver-view-license-issue-date>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Expiry Date</th>
                            <td class="px-4 py-3 text-fleet-ink" data-driver-view-license-expiry>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Permit Days Left</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-driver-view-license-days-left>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Issuing Authority</th>
                            <td class="px-4 py-3 text-fleet-ink" data-driver-view-license-issuing-authority>-</td>
                        </tr>

                        <tr class="bg-slate-100" data-print-hide-uploads>
                            <th colspan="4" class="border-y border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Uploads</th>
                        </tr>
                        <tr class="border-b border-fleet-line-soft" data-print-hide-uploads>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Driver Photo</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink"><a href="#" target="_blank" rel="noopener noreferrer" class="hidden font-semibold text-fleet-primary hover:underline" data-driver-view-driver-photo-link>Driver Photo</a><span class="text-fleet-muted" data-driver-view-driver-photo-empty>-</span></td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">National ID Photo</th>
                            <td class="px-4 py-3 text-fleet-ink"><a href="#" target="_blank" rel="noopener noreferrer" class="hidden font-semibold text-fleet-primary hover:underline" data-driver-view-national-id-photo-link>National ID Photo</a><span class="text-fleet-muted" data-driver-view-national-id-photo-empty>-</span></td>
                        </tr>
                        <tr data-print-hide-uploads>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Driving License Scan</th>
                            <td colspan="3" class="px-4 py-3 text-fleet-ink"><a href="#" target="_blank" rel="noopener noreferrer" class="hidden font-semibold text-fleet-primary hover:underline" data-driver-view-license-scan-link>Driving License Scan</a><span class="text-fleet-muted" data-driver-view-license-scan-empty>-</span><p class="mt-2 text-sm text-fleet-muted" data-driver-view-no-uploads>No uploaded files for this driver.</p></td>
                        </tr>
                    </tbody>
                </table>
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
