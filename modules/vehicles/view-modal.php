<div id="vehicle-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
    <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full max-w-[980px] overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="vehicle-view-modal-title">
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
                <div class="hidden h-36 w-48 overflow-hidden rounded-2xl border border-fleet-line bg-fleet-surface-muted shadow-sm" data-vehicle-view-image-wrap>
                    <img src="" alt="Vehicle image" class="h-full w-full object-cover" data-vehicle-view-image>
                </div>
            </div>
            <div class="overflow-x-auto rounded-2xl border border-fleet-line bg-white">
                <table class="w-full min-w-[860px] border-collapse text-sm">
                    <tbody>
                        <tr class="bg-slate-100">
                            <th colspan="4" class="border-b border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Vehicle Profile</th>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="w-[18%] border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Registration</th>
                            <td class="w-[32%] border-r border-fleet-line-soft px-4 py-3 font-semibold text-fleet-ink" data-vehicle-view-registration>-</td>
                            <th class="w-[18%] border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Make</th>
                            <td class="w-[32%] px-4 py-3 text-fleet-ink" data-vehicle-view-make>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Model</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-vehicle-view-model>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Year</th>
                            <td class="px-4 py-3 text-fleet-ink" data-vehicle-view-year>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Type</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-vehicle-view-type>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Fuel</th>
                            <td class="px-4 py-3 text-fleet-ink" data-vehicle-view-fuel>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Department</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-vehicle-view-department>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Status</th>
                            <td class="px-4 py-3 font-semibold text-fleet-ink" data-vehicle-view-status>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Current Mileage</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-vehicle-view-mileage>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Insurance Expiry</th>
                            <td class="px-4 py-3 text-fleet-ink" data-vehicle-view-insurance>-</td>
                        </tr>
                        <tr class="align-top">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Repairs Done</th>
                            <td colspan="3" class="px-4 py-3 leading-6 text-fleet-ink" data-vehicle-view-repairs>No repairs recorded.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-6 flex justify-end gap-3 border-t border-fleet-line-soft pt-5">
                <button type="button" data-print-vehicle-view class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted"><span>P</span><span>Print Vehicle Details</span></button>
                <button type="button" data-close-vehicle-view-modal class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Close</button>
            </div>
        </div>
    </div>
</div>
