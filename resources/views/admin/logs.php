<div class="row g-3 mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-white mb-0"><i class="bi bi-shield-check text-teal me-2" style="color: var(--teal-light);"></i> System-Wide Audit Log</h3>
            <p class="text-secondary small mb-0">Immutable record of case creation, assignment changes, evidence uploads, and status transitions</p>
        </div>
    </div>
</div>

<div class="card-custom p-0 overflow-hidden mb-4">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small border-secondary-subtle" style="background-color: #0d1527;">
                    <th class="ps-4">Timestamp</th>
                    <th>Case #</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>Actor Personnel</th>
                    <th class="pe-4">Role</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-5">No audit records found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                        <tr class="border-secondary-subtle">
                            <td class="ps-4 text-secondary small"><?= e($l['created_at']) ?></td>
                            <td>
                                <a href="/cases/view?id=<?= (int)$l['case_id'] ?>" class="text-info fw-bold text-decoration-none">
                                    <?= e($l['case_number']) ?>
                                </a>
                            </td>
                            <td><span class="badge bg-secondary font-monospace"><?= e($l['action_type']) ?></span></td>
                            <td class="text-light small"><?= e($l['description']) ?></td>
                            <td class="text-white small fw-medium"><?= e($l['actor_name'] ?? 'System') ?></td>
                            <td class="pe-4"><span class="badge-role role-<?= e($l['actor_role'] ?? 'staff') ?>"><?= e($l['actor_role'] ?? 'Staff') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
