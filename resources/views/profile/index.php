<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card-custom p-4 p-md-5 mb-4">
            <h4 class="fw-bold text-white mb-1"><i class="bi bi-person-gear text-teal me-2" style="color: var(--teal-light);"></i> Personnel Profile</h4>
            <p class="text-secondary small mb-4">Manage personal account credentials and security authentication</p>

            <!-- Edit Basic Info Form -->
            <form action="/profile/update" method="POST" class="mb-5 pb-4 border-bottom border-secondary-subtle">
                <?= \App\Helpers\Csrf::field() ?>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="full_name" class="form-label text-light small fw-medium">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?= e($user['full_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label text-light small fw-medium">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e($user['email']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-medium">Assigned Role</label>
                    <input type="text" class="form-control bg-dark text-secondary" value="<?= ucfirst(e($user['role'])) ?>" readonly>
                    <div class="form-text text-secondary" style="font-size: 0.75rem;">Role assignments are governed by the system administrator.</div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-teal btn-sm px-3">Update Details</button>
                </div>
            </form>

            <!-- Change Password Form -->
            <h5 class="fw-bold text-white mb-2"><i class="bi bi-shield-lock text-warning me-2"></i> Update Password</h5>
            <p class="text-secondary small mb-3">Ensure password contains at least 8 characters with numbers and symbols</p>

            <form action="/profile/password" method="POST">
                <?= \App\Helpers\Csrf::field() ?>

                <div class="mb-3">
                    <label for="current_password" class="form-label text-light small fw-medium">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="••••••••">
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="new_password" class="form-label text-light small fw-medium">New Password (min 8 chars)</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8" placeholder="••••••••">
                    </div>
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label text-light small fw-medium">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8" placeholder="••••••••">
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-outline-warning btn-sm px-3">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
