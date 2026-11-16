<div class="row g-3 mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-white mb-0">Case Dossiers & Reports</h3>
            <p class="text-secondary small mb-0">Generate formal printable summaries or export bulk investigation archives</p>
        </div>
        <a href="/cases/export-csv" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-arrow-down me-1"></i> Export Complete Registry (CSV)
        </a>
    </div>
</div>

<div class="card-custom p-4">
    <h5 class="fw-bold text-white mb-3"><i class="bi bi-printer me-2 text-teal" style="color: var(--teal-light);"></i> Available Case Dossiers</h5>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary border-secondary-subtle">
                    <th>Case #</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Opened Date</th>
                    <th class="text-end">Dossier Report</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cases)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">No case files available.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cases as $c): ?>
                        <tr class="border-secondary-subtle">
                            <td><strong class="text-white"><?= e($c['case_number']) ?></strong></td>
                            <td><?= e($c['title']) ?></td>
                            <td><span class="badge bg-secondary"><?= e($c['category']) ?></span></td>
                            <td><span class="badge border border-secondary text-light"><?= e($c['status']) ?></span></td>
                            <td class="text-secondary small"><?= e($c['opened_at']) ?></td>
                            <td class="text-end">
                                <a href="/cases/report?id=<?= (int)$c['id'] ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-printer me-1"></i> View Printable Dossier
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
