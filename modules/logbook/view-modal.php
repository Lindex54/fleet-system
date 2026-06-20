<div id="logbook-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
    <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full max-w-[980px] overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="logbook-view-modal-title">
        <div class="p-6" data-logbook-detail-sheet>
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-primary">Log Entry Details</p>
                    <h2 id="logbook-view-modal-title" class="mt-2 text-2xl font-extrabold text-fleet-ink" data-logbook-view-title>Vehicle log entry</h2>
                    <p class="mt-1 text-sm text-fleet-muted" data-logbook-view-subtitle>Complete trip information for the selected log entry.</p>
                </div>
                <button type="button" data-close-logbook-view-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close logbook details">&times;</button>
            </div>
            <div class="overflow-x-auto rounded-2xl border border-fleet-line bg-white">
                <table class="w-full min-w-[860px] border-collapse text-sm">
                    <tbody>
                        <tr class="bg-slate-100">
                            <th colspan="4" class="border-b border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Trip Details</th>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="w-[18%] border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Date</th>
                            <td class="w-[32%] border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-logbook-view-date>-</td>
                            <th class="w-[18%] border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Vehicle</th>
                            <td class="w-[32%] px-4 py-3 font-semibold text-fleet-ink" data-logbook-view-vehicle>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Driver</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-logbook-view-driver>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Purpose</th>
                            <td class="px-4 py-3 text-fleet-ink" data-logbook-view-purpose>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">From</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-logbook-view-from>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">To</th>
                            <td class="px-4 py-3 text-fleet-ink" data-logbook-view-to>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Distance</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-logbook-view-km>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Fuel (L)</th>
                            <td class="px-4 py-3 text-fleet-ink" data-logbook-view-fuel>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Odometer Start</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-logbook-view-odo-start>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Odometer End</th>
                            <td class="px-4 py-3 text-fleet-ink" data-logbook-view-odo-end>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Fuel Cost</th>
                            <td colspan="3" class="px-4 py-3 text-fleet-ink" data-logbook-view-cost>-</td>
                        </tr>
                        <tr class="align-top">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Remarks</th>
                            <td colspan="3" class="px-4 py-3 leading-6 text-fleet-ink" data-logbook-view-remarks>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-6 flex justify-end gap-3 border-t border-fleet-line-soft pt-5">
                <button type="button" data-print-logbook-view class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted"><span>P</span><span>Print Log Entry</span></button>
                <button type="button" data-close-logbook-view-modal class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Close</button>
            </div>
        </div>
    </div>
</div>
