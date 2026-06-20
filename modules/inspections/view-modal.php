<div id="pre-inspection-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
    <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" style="max-width: 980px;" role="dialog" aria-modal="true" aria-labelledby="pre-inspection-view-modal-title">
        <div class="p-6" data-pre-inspection-detail-sheet>
            <div class="mb-6 flex items-start justify-between gap-4"><div><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-primary">Pre-Inspection Details</p><h2 id="pre-inspection-view-modal-title" class="mt-2 text-2xl font-extrabold text-fleet-ink" data-pre-inspection-view-title>Pre-inspection report</h2><p class="mt-1 text-sm text-fleet-muted" data-pre-inspection-view-subtitle>Full pre-inspection information for the selected report.</p></div><button type="button" data-close-pre-inspection-view-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close pre-inspection details">&times;</button></div>
            <div class="overflow-x-auto rounded-2xl border border-fleet-line bg-white">
                <table class="w-full min-w-[860px] border-collapse text-sm">
                    <tbody>
                        <tr class="bg-slate-100">
                            <th colspan="4" class="border-b border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Report Details</th>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="w-[18%] border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Invoice No.</th>
                            <td class="w-[32%] border-r border-fleet-line-soft px-4 py-3 font-semibold text-fleet-ink" data-pre-inspection-view-invoice>-</td>
                            <th class="w-[18%] border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Date</th>
                            <td class="w-[32%] px-4 py-3 font-semibold text-fleet-ink" data-pre-inspection-view-date>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Vehicle</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 font-semibold text-fleet-ink" data-pre-inspection-view-vehicle>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Make / Model</th>
                            <td class="px-4 py-3 text-fleet-ink" data-pre-inspection-view-make-model>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Vehicle Description</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-pre-inspection-view-vehicle-description>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Mileage</th>
                            <td class="px-4 py-3 text-fleet-ink" data-pre-inspection-view-mileage>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Inspector</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 font-semibold text-fleet-ink" data-pre-inspection-view-inspector>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Title</th>
                            <td class="px-4 py-3 text-fleet-ink" data-pre-inspection-view-inspector-title>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Overall Status</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 font-semibold text-fleet-ink" data-pre-inspection-view-overall>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">CC</th>
                            <td class="px-4 py-3 text-fleet-ink" data-pre-inspection-view-cc>-</td>
                        </tr>

                        <tr class="bg-slate-100">
                            <th colspan="4" class="border-y border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Memo Routing</th>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">To</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-pre-inspection-view-memo-to>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Thru (1st)</th>
                            <td class="px-4 py-3 text-fleet-ink" data-pre-inspection-view-memo-thru-one>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Thru (2nd)</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-pre-inspection-view-memo-thru-two>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">From</th>
                            <td class="px-4 py-3 text-fleet-ink" data-pre-inspection-view-memo-from>-</td>
                        </tr>

                        <tr class="bg-slate-100">
                            <th colspan="4" class="border-y border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Findings And Notes</th>
                        </tr>
                        <tr class="border-b border-fleet-line-soft align-top">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Defects Summary</th>
                            <td colspan="3" class="px-4 py-3 leading-6 text-fleet-ink" data-pre-inspection-view-defects>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft align-top">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Closing Note</th>
                            <td colspan="3" class="px-4 py-3 leading-6 text-fleet-ink" data-pre-inspection-view-closing-note>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Item Count</th>
                            <td colspan="3" class="px-4 py-3 text-fleet-ink" data-pre-inspection-view-item-count>0</td>
                        </tr>

                        <tr class="bg-slate-100">
                            <th class="border-y border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">#</th>
                            <th class="border-y border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Inspection Point</th>
                            <th class="border-y border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Status / Findings</th>
                            <th class="border-y border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Remarks / Action Point</th>
                        </tr>
                    </tbody>
                    <tbody data-pre-inspection-view-items>
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-fleet-muted">No inspection items recorded.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-6 flex justify-end gap-3 border-t border-fleet-line-soft pt-5"><button type="button" data-print-pre-inspection-view class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted"><span>P</span><span>Print Report Details</span></button><button type="button" data-close-pre-inspection-view-modal class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Close</button></div>
        </div>
    </div>
</div>
