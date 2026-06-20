<div id="maintenance-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
    <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" style="max-width: 980px;" role="dialog" aria-modal="true" aria-labelledby="maintenance-view-modal-title">
        <div class="p-6" data-maintenance-detail-sheet>
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-primary">Maintenance Record</p>
                    <h2 id="maintenance-view-modal-title" class="mt-2 text-2xl font-extrabold text-fleet-ink" data-maintenance-view-title>Maintenance details</h2>
                    <p class="mt-1 text-sm text-fleet-muted" data-maintenance-view-subtitle>Full record information for the selected maintenance entry.</p>
                </div>
                <button type="button" data-close-maintenance-view-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close maintenance details">&times;</button>
            </div>
            <div class="overflow-x-auto rounded-2xl border border-fleet-line bg-white">
                <table class="w-full min-w-[860px] border-collapse text-sm">
                    <tbody>
                        <tr class="bg-slate-100">
                            <th colspan="4" class="border-b border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Maintenance Details</th>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="w-[18%] border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Vehicle</th>
                            <td class="w-[32%] border-r border-fleet-line-soft px-4 py-3 font-semibold text-fleet-ink" data-maintenance-view-vehicle>-</td>
                            <th class="w-[18%] border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Type</th>
                            <td class="w-[32%] px-4 py-3 text-fleet-ink" data-maintenance-view-type>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Status</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 font-semibold text-fleet-ink" data-maintenance-view-status>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Provider</th>
                            <td class="px-4 py-3 text-fleet-ink" data-maintenance-view-provider>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Date Reported</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-maintenance-view-date-reported>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Date Completed</th>
                            <td class="px-4 py-3 text-fleet-ink" data-maintenance-view-date-completed>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Mileage at Service</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-maintenance-view-mileage>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Total Cost</th>
                            <td class="px-4 py-3 font-semibold text-fleet-ink" data-maintenance-view-cost>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Invoice Number</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-maintenance-view-invoice>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Remarks</th>
                            <td class="px-4 py-3 text-fleet-ink" data-maintenance-view-remarks>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft align-top">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Description</th>
                            <td colspan="3" class="px-4 py-3 leading-6 text-fleet-ink" data-maintenance-view-description>-</td>
                        </tr>
                        <tr class="align-top">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Parts Replaced</th>
                            <td colspan="3" class="px-4 py-3 leading-6 text-fleet-ink" data-maintenance-view-parts>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-6 flex justify-end gap-3 border-t border-fleet-line-soft pt-5">
                <button type="button" data-print-maintenance-view class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted"><span>P</span><span>Print Record Details</span></button>
                <button type="button" data-close-maintenance-view-modal class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Close</button>
            </div>
        </div>
    </div>
</div>
