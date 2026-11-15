<div class="row g-3 mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-white mb-0">Case Registry</h3>
            <p class="text-secondary small mb-0">Browse, filter, and inspect registered investigation files</p>
        </div>
        <div class="d-flex gap-2">
            <!-- CSV Export with current filters -->
            <?php
                $csvParams = http_build_query([
                    'search' => $filters['search'],
                    'status' => $filters['status'],
                    'category' => $filters['category']
                ]);
            ?>
            <a href="/cases/export-csv?<?= $csvParams ?>" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
            </a>
            <form action="/cases/purge-demo" method="POST" class="d-inline" onsubmit="return confirm('Confirm purging all fictional demo cases to start clean CID operational records?');">
                <?= \App\Helpers\Csrf::field() ?>
                <button type="submit" class="btn btn-outline-danger" title="Purge Fictional Demo Cases">
                    <i class="bi bi-trash3 me-1"></i> Purge Demo Cases
                </button>
            </form>
            <?php if ($currentUser['role'] !== 'reviewer'): ?>
                <a href="/cases/create" class="btn btn-teal">
                    <i class="bi bi-plus-circle me-1"></i> New Case
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Search & Multi-Filter Card -->
<div class="card-custom p-3 mb-4">
    <form method="GET" action="/cases" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label text-secondary small mb-1">Search Keywords</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Case #, title, notes..." value="<?= e($filters['search']) ?>">
            </div>
        </div>

        <div class="col-md-2">
            <label class="form-label text-secondary small mb-1">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <?php foreach (['Open', 'Under Investigation', 'Pending Review', 'Closed', 'Archived'] as $st): ?>
                    <option value="<?= $st ?>" <?= $filters['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label text-secondary small mb-1">Category</label>
            <select name="category" class="form-select form-select-sm">
                <option value="">All Categories</option>
                <?php foreach (['Cybercrime', 'Fraud', 'Theft', 'Homicide', 'Narcotics', 'Other'] as $cat): ?>
                    <option value="<?= $cat ?>" <?= $filters['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label text-secondary small mb-1">Priority</label>
            <select name="priority" class="form-select form-select-sm">
                <option value="">All Priorities</option>
                <?php foreach (['Low', 'Medium', 'High', 'Urgent'] as $pr): ?>
                    <option value="<?= $pr ?>" <?= $filters['priority'] === $pr ? 'selected' : '' ?>><?= $pr ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-teal btn-sm flex-grow-1">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            <a href="/cases" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                <i class="bi bi-x-circle"></i>
            </a>
        </div>
    </form>
</div>

<!-- Cases Table Card -->
<div class="card-custom p-0 overflow-hidden mb-4">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0" style="background-color: transparent;">
            <thead>
                <tr class="text-secondary border-secondary-subtle" style="background-color: #0d1527;">
                    <th class="ps-4">Case Number</th>
                    <th>Title & Description</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Evidence</th>
                    <th>Opened</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cases)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-secondary py-5">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                            No investigation cases found matching the criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cases as $c): ?>
                        <tr class="border-secondary-subtle">
                            <td class="ps-4">
                                <a href="/cases/view?id=<?= (int)$c['id'] ?>" class="text-info fw-bold text-decoration-none">
                                    <?= e($c['case_number']) ?>
                                </a>
                            </td>
                            <td style="max-width: 260px;">
                                <div class="fw-semibold text-white text-truncate"><?= e($c['title']) ?></div>
                                <div class="text-secondary small text-truncate"><?= e($c['description']) ?></div>
                            </td>
                            <td><span class="badge bg-secondary"><?= e($c['category']) ?></span></td>
                            <td>
                                <?php
                                    $pClass = match($c['priority']) {
                                        'Urgent' => 'bg-danger',
                                        'High' => 'bg-warning text-dark',
                                        'Medium' => 'bg-info text-dark',
                                        default => 'bg-secondary'
                                    };
                                ?>
                                <span class="badge <?= $pClass ?>"><?= e($c['priority']) ?></span>
                            </td>
                            <td>
                                <?php
                                    $sClass = match($c['status']) {
                                        'Open' => 'border border-primary text-primary',
                                        'Under Investigation' => 'border border-warning text-warning',
                                        'Pending Review' => 'border border-info text-info',
                                        'Closed' => 'border border-success text-success',
                                        default => 'border border-secondary text-secondary'
                                    };
                                ?>
                                <span class="badge <?= $sClass ?> px-2 py-1"><?= e($c['status']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-dark border border-secondary text-light">
                                    <i class="bi bi-paperclip me-1"></i><?= (int)$c['evidence_count'] ?>
                                </span>
                            </td>
                            <td class="text-secondary small"><?= e($c['opened_at']) ?></td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="/cases/view?id=<?= (int)$c['id'] ?>" class="btn btn-outline-info" title="Inspect Case">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($currentUser['role'] !== 'reviewer'): ?>
                                        <a href="/cases/edit?id=<?= (int)$c['id'] ?>" class="btn btn-outline-secondary" title="Edit Details">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-between align-items-center p-3 border-top border-secondary-subtle">
            <div class="text-secondary small">
                Showing Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong> (Total <?= $total ?> cases)
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php
                            $queryParams = array_merge($filters, ['page' => $i]);
                            $url = '/cases?' . http_build_query($queryParams);
                        ?>
                        <li class="page-item <?= ($page === $i) ? 'active' : '' ?>">
                            <a class="page-link bg-dark border-secondary text-light" href="<?= $url ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
