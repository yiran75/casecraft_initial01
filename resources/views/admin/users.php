<div class="row g-3 mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-white mb-0"><i class="bi bi-people text-teal me-2" style="color: var(--teal-light);"></i> Personnel Authorization Directory</h3>
            <p class="text-secondary small mb-0">System administrator console for role assignment and active account status</p>
        </div>
    </div>
</div>

<div class="card-custom p-0 overflow-hidden mb-4">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small border-secondary-subtle" style="background-color: #0d1527;">
                    <th class="ps-4">ID</th>
                    <th>Officer Name</th>
                    <th>Email Address</th>
                    <th>Current Role</th>
                    <th>Account Status</th>
                    <th>Registered</th>
                    <th class="text-end pe-4">Manage Permissions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr class="border-secondary-subtle">
                        <td class="ps-4 text-secondary small">#<?= (int)$u['id'] ?></td>
                        <td><strong class="text-white"><?= e($u['full_name']) ?></strong></td>
                        <td class="text-secondary"><?= e($u['email']) ?></td>
                        <td>
                            <span class="badge-role role-<?= e($u['role']) ?>"><?= ucfirst(e($u['role'])) ?></span>
                        </td>
                        <td>
                            <?php if ($u['is_active']): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Deactivated</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-secondary small"><?= e($u['created_at']) ?></td>
                        <td class="text-end pe-4">
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#editUserModal_<?= (int)$u['id'] ?>">
                                <i class="bi bi-pencil me-1"></i> Edit Role
                            </button>
                        </td>
                    </tr>

                    <!-- Edit User Role Modal -->
                    <div class="modal fade" id="editUserModal_<?= (int)$u['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content card-custom text-start">
                                <form action="/admin/users/role" method="POST">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">

                                    <div class="modal-header border-secondary-subtle">
                                        <h5 class="modal-title text-white">Modify Authorization: <?= e($u['full_name']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label text-light small fw-medium">Assigned Role</label>
                                            <select name="role" class="form-select">
                                                <option value="investigator" <?= ($u['role'] === 'investigator') ? 'selected' : '' ?>>Investigator</option>
                                                <option value="reviewer" <?= ($u['role'] === 'reviewer') ? 'selected' : '' ?>>Read-only Reviewer</option>
                                                <option value="admin" <?= ($u['role'] === 'admin') ? 'selected' : '' ?>>Administrator</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-light small fw-medium">Access Status</label>
                                            <select name="is_active" class="form-select">
                                                <option value="1" <?= ($u['is_active'] == 1) ? 'selected' : '' ?>>Active (Can sign in)</option>
                                                <option value="0" <?= ($u['is_active'] == 0) ? 'selected' : '' ?>>Deactivated (Locked out)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-secondary-subtle">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-teal btn-sm">Update Authorization</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
