<?php
// Shared create form fields for estate projects.
?>
<div class="space-y-5">
    <div>
        <label class="mb-2 block text-sm font-semibold text-fleet-ink">Project Title *</label>
        <input name="project_name" type="text" class="vehicle-form-control" placeholder="e.g. Library Extension Block" value="<?= htmlspecialchars($estateFormData['project_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold text-fleet-ink">Project Code *</label>
            <input name="project_code" type="text" class="vehicle-form-control" placeholder="e.g. BU-EST-001" value="<?= htmlspecialchars($estateFormData['project_code'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-fleet-ink">Category</label>
            <input name="category" type="text" class="vehicle-form-control" placeholder="e.g. Construction" value="<?= htmlspecialchars($estateFormData['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold text-fleet-ink">Location / Area</label>
            <input name="location" type="text" class="vehicle-form-control" placeholder="e.g. Main Campus - Block B" value="<?= htmlspecialchars($estateFormData['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-fleet-ink">Status</label>
            <select name="status" class="vehicle-form-control">
                <?php foreach (['planned' => 'Planned', 'approved' => 'Approved', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label): ?>
                    <option value="<?= $value; ?>" <?= (($estateFormData['status'] ?? 'planned') === $value) ? 'selected' : ''; ?>><?= $label; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold text-fleet-ink">Priority</label>
            <select name="priority" class="vehicle-form-control">
                <?php foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $value => $label): ?>
                    <option value="<?= $value; ?>" <?= (($estateFormData['priority'] ?? 'medium') === $value) ? 'selected' : ''; ?>><?= $label; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-fleet-ink">Start Date</label>
            <input name="start_date" type="date" class="vehicle-form-control" value="<?= htmlspecialchars($estateFormData['start_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold text-fleet-ink">Expected End Date</label>
            <input name="deadline" type="date" class="vehicle-form-control" value="<?= htmlspecialchars($estateFormData['deadline'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-fleet-ink">Budget (UGX)</label>
            <input name="budget" type="number" min="0" step="0.01" class="vehicle-form-control" value="<?= htmlspecialchars($estateFormData['budget'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>">
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold text-fleet-ink">Spent So Far (UGX)</label>
            <input name="spent" type="number" min="0" step="0.01" class="vehicle-form-control" value="<?= htmlspecialchars($estateFormData['spent'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-fleet-ink">Progress % (<span data-estate-new-progress-label><?= htmlspecialchars($estateFormData['progress_percent'] ?? '0', ENT_QUOTES, 'UTF-8'); ?></span>%)</label>
            <input name="progress_percent" type="range" min="0" max="100" class="h-11 w-full accent-fleet-primary" data-estate-new-progress value="<?= htmlspecialchars($estateFormData['progress_percent'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>">
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold text-fleet-ink">Contractor Name</label>
            <input name="contractor_name" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($estateFormData['contractor_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-fleet-ink">Contractor Contact</label>
            <input name="contractor_contact" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($estateFormData['contractor_contact'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>
    </div>

    <div>
        <label class="mb-2 block text-sm font-semibold text-fleet-ink">Funding Source</label>
        <input name="funding_source" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($estateFormData['funding_source'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </div>

    <div>
        <label class="mb-2 block text-sm font-semibold text-fleet-ink">Description</label>
        <textarea name="description" rows="3" class="vehicle-form-control min-h-24 resize-y py-3"><?= htmlspecialchars($estateFormData['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>
</div>
