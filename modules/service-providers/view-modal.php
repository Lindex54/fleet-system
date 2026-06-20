<div id="provider-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6" aria-hidden="true">
    <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" style="max-width: 980px;" role="dialog" aria-modal="true" aria-labelledby="provider-view-modal-title">
        <div class="p-6" data-provider-detail-sheet>
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-primary">Provider Details</p>
                    <h2 id="provider-view-modal-title" class="mt-2 text-2xl font-extrabold text-fleet-ink" data-provider-view-name>Service provider</h2>
                    <p class="mt-1 text-sm text-fleet-muted" data-provider-view-subtitle>Full service provider profile for the selected record.</p>
                </div>
                <button type="button" data-close-provider-view-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close provider details">&times;</button>
            </div>
            <div class="overflow-x-auto rounded-2xl border border-fleet-line bg-white">
                <table class="w-full min-w-[860px] border-collapse text-sm">
                    <tbody>
                        <tr class="bg-slate-100">
                            <th colspan="4" class="border-b border-fleet-line px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Provider Profile</th>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="w-[18%] border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Name</th>
                            <td class="w-[32%] border-r border-fleet-line-soft px-4 py-3 font-semibold text-fleet-ink" data-provider-view-company>-</td>
                            <th class="w-[18%] border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Specialization</th>
                            <td class="w-[32%] px-4 py-3 text-fleet-ink" data-provider-view-specialty>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Town</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-provider-view-town>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Status</th>
                            <td class="px-4 py-3 font-semibold text-fleet-ink" data-provider-view-status>-</td>
                        </tr>
                        <tr class="border-b border-fleet-line-soft">
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Contact Person</th>
                            <td class="border-r border-fleet-line-soft px-4 py-3 text-fleet-ink" data-provider-view-contact-person>-</td>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Phone</th>
                            <td class="px-4 py-3 text-fleet-ink" data-provider-view-phone>-</td>
                        </tr>
                        <tr>
                            <th class="border-r border-fleet-line-soft bg-slate-50 px-4 py-3 text-left font-semibold text-fleet-muted">Email</th>
                            <td colspan="3" class="px-4 py-3 break-all text-fleet-ink" data-provider-view-email>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-6 flex justify-end gap-3 border-t border-fleet-line-soft pt-5">
                <button type="button" data-print-provider-view class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted"><span>P</span><span>Print Provider Details</span></button>
                <button type="button" data-close-provider-view-modal class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Close</button>
            </div>
        </div>
    </div>
</div>
