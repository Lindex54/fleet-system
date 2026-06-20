<div id="post-inspection-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
    <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" style="max-width: 980px;" role="dialog" aria-modal="true" aria-labelledby="post-inspection-view-modal-title">
        <div class="p-6" data-post-inspection-detail-sheet>
            <div class="mb-6 flex items-start justify-between gap-4"><div><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-primary">Post-Inspection Details</p><h2 id="post-inspection-view-modal-title" class="mt-2 text-2xl font-extrabold text-fleet-ink" data-post-inspection-view-title>Post-inspection report</h2><p class="mt-1 text-sm text-fleet-muted" data-post-inspection-view-subtitle>Full post-inspection information for the selected report.</p></div><button type="button" data-close-post-inspection-view-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close post-inspection details">&times;</button></div>
            <div class="overflow-x-auto rounded-2xl border border-fleet-line bg-white">
                <table class="w-full min-w-[860px] border-collapse text-sm">
                    <tbody>
                        <tr class="bg-slate-100">
                            <th colspan="4" class="border-b border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Report Details</th>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="w-[18%] border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Invoice No.</th>
                            <td class="w-[32%] border-r border-fleet-line-soft px-4 py-3 font-semibold text-fleet-ink" data-post-inspection-view-invoice>-</td>
                            <th class="w-[18%] border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Post Invoice No.</th>
                            <td class="w-[32%] px-4 py-3 font-semibold text-fleet-ink" data-post-inspection-view-post-invoice>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Date</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-post-inspection-view-date>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Vehicle</th>
                            <td class="px-4 py-3 font-semibold text-fleet-ink" data-post-inspection-view-vehicle>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Make / Model</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-post-inspection-view-make-model>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Inspector</th>
                            <td class="px-4 py-3 text-fleet-ink" data-post-inspection-view-inspector>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Overall Status</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 font-semibold text-fleet-ink" data-post-inspection-view-overall>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Repair Cost</th>
                            <td class="px-4 py-3 font-semibold text-fleet-ink" data-post-inspection-view-repair-cost>-</td>
                        </tr>

                        <tr class="bg-slate-100">
                            <th colspan="4" class="border-y border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Repair Summary</th>
                        </tr>
                        <tr class="border-b border-fleet-line-soft align-top">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Works Done</th>
                            <td colspan="3" class="px-4 py-3 leading-6 text-fleet-ink" data-post-inspection-view-works-done>-</td>
                        </tr>
                        <tr class="align-top">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Recommendation</th>
                            <td colspan="3" class="px-4 py-3 leading-6 text-fleet-ink" data-post-inspection-view-recommendation>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-6 flex justify-end gap-3 border-t border-fleet-line-soft pt-5"><button type="button" data-print-post-inspection-view class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted"><span>P</span><span>Print Report Details</span></button><button type="button" data-close-post-inspection-view-modal class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Close</button></div>
        </div>
    </div>
</div>
